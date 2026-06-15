<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'restaurant_id', 'user_id', 'order_id', 'author', 'avatar',
        'rating', 'comment', 'photos', 'tags',
    ];

    protected function casts(): array
    {
        return ['photos' => 'array', 'tags' => 'array', 'rating' => 'integer'];
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
