<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Restaurant extends Model
{
    protected $fillable = [
        'user_id', 'name', 'cover_image', 'logo', 'cuisines', 'categories', 'rating',
        'review_count', 'distance_km', 'eta_minutes', 'delivery_fee', 'min_order',
        'is_open', 'has_promotion', 'promotion_text', 'price_level', 'address',
        'opening_hours', 'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'cuisines' => 'array',
            'categories' => 'array',
            'opening_hours' => 'array',
            'rating' => 'float',
            'distance_km' => 'float',
            'delivery_fee' => 'float',
            'min_order' => 'float',
            'is_open' => 'boolean',
            'has_promotion' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Whether the restaurant is open at the current moment, computed from the
     * per-day opening_hours the restaurant set on its dashboard. A day entry
     * shaped `['day' => 'Monday', 'open' => '09:00', 'close' => '22:00']` is
     * open within that window; `closed => true` or missing open/close = closed.
     * Falls back to the stored is_open flag when no hours are configured.
     */
    public function isOpenNow(): bool
    {
        $hours = $this->opening_hours ?? [];
        if (empty($hours)) {
            return (bool) $this->is_open;
        }

        $now = Carbon::now();
        $today = $now->format('l'); // Monday, Tuesday, ...

        $entry = collect($hours)->first(fn ($h) => ($h['day'] ?? null) === $today);
        if (! $entry || ! empty($entry['closed']) || empty($entry['open']) || empty($entry['close'])) {
            return false;
        }

        $open = Carbon::createFromFormat('H:i', $entry['open']);
        $close = Carbon::createFromFormat('H:i', $entry['close']);
        $current = Carbon::createFromFormat('H:i', $now->format('H:i'));

        // Handle past-midnight closing (e.g. 18:00 → 02:00).
        if ($close->lessThanOrEqualTo($open)) {
            return $current->greaterThanOrEqualTo($open) || $current->lessThanOrEqualTo($close);
        }

        return $current->betweenIncluded($open, $close);
    }
}
