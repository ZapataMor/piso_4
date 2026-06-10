<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionParticipant extends Model
{
    protected $fillable = [
        'restaurant_session_id', 'nombre', 'token', 'is_host', 'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_host' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(RestaurantSession::class, 'restaurant_session_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Total del carrito borrador de este participante. */
    public function cartTotal(): float
    {
        return (float) $this->cartItems()
            ->with('product')
            ->get()
            ->sum(fn (CartItem $item) => (float) ($item->product->price ?? 0) * $item->quantity);
    }
}
