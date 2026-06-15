<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'label', 'type', 'value', 'min_subtotal'];

    protected function casts(): array
    {
        return ['value' => 'float', 'min_subtotal' => 'float'];
    }
}
