<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\User;
use App\Models\RiderEarning;
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
        if ($user->rider_status === 'busy') {
            return response()->json(['error' => 'Cannot toggle availability while busy with a delivery.'], 422);
        }
        $newStatus = $user->rider_status === 'available' ? 'unavailable' : 'available';
        $user->update([
            'rider_status' => $newStatus,
            'is_available' => $newStatus === 'available'
        ]);

        return response()->json(['isAvailable' => $newStatus === 'available', 'status' => $newStatus]);
    }

    /** Deliveries this rider has accepted and is actively working. */
    public function deliveries(Request $request)
    {
        $deliveries = $request->user()->deliveries()
            ->whereIn('status', ['accepted', 'arrived_at_restaurant', 'picked_up', 'on_the_way'])
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
        abort_unless($delivery->status === 'assigned', 422, 'Delivery is not in assigned state.');
        
        $user = $request->user();
        // Check if already busy
        $hasActive = Delivery::where('rider_id', $user->id)
            ->whereIn('status', ['accepted', 'arrived_at_restaurant', 'picked_up', 'on_the_way'])
            ->exists();
        if ($hasActive) {
            return response()->json(['error' => 'You already have an active delivery.'], 422);
        }

        $delivery->update(['status' => 'accepted', 'accepted_at' => now()]);
        $delivery->order?->update(['status' => 'rider_assigned']);

        $user->update([
            'rider_status' => 'busy',
            'is_available' => false
        ]);

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    public function decline(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        abort_unless($delivery->status === 'assigned', 422, 'You can only decline pending assignments.');

        $user = $request->user();
        $order = $delivery->order;

        $delivery->update(['status' => 'declined', 'rider_id' => null]);
        $order?->update(['status' => 'ready', 'rider_id' => null]);

        if ($order) {
            User::where('role', 'admin')->get()->each(fn (User $admin) => $admin->notifyApp(
                'Delivery declined',
                "{$order->order_number} was declined by rider {$user->name}.",
                'close-circle',
                $order->notificationData(),
                'admin',
            ));
        }

        if ($user->rider_status === 'busy') {
            $user->update([
                'rider_status' => 'available',
                'is_available' => true
            ]);
        }

        return response()->json(['message' => 'Declined']);
    }

    /** Rider confirms arrival at the restaurant. */
    public function arriveAtRestaurant(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        abort_unless($delivery->status === 'accepted', 422, 'Must accept delivery first.');

        $delivery->update(['status' => 'arrived_at_restaurant']);
        $delivery->order?->update(['status' => 'rider_arrived']);
        $delivery->order?->notifyCustomer('Rider arrived', 'Your rider has arrived at the restaurant.', 'storefront');

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    /** Rider confirms pickup at the restaurant. */
    public function pickup(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        abort_unless($delivery->status === 'arrived_at_restaurant', 422, 'Must arrive at restaurant first.');

        $delivery->update(['status' => 'picked_up', 'picked_up_at' => now()]);
        $delivery->order?->update(['status' => 'picked_up', 'picked_up_at' => now()]);
        $delivery->order?->notifyCustomer('Order picked up', 'Your rider has picked up your order.', 'bicycle');

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    /** Rider is en route to the customer. */
    public function onTheWay(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        abort_unless($delivery->status === 'picked_up', 422, 'Must pick up order first.');

        $delivery->update(['status' => 'on_the_way']);
        $delivery->order?->update(['status' => 'on_the_way']);
        $delivery->order?->notifyCustomer('On the way', 'Your order is on the way!', 'navigate');

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    /** Rider marks the order delivered. */
    public function deliver(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        abort_unless($delivery->status === 'on_the_way', 422, 'Must be on the way first.');

        $delivery->update(['status' => 'delivered', 'delivered_at' => now()]);
        $order = $delivery->order;
        $order?->update(['status' => 'delivered', 'delivered_at' => now()]);
        $order?->notifyCustomer('Delivered', 'Your order has been delivered. Enjoy!', 'checkmark-circle');

        // Set rider back to available immediately as they have finished the delivery
        $user = $request->user();
        $user->update([
            'rider_status' => 'available',
            'is_available' => true
        ]);

        // Create pending earning for this completed run
        RiderEarning::firstOrCreate(
            ['delivery_id' => $delivery->id],
            [
                'rider_id' => $user->id,
                'amount' => 800.00,
                'status' => 'pending'
            ]
        );

        if ($order) {
            // If the order has already been paid (e.g. Card/Wallet), automatically complete it!
            if ($order->payment_status === 'paid') {
                $order->completeOrder();
            } else {
                // Otherwise let admins know it's ready to be completed
                User::where('role', 'admin')->get()->each(fn (User $admin) => $admin->notifyApp(
                    'Order delivered',
                    "{$order->order_number} was delivered — complete it to pay the rider.",
                    'checkmark-circle',
                    $order->notificationData(),
                    'admin',
                ));
            }
        }

        return new DeliveryResource($delivery->load(['order.items', 'order.restaurant']));
    }

    public function completed(Request $request)
    {
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
        
        $totalEarnings = (float) RiderEarning::where('rider_id', $user->id)->where('status', 'paid')->sum('amount');
        $todayEarnings = (float) RiderEarning::where('rider_id', $user->id)->where('status', 'paid')->whereDate('created_at', today())->sum('amount');
        $pendingPayout = (float) RiderEarning::where('rider_id', $user->id)->where('status', 'pending')->sum('amount');
        $completedCount = RiderEarning::where('rider_id', $user->id)->count();

        return response()->json([
            'completedDeliveries' => $completedCount,
            'perDelivery' => 800.00,
            'todayEarnings' => $todayEarnings,
            'totalEarnings' => $totalEarnings,
            'pendingPayout' => $pendingPayout,
            'walletBalance' => (float) $user->wallet_balance,
        ]);
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($request, $delivery);
        $data = $request->validate([
            'status' => 'required|string|in:accepted,arrived_at_restaurant,picked_up,on_the_way,delivered'
        ]);

        $nextStatus = $data['status'];

        if ($nextStatus === 'accepted') {
            return $this->accept($request, $delivery);
        }
        if ($nextStatus === 'arrived_at_restaurant') {
            return $this->arriveAtRestaurant($request, $delivery);
        }
        if ($nextStatus === 'picked_up') {
            return $this->pickup($request, $delivery);
        }
        if ($nextStatus === 'on_the_way') {
            return $this->onTheWay($request, $delivery);
        }
        if ($nextStatus === 'delivered') {
            return $this->deliver($request, $delivery);
        }

        return response()->json(['error' => 'Invalid status transition.'], 422);
    }
}
