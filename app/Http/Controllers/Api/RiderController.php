<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    private function authorizeDelivery(Request $request, Delivery $delivery): void
    {
        abort_unless($delivery->rider_id === $request->user()->id, 403);
    }

    /** Mark self available / unavailable. */
    public function toggleAvailability(Request $request)
    {
        $user = $request->user();
        $user->update(['is_available' => ! $user->is_available]);

        return response()->json(['isAvailable' => (bool) $user->is_available]);
    }

    /** Active delivery requests assigned to this rider. */
    public function deliveries(Request $request)
    {
        $deliveries = $request->user()->deliveries()
            ->whereIn('status', ['assigned', 'accepted', 'picked_up', 'on_the_way'])
            ->with(['order.items', 'order.restaurant'])
            ->latest()
            ->get();

        return DeliveryResource::collection($deliveries);
    }

    public function accept(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        $delivery->update(['status' => 'accepted', 'accepted_at' => now()]);
        $delivery->order?->update(['status' => 'rider_assigned']);

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    public function decline(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        // Release it back to the pool for the admin to reassign.
        $delivery->update(['status' => 'declined', 'rider_id' => null]);
        $delivery->order?->update(['status' => 'ready', 'rider_id' => null]);

        return response()->json(['message' => 'Declined']);
    }

    /** Rider confirms pickup → customer sees "on the way". */
    public function pickup(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        $delivery->update(['status' => 'picked_up', 'picked_up_at' => now()]);
        $delivery->order?->update(['status' => 'on_the_way', 'picked_up_at' => now()]);

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    /** Rider marks delivered → earns ₦800. */
    public function deliver(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        $delivery->update(['status' => 'delivered', 'delivered_at' => now()]);
        $delivery->order?->update(['status' => 'delivered', 'delivered_at' => now()]);

        // Credit the flat delivery fee to the rider's wallet.
        $request->user()->increment('wallet_balance', $delivery->amount);

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    public function completed(Request $request)
    {
        $deliveries = $request->user()->deliveries()
            ->where('status', 'delivered')
            ->with(['order.restaurant'])
            ->latest()
            ->get();

        return DeliveryResource::collection($deliveries);
    }

    public function earnings(Request $request)
    {
        $user = $request->user();
        $delivered = $user->deliveries()->where('status', 'delivered');

        return response()->json([
            'completedDeliveries' => (clone $delivered)->count(),
            'perDelivery' => 800,
            'todayEarnings' => round((clone $delivered)->whereDate('delivered_at', today())->sum('amount'), 2),
            'totalEarnings' => round((clone $delivered)->sum('amount'), 2),
            'walletBalance' => (float) $user->wallet_balance,
        ]);
    }
}
