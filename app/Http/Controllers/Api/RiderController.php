<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\User;
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

    /** Deliveries this rider has accepted and is actively working. */
    public function deliveries(Request $request)
    {
        $deliveries = $request->user()->deliveries()
            ->whereIn('status', ['accepted', 'picked_up', 'on_the_way'])
            ->with(['order.items', 'order.restaurant'])
            ->latest()
            ->get();

        return DeliveryResource::collection($deliveries);
    }

    /** New assignments awaiting this rider's accept/decline. */
    public function assignments(Request $request)
    {
        $deliveries = $request->user()->deliveries()
            ->where('status', 'assigned')
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

    /** Rider confirms pickup at the restaurant. */
    public function pickup(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        $delivery->update(['status' => 'picked_up', 'picked_up_at' => now()]);
        $delivery->order?->update(['status' => 'picked_up', 'picked_up_at' => now()]);
        $delivery->order?->notifyCustomer('Order picked up', 'Your rider has picked up your order.', 'bicycle');

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    /** Rider is en route to the customer. */
    public function onTheWay(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        $delivery->update(['status' => 'on_the_way']);
        $delivery->order?->update(['status' => 'on_the_way']);
        $delivery->order?->notifyCustomer('On the way', 'Your order is on the way!', 'navigate');

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    /**
     * Rider marks the order delivered. The payout is NOT credited here — an admin
     * pays the rider out when they "complete" the order (see AdminController::complete).
     */
    public function deliver(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        $delivery->update(['status' => 'delivered', 'delivered_at' => now()]);
        $order = $delivery->order;
        $order?->update(['status' => 'delivered', 'delivered_at' => now()]);
        $order?->notifyCustomer('Delivered', 'Your order has been delivered. Enjoy!', 'checkmark-circle');

        // Let admins know it's ready to be completed (which releases the rider payout).
        if ($order) {
            User::where('role', 'admin')->get()->each(fn (User $admin) => $admin->notifyApp(
                'Order delivered',
                "{$order->order_number} was delivered — complete it to pay the rider.",
                'checkmark-circle',
                $order->notificationData(),
                'admin',
            ));
        }

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    public function completed(Request $request)
    {
        // Both delivered (awaiting payout) and completed (paid out) are finished jobs.
        $deliveries = $request->user()->deliveries()
            ->whereIn('status', ['delivered', 'completed'])
            ->with(['order.restaurant'])
            ->latest()
            ->get();

        return DeliveryResource::collection($deliveries);
    }

    public function earnings(Request $request)
    {
        $user = $request->user();
        // Paid out = admin has completed the order. Delivered-but-not-completed is pending payout.
        $paid = $user->deliveries()->where('status', 'completed');
        $pending = $user->deliveries()->where('status', 'delivered');

        return response()->json([
            'completedDeliveries' => $user->deliveries()->whereIn('status', ['delivered', 'completed'])->count(),
            'perDelivery' => (float) config('hyperlocal.rider_fee'),
            'todayEarnings' => round((clone $paid)->whereDate('paid_out_at', today())->sum('amount'), 2),
            'totalEarnings' => round((clone $paid)->sum('amount'), 2),
            'pendingPayout' => round((clone $pending)->sum('amount'), 2),
            'walletBalance' => (float) $user->wallet_balance,
        ]);
    }
}
