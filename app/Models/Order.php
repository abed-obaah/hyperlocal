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

    /** Reusable logic to transition order and delivery to completed state, pay the rider, and send notifications. */
    public function completeOrder(): void
    {
        $delivery = $this->delivery;
        if ($delivery && $delivery->status !== 'completed' && $delivery->rider) {
            $rider = $delivery->rider;
            
            // Check if already paid out in rider_earnings
            $earning = \App\Models\RiderEarning::where('delivery_id', $delivery->id)->first();
            if (!$earning || $earning->status !== 'paid') {
                // Credit wallet
                $rider->creditWallet((float) $delivery->amount, 'rider_payout', $this);
                
                if ($earning) {
                    $earning->update(['status' => 'paid']);
                } else {
                    \App\Models\RiderEarning::create([
                        'rider_id' => $rider->id,
                        'delivery_id' => $delivery->id,
                        'amount' => (float) $delivery->amount,
                        'status' => 'paid',
                    ]);
                }
            }

            $delivery->update([
                'status' => 'completed',
                'paid_out_at' => now(),
                'completed_at' => now(),
            ]);

            // Set rider back to available
            $rider->update([
                'rider_status' => 'available',
                'is_available' => true,
            ]);

            $rider->notifyApp(
                'Payout received',
                '₦'.number_format((float) $delivery->amount, 2)." added to your wallet for {$this->order_number}.",
                'cash',
                $this->notificationData(),
                'payout',
            );
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }
}
