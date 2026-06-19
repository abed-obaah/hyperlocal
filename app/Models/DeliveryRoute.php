<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    protected $fillable = [
        'delivery_id', 'route_type', 'polyline', 'distance_meters', 'duration_seconds', 'provider',
    ];

    protected function casts(): array
    {
        return [
            'polyline' => 'array',
            'distance_meters' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
