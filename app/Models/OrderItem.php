<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'menu_item_id', 'name', 'image', 'base_price',
        'quantity', 'selected_options', 'notes', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'float',
            'line_total' => 'float',
            'selected_options' => 'array',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
