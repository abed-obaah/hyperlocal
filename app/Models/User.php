<?php

namespace App\Models;

use App\Services\ExpoPushService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'avatar', 'is_available',
        'wallet_balance', 'device_token', 'rider_status',
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

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    public function riderEarnings()
    {
        return $this->hasMany(RiderEarning::class, 'rider_id');
    }

    /**
     * Move money into this user's wallet and record an audit row. Wrapped in a
     * transaction with a row lock so concurrent credits can't clobber the balance.
     */
    public function creditWallet(float $amount, string $reason, ?Order $order = null, array $meta = []): WalletTransaction
    {
        return $this->walletMovement('credit', $amount, $reason, $order, $meta);
    }

    /** Move money out of this user's wallet and record an audit row. */
    public function debitWallet(float $amount, string $reason, ?Order $order = null, array $meta = []): WalletTransaction
    {
        return $this->walletMovement('debit', $amount, $reason, $order, $meta);
    }

    private function walletMovement(string $type, float $amount, string $reason, ?Order $order, array $meta): WalletTransaction
    {
        return DB::transaction(function () use ($type, $amount, $reason, $order, $meta) {
            $fresh = static::whereKey($this->getKey())->lockForUpdate()->first();
            $balance = (float) $fresh->wallet_balance + ($type === 'credit' ? $amount : -$amount);
            $fresh->update(['wallet_balance' => $balance]);
            $this->wallet_balance = $balance;

            return $fresh->walletTransactions()->create([
                'type' => $type,
                'amount' => $amount,
                'reason' => $reason,
                'order_id' => $order?->id,
                'balance_after' => $balance,
                'meta' => $meta ?: null,
            ]);
        });
    }

    /**
     * Single entry point for notifying any user: writes the in-app feed row and
     * fires an Expo push. Push failures are swallowed by the service so they can
     * never break the surrounding order flow.
     */
    public function notifyApp(string $title, string $body, string $icon = 'notifications', array $data = [], string $type = 'order'): void
    {
        UserNotification::create([
            'user_id' => $this->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'icon' => $icon,
            'data' => $data,
        ]);

        if ($this->device_token) {
            app(ExpoPushService::class)->send([$this->device_token], $title, $body, $data);
        }
    }
}
