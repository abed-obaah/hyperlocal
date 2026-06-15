<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'order_id', 'customer_id', 'subject', 'description',
        'status', 'resolution', 'refund_amount',
    ];

    protected function casts(): array
    {
        return ['refund_amount' => 'float'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
