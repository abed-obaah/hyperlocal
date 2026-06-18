<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiderEarning extends Model
{
    protected $fillable = [
        'rider_id', 'delivery_id', 'amount', 'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
