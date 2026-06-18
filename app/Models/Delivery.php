<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'order_id', 'rider_id', 'status', 'amount',
        'assigned_at', 'accepted_at', 'picked_up_at', 'delivered_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'assigned_at' => 'datetime',
            'accepted_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function earning()
    {
        return $this->hasOne(RiderEarning::class);
    }
}
