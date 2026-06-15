<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_id', 'restaurant_id', 'rider_id', 'status',
        'payment_method', 'payment_status', 'address', 'subtotal', 'delivery_fee',
        'discount', 'tax', 'total', 'eta_minutes', 'rejected_reason',
        'placed_at', 'accepted_at', 'ready_at', 'picked_up_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'subtotal' => 'float',
            'delivery_fee' => 'float',
            'discount' => 'float',
            'tax' => 'float',
            'total' => 'float',
            'placed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'ready_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    /** Create an in-app notification for this order's customer. */
    public function notifyCustomer(string $title, string $body, string $icon = 'notifications'): void
    {
        UserNotification::create([
            'user_id' => $this->customer_id,
            'type' => 'order',
            'title' => $title,
            'body' => $body,
            'icon' => $icon,
            'data' => ['orderId' => (string) $this->id, 'restaurantId' => (string) $this->restaurant_id],
        ]);
    }
}
