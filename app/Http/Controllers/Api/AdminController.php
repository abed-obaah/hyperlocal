<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\RestaurantResource;
use App\Models\Complaint;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /** Onboard a restaurant (creates its login + the restaurant record). */
    public function onboardRestaurant(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'ownerName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'cuisines' => 'nullable|array',
            'categories' => 'nullable|array',
            'address' => 'nullable|string',
            'deliveryFee' => 'nullable|numeric',
            'etaMinutes' => 'nullable|integer',
        ]);

        $owner = User::create([
            'name' => $data['ownerName'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'restaurant',
        ]);

        $restaurant = Restaurant::create([
            'user_id' => $owner->id,
            'name' => $data['name'],
            'cuisines' => $data['cuisines'] ?? [],
            'categories' => $data['categories'] ?? [],
            'address' => $data['address'] ?? null,
            'delivery_fee' => $data['deliveryFee'] ?? 0,
            'eta_minutes' => $data['etaMinutes'] ?? 30,
            'cover_image' => 'https://picsum.photos/seed/'.urlencode($data['name']).'/800/600',
            'logo' => 'https://picsum.photos/seed/'.urlencode($data['name']).'-logo/200/200',
            'is_open' => true,
        ]);

        return new RestaurantResource($restaurant);
    }

    /** Onboard a rider account. */
    public function onboardRider(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:30',
        ]);

        $rider = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'role' => 'rider',
            'avatar' => 'https://i.pravatar.cc/200?u='.urlencode($data['email']),
        ]);

        return response()->json([
            'id' => (string) $rider->id,
            'name' => $rider->name,
            'email' => $rider->email,
            'phone' => $rider->phone,
            'role' => 'rider',
        ], 201);
    }

    /** Live order feed for monitoring. */
    public function orders(Request $request)
    {
        $query = Order::with(['items', 'restaurant', 'rider'])->latest();
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return OrderResource::collection($query->get());
    }

    public function availableRiders()
    {
        return User::where('role', 'rider')->where('rider_status', 'available')->get()->map(fn ($r) => [
            'id' => (string) $r->id,
            'name' => $r->name,
            'phone' => $r->phone,
            'photo' => $r->avatar,
        ]);
    }

    /** Assign an available rider to an order. */
    public function assignRider(Request $request, Order $order)
    {
        $data = $request->validate(['riderId' => 'required|exists:users,id']);

        $rider = User::findOrFail($data['riderId']);
        abort_unless($rider->role === 'rider', 422, 'Selected user is not a rider.');
        abort_if($rider->rider_status === 'busy', 422, 'This rider is currently busy with another delivery.');

        $previousRiderId = $order->rider_id;
        if ($previousRiderId && $previousRiderId != $data['riderId']) {
            $previousRider = User::find($previousRiderId);
            if ($previousRider) {
                $previousRider->update([
                    'rider_status' => 'available',
                    'is_available' => true,
                ]);
            }
        }

        $delivery = Delivery::updateOrCreate(
            ['order_id' => $order->id],
            [
                'rider_id' => $data['riderId'],
                'status' => 'assigned',
                'assigned_at' => now(),
                'amount' => config('hyperlocal.rider_fee', 800.00),
            ],
        );
        $order->update(['rider_id' => $data['riderId'], 'status' => 'rider_assigned']);

        // Notify the assigned rider that a package is ready for them.
        $rider->notifyApp(
            'New delivery assigned',
            "{$order->restaurant?->name} · {$order->order_number} is ready for pickup.",
            'bicycle',
            $order->notificationData(),
            'delivery',
        );

        return new OrderResource($order->load(['items', 'restaurant', 'rider']));
    }

    /**
     * Admin completes a delivered order: releases the flat fee to the rider's
     * wallet (with an audit row) and notifies them. Idempotent — a delivery that
     * has already been paid out is left untouched.
     */
    public function complete(Request $request, Order $order)
    {
        abort_unless($order->status === 'delivered', 422, 'Order is not delivered yet.');

        $order->completeOrder();

        return new OrderResource($order->load(['items', 'restaurant', 'rider']));
    }

    /** All riders with availability + delivery performance. */
    public function riders()
    {
        return User::where('role', 'rider')->get()->map(function ($r) {
            $finished = Delivery::where('rider_id', $r->id)->whereIn('status', ['delivered', 'completed']);
            $paid = Delivery::where('rider_id', $r->id)->where('status', 'completed');
            $active = Delivery::where('rider_id', $r->id)
                ->whereIn('status', ['assigned', 'accepted', 'picked_up', 'on_the_way'])
                ->with('order')
                ->latest()
                ->first();

            return [
                'id' => (string) $r->id,
                'name' => $r->name,
                'email' => $r->email,
                'phone' => $r->phone,
                'photo' => $r->avatar,
                'isAvailable' => (bool) $r->is_available,
                'completedDeliveries' => (clone $finished)->count(),
                'todayDeliveries' => (clone $finished)->whereDate('delivered_at', today())->count(),
                'totalEarnings' => round((clone $paid)->sum('amount'), 2),
                'walletBalance' => (float) $r->wallet_balance,
                'activeOrder' => $active?->order?->order_number,
            ];
        });
    }

    /** Orders with their payment state, for transfer/refund confirmation. */
    public function payments(Request $request)
    {
        $query = Order::with('restaurant')->latest();
        if ($status = $request->query('payment_status')) {
            $query->where('payment_status', $status);
        }
        $orders = $query->get();

        $sumBy = fn (string $s) => round((float) Order::where('payment_status', $s)->sum('total'), 2);

        return response()->json([
            'summary' => [
                'paid' => $sumBy('paid'),
                'pending' => $sumBy('pending'),
                'refundPending' => $sumBy('refund_pending'),
                'refunded' => $sumBy('refunded'),
            ],
            'data' => $orders->map(fn ($o) => [
                'id' => (string) $o->id,
                'orderNumber' => $o->order_number,
                'restaurantName' => $o->restaurant?->name,
                'total' => (float) $o->total,
                'paymentMethod' => $o->payment_method,
                'paymentStatus' => $o->payment_status,
                'status' => $o->status,
                'date' => optional($o->created_at)->toISOString(),
            ]),
        ]);
    }

    /** Confirm a cash/transfer payment was received. */
    public function confirmPayment(Request $request, Order $order)
    {
        $order->update(['payment_status' => 'paid']);
        $order->notifyCustomer('Payment confirmed', "We've received payment for {$order->order_number}.", 'card');

        if ($order->status === 'delivered') {
            $order->completeOrder();
        }

        return response()->json(['paymentStatus' => 'paid']);
    }

    /** Mark a paid / refund-pending order as refunded. */
    public function refundOrder(Request $request, Order $order)
    {
        abort_unless($order->payment_status === 'refund_pending', 422, 'Order does not have a pending refund.');

        $order->update(['payment_status' => 'refunded']);
        $order->customer?->creditWallet((float) $order->total, 'order_refund', $order);
        $order->notifyCustomer(
            'Refund issued', 
            "Your payment of ₦" . number_format($order->total, 2) . " for {$order->order_number} has been refunded to your wallet.", 
            'cash'
        );

        return response()->json(['paymentStatus' => 'refunded']);
    }

    /** Cancel an order; flags a refund when it was already paid. */
    public function cancelOrder(Request $request, Order $order)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:255']);

        $wasPaid = $order->payment_status === 'paid';
        $order->update([
            'status' => 'cancelled',
            'rejected_reason' => $data['reason'] ?? 'Cancelled by admin',
            'payment_status' => $wasPaid ? 'refund_pending' : $order->payment_status,
        ]);

        if ($order->delivery) {
            $order->delivery->update(['status' => 'cancelled']);
        }
        if ($order->rider) {
            $hasOtherActive = Delivery::where('rider_id', $order->rider_id)
                ->where('order_id', '!=', $order->id)
                ->whereIn('status', ['accepted', 'arrived_at_restaurant', 'picked_up', 'on_the_way'])
                ->exists();
            if (!$hasOtherActive) {
                $order->rider->update([
                    'rider_status' => 'available',
                    'is_available' => true,
                ]);
            }
        }

        if ($wasPaid) {
            $order->notifyCustomer(
                'Order cancelled',
                $order->rejected_reason.'. A refund has been initiated.',
                'close-circle',
            );
        } else {
            $order->notifyCustomer('Order cancelled', $order->rejected_reason, 'close-circle');
        }

        return new OrderResource($order->load(['items', 'restaurant', 'rider']));
    }

    public function complaints()
    {
        return Complaint::with('order')->latest()->get()->map(fn ($c) => [
            'id' => (string) $c->id,
            'orderId' => $c->order_id ? (string) $c->order_id : null,
            'orderNumber' => $c->order?->order_number,
            'subject' => $c->subject,
            'description' => $c->description,
            'status' => $c->status,
            'resolution' => $c->resolution,
            'refundAmount' => $c->refund_amount,
            'createdAt' => optional($c->created_at)->toISOString(),
        ]);
    }

    public function resolveComplaint(Request $request, Complaint $complaint)
    {
        $data = $request->validate([
            'resolution' => 'required|string',
            'refundAmount' => 'nullable|numeric|min:0',
        ]);

        $isRefund = ! empty($data['refundAmount']);
        $complaint->update([
            'status' => $isRefund ? 'refunded' : 'resolved',
            'resolution' => $data['resolution'],
            'refund_amount' => $data['refundAmount'] ?? null,
        ]);

        if ($isRefund && $complaint->order) {
            $complaint->order->update(['payment_status' => 'refunded']);
        }

        return response()->json(['message' => 'Complaint updated']);
    }

    /** Platform revenue + activity overview. */
    public function revenue()
    {
        // Orders that count toward revenue (exclude rejected/cancelled).
        $active = Order::whereNotIn('status', ['rejected', 'cancelled']);

        $deliveryFees = round((clone $active)->sum('delivery_fee'), 2);
        $commission = round((clone $active)->sum('commission'), 2);
        // Rider payouts that have actually been released (admin-completed orders).
        $riderPayouts = round(Delivery::where('status', 'completed')->sum('amount'), 2);
        // What the platform keeps on delivery after paying riders.
        $deliveryMargin = round($deliveryFees - $riderPayouts, 2);

        return response()->json([
            'totalRevenue' => round((clone $active)->sum('total'), 2),
            'deliveryFees' => $deliveryFees,
            'todayRevenue' => round((clone $active)->whereDate('created_at', today())->sum('total'), 2),
            'commission' => $commission,
            'deliveryMargin' => $deliveryMargin,
            'riderPayouts' => $riderPayouts,
            // Platform's take: food commission + delivery margin.
            'platformEarnings' => round($commission + $deliveryMargin, 2),
            'totalOrders' => Order::count(),
            'liveOrders' => Order::whereIn('status', ['placed', 'accepted', 'preparing', 'ready', 'rider_assigned', 'picked_up', 'on_the_way'])->count(),
            'restaurants' => Restaurant::count(),
            'riders' => User::where('role', 'rider')->count(),
            'customers' => User::where('role', 'customer')->count(),
            'openComplaints' => Complaint::where('status', 'open')->count(),
        ]);
    }
}
