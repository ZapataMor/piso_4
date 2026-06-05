<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Helpers\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'restaurant_session_id', 'session_participant_id', 'mesa_id', 'numero',
        'estado', 'subtotal', 'notes',
        'placed_at', 'started_at', 'ready_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'estado' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'numero' => 'integer',
            'placed_at' => 'datetime',
            'started_at' => 'datetime',
            'ready_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(RestaurantSession::class, 'restaurant_session_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'session_participant_id');
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    protected function subtotalFormatted(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->subtotal));
    }

    // ----------------------------------------------------------------
    // Scopes
    // ----------------------------------------------------------------

    /** Pedidos vivos (no facturados ni cancelados). @param  Builder<Order>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->whereNotIn('estado', [OrderStatus::Facturado->value, OrderStatus::Cancelado->value]);
    }
}
