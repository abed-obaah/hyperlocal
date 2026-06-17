<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items', 'restaurant', 'rider'])
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        return new OrderResource($order->load(['items', 'restaurant', 'rider']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'restaurantId' => 'required|exists:restaurants,id',
            'items' => 'required|array|min:1',
            'items.*.menuItemId' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selectedOptions' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
            'address' => 'required|array',
            'paymentMethod' => 'required|in:cash,card,wallet',
            'couponCode' => 'nullable|string',
        ]);

        $restaurant = Restaurant::findOrFail($data['restaurantId']);

        // Recompute totals server-side from authoritative menu prices.
        $subtotal = 0;
        $lines = [];
        foreach ($data['items'] as $line) {
            $item = MenuItem::findOrFail($line['menuItemId']);
            $options = $line['selectedOptions'] ?? [];
            $optionsTotal = collect($options)->sum(fn ($o) => (float) ($o['price'] ?? 0));
            $lineTotal = ($item->price + $optionsTotal) * $line['quantity'];
            $subtotal += $lineTotal;
            $lines[] = [
                'menu_item_id' => $item->id,
                'name' => $item->name,
                'image' => $item->image,
                'base_price' => $item->price,
                'quantity' => $line['quantity'],
                'selected_options' => $options,
                'notes' => $line['notes'] ?? null,
                'line_total' => $lineTotal,
            ];
        }

        $discount = 0;
        $deliveryFee = (float) $restaurant->delivery_fee;
        if (! empty($data['couponCode'])) {
            $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper($data['couponCode'])])->first();
            if ($coupon && $subtotal >= $coupon->min_subtotal) {
                if ($coupon->code === 'FREESHIP') {
                    $deliveryFee = 0;
                } elseif ($coupon->type === 'percentage') {
                    $discount = $subtotal * $coupon->value / 100;
                } else {
                    $discount = $coupon->value;
                }
            }
        }

        $taxable = max($subtotal - $discount, 0);
        $tax = round($taxable * 0.05, 2);
        $total = round($taxable + $deliveryFee + $tax, 2);
        // Platform commission snapshot, taken on the (post-discount) food value.
        $commission = round($taxable * (float) config('hyperlocal.commission_rate'), 2);

        $order = Order::create([
            'order_number' => '#'.(2387 + Order::count()),
            'customer_id' => $request->user()->id,
            'restaurant_id' => $restaurant->id,
            'status' => 'placed',
            'payment_method' => $data['paymentMethod'],
            'payment_status' => $data['paymentMethod'] === 'cash' ? 'pending' : 'paid',
            'address' => $data['address'],
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => $deliveryFee,
            'discount' => round($discount, 2),
            'tax' => $tax,
            'total' => $total,
            'commission' => $commission,
            'eta_minutes' => $restaurant->eta_minutes,
            'placed_at' => now(),
        ]);

        foreach ($lines as $line) {
            $order->items()->create($line);
        }

        $order->notifyCustomer(
            'Order placed',
            "We've sent {$order->order_number} to {$restaurant->name}.",
            'receipt',
        );

        return new OrderResource($order->load(['items', 'restaurant', 'rider']));
    }

    /**
     * Demo-only: nudge an order forward along the happy path so the customer
     * tracking screen animates without needing the restaurant/rider apps.
     */
    public function advance(Request $request, Order $order)
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        $flow = ['placed', 'accepted', 'preparing', 'ready', 'rider_assigned', 'picked_up', 'on_the_way', 'delivered'];
        $idx = array_search($order->status, $flow, true);
        if ($idx !== false && $idx < count($flow) - 1) {
            $order->update(['status' => $flow[$idx + 1]]);
        }

        return new OrderResource($order->load(['items', 'restaurant', 'rider']));
    }
}
