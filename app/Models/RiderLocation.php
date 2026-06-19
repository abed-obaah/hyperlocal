<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiderLocation extends Model
{
    protected $fillable = [
        'rider_id', 'delivery_id', 'latitude', 'longitude', 'heading', 'speed', 'accuracy',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'heading' => 'float',
            'speed' => 'float',
            'accuracy' => 'float',
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
