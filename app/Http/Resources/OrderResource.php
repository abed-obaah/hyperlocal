<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /** Maps the rich backend status to the mobile app's 5-step timeline status. */
    public const STATUS_MAP = [
        'placed' => 'received',
        'accepted' => 'preparing',
        'preparing' => 'preparing',
        'ready' => 'preparing',
        'rider_assigned' => 'rider-assigned',
        'picked_up' => 'on-the-way',
        'on_the_way' => 'on-the-way',
        'delivered' => 'delivered',
        'completed' => 'delivered',
        'rejected' => 'received',
        'cancelled' => 'received',
    ];

    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'orderNumber' => $this->order_number,
            'restaurantId' => (string) $this->restaurant_id,
            'restaurantName' => $this->restaurant?->name,
            'restaurantLogo' => $this->restaurant?->logo,
            'restaurantAddress' => $this->restaurant?->address,
            'customerName' => $this->customer?->name,
            'customerPhone' => $this->customer?->phone,
            'items' => $this->items->map(fn ($i) => [
                'lineId' => (string) $i->id,
                'menuItemId' => (string) $i->menu_item_id,
                'restaurantId' => (string) $this->restaurant_id,
                'name' => $i->name,
                'image' => $i->image,
                'basePrice' => (float) $i->base_price,
                'quantity' => $i->quantity,
                'selectedOptions' => $i->selected_options ?? [],
                'notes' => $i->notes,
                'lineTotal' => (float) $i->line_total,
            ]),
            'subtotal' => (float) $this->subtotal,
            'deliveryFee' => (float) $this->delivery_fee,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'commission' => (float) $this->commission,
            'paymentMethod' => $this->payment_method,
            'paymentStatus' => $this->payment_status,
            'address' => $this->address,
            'status' => self::STATUS_MAP[$this->status] ?? 'received',
            'backendStatus' => $this->status,
            'placedAt' => optional($this->placed_at ?? $this->created_at)->toISOString(),
            'completedAt' => optional($this->completed_at)->toISOString(),
            'etaMinutes' => $this->eta_minutes,
            'rider' => $this->rider ? [
                'id' => (string) $this->rider->id,
                'name' => $this->rider->name,
                'phone' => $this->rider->phone,
                'photo' => $this->rider->avatar,
                'rating' => 4.9,
                'vehicle' => 'Motorbike · Red',
            ] : null,
        ];
    }
}
