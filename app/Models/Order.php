<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_id', 'restaurant_id', 'rider_id', 'status',
        'payment_method', 'payment_status', 'address', 'subtotal', 'delivery_fee',
        'discount', 'tax', 'total', 'commission', 'eta_minutes', 'rejected_reason',
        'placed_at', 'accepted_at', 'ready_at', 'picked_up_at', 'delivered_at', 'completed_at',
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
            'commission' => 'float',
            'placed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'ready_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
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

    /** Notify this order's customer in-app and via push. */
    public function notifyCustomer(string $title, string $body, string $icon = 'notifications'): void
    {
        $this->customer?->notifyApp($title, $body, $icon, $this->notificationData());
    }

    /** Standard payload attached to every order notification. */
    public function notificationData(): array
    {
        return ['orderId' => (string) $this->id, 'restaurantId' => (string) $this->restaurant_id];
    }
}
