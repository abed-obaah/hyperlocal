<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'avatar', 'is_available', 'wallet_balance',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_available' => 'boolean',
            'wallet_balance' => 'decimal:2',
        ];
    }

    public function isRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function restaurant()
    {
        return $this->hasOne(Restaurant::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'rider_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /** Restaurants this user has favourited. */
    public function favoriteRestaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'favorites')->withTimestamps();
    }

    /** In-app notification feed (kept separate from Laravel's Notifiable table). */
    public function appNotifications()
    {
        return $this->hasMany(UserNotification::class)->latest();
    }
}
