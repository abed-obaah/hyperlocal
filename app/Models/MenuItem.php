<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'restaurant_id', 'name', 'description', 'price', 'image', 'section',
        'ingredients', 'prep_time_minutes', 'calories', 'popular', 'is_available', 'option_groups',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'ingredients' => 'array',
            'option_groups' => 'array',
            'popular' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
