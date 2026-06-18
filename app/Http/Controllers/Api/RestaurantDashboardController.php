<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;

class RestaurantDashboardController extends Controller
{
    private function restaurant(Request $request): Restaurant
    {
        $restaurant = $request->user()->restaurant;
        abort_if(! $restaurant, 403, 'No restaurant linked to this account.');

        return $restaurant;
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_unless($order->restaurant_id === $this->restaurant($request)->id, 403);
    }

    /** Incoming + in-progress orders. */
    public function orders(Request $request)
    {
        $orders = $this->restaurant($request)->orders()
            ->whereIn('status', ['placed', 'accepted', 'preparing', 'ready', 'rider_assigned', 'picked_up', 'on_the_way'])
            ->with(['items', 'rider'])
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function history(Request $request)
    {
        $orders = $this->restaurant($request)->orders()
            ->whereIn('status', ['delivered', 'rejected', 'cancelled'])
            ->with(['items', 'rider'])
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function accept(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        // Accept and "preparing" are two distinct steps: accepting confirms the
        // order, then the restaurant separately taps "preparing" once cooking starts.
        $order->update(['status' => 'accepted', 'accepted_at' => now()]);
        $order->notifyCustomer('Order accepted', "{$order->restaurant->name} accepted your order.", 'restaurant');

        return new OrderResource($order->load(['items', 'rider']));
    }

    public function reject(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        $data = $request->validate(['reason' => 'nullable|string|max:255']);

        $wasPaid = $order->payment_status === 'paid';
        $order->update([
            'status' => 'rejected',
            'rejected_reason' => $data['reason'] ?? 'Restaurant unavailable',
            'payment_status' => $wasPaid ? 'refund_pending' : $order->payment_status,
        ]);

        if ($wasPaid) {
            $order->notifyCustomer(
                'Order declined',
                $order->rejected_reason.'. A refund has been initiated.',
                'close-circle',
            );
        } else {
            $order->notifyCustomer('Order declined', $order->rejected_reason, 'close-circle');
        }

        return new OrderResource($order->load(['items', 'rider']));
    }

    public function preparing(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        $order->update(['status' => 'preparing']);
        $order->notifyCustomer('Preparing your food', "{$order->restaurant->name} is preparing your order.", 'restaurant');

        return new OrderResource($order->load(['items', 'rider']));
    }

    public function ready(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        $order->update(['status' => 'ready', 'ready_at' => now()]);
        $order->notifyCustomer('Order ready', 'Your order is ready and waiting for a rider.', 'bag-check');

        // Tell every admin a package is ready so they can assign a rider.
        User::where('role', 'admin')->get()->each(fn (User $admin) => $admin->notifyApp(
            'Package ready',
            "{$order->restaurant->name} has {$order->order_number} ready for pickup.",
            'bag-check',
            $order->notificationData(),
            'admin',
        ));

        return new OrderResource($order->load(['items', 'rider']));
    }

    /** Basic sales + order stats for the dashboard. */
    public function sales(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $base = $restaurant->orders()->whereNotIn('status', ['rejected', 'cancelled']);

        return response()->json([
            'todayOrders' => (clone $base)->whereDate('created_at', today())->count(),
            'todayRevenue' => round((clone $base)->whereDate('created_at', today())->sum('total'), 2),
            'totalOrders' => (clone $base)->count(),
            'totalRevenue' => round((clone $base)->sum('total'), 2),
            'pending' => $restaurant->orders()->where('status', 'placed')->count(),
        ]);
    }

    /**
     * All reviews for the restaurant, each annotated with the food that was
     * ordered when the review is linked to an order. Reviews with no order are
     * surfaced as "general" restaurant reviews.
     */
    public function reviews(Request $request)
    {
        $restaurant = $this->restaurant($request);

        $reviews = $restaurant->reviews()
            ->with(['order.items'])
            ->latest()
            ->get();

        return response()->json([
            'summary' => [
                'count' => $reviews->count(),
                'average' => round((float) $reviews->avg('rating'), 1),
                'distribution' => collect(range(5, 1))
                    ->mapWithKeys(fn ($star) => [$star => $reviews->where('rating', $star)->count()]),
            ],
            'data' => $reviews->map(fn ($review) => [
                'id' => (string) $review->id,
                'author' => $review->author,
                'avatar' => $review->avatar,
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'tags' => $review->tags ?? [],
                'date' => optional($review->created_at)->toDateString(),
                'orderNumber' => $review->order?->order_number,
                'items' => $review->order
                    ? $review->order->items->map(fn ($i) => [
                        'name' => $i->name,
                        'quantity' => $i->quantity,
                    ])->values()
                    : [],
            ]),
        ]);
    }

    /** Current opening hours + whether the restaurant is open right now. */
    public function hours(Request $request)
    {
        $restaurant = $this->restaurant($request);

        return response()->json([
            'openingHours' => $restaurant->opening_hours ?? [],
            'isOpenNow' => $restaurant->isOpenNow(),
        ]);
    }

    /** Replace the per-day opening hours; recompute the live open flag. */
    public function updateHours(Request $request)
    {
        $restaurant = $this->restaurant($request);

        $data = $request->validate([
            'openingHours' => 'required|array',
            'openingHours.*.day' => 'required|string',
            'openingHours.*.open' => 'nullable|string',   // "09:00" or null when closed
            'openingHours.*.close' => 'nullable|string',
            'openingHours.*.closed' => 'nullable|boolean',
        ]);

        $restaurant->update(['opening_hours' => $data['openingHours']]);
        $restaurant->update(['is_open' => $restaurant->isOpenNow()]);

        return response()->json([
            'openingHours' => $restaurant->opening_hours,
            'isOpenNow' => $restaurant->isOpenNow(),
        ]);
    }
}
