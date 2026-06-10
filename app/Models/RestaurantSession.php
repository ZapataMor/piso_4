<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class RestaurantSession extends Model
{
    protected $fillable = ['mesa_id', 'codigo', 'estado', 'fecha_inicio', 'fecha_fin'];

    protected function casts(): array
    {
        return [
            'estado' => SessionStatus::class,
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Todas las líneas de pedido de la sesión (a través de orders). */
    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Order::class);
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }

    public function waiterCalls(): HasMany
    {
        return $this->hasMany(WaiterCall::class);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->estado === SessionStatus::Activa;
    }

    /** Total consumido en la sesión (excluye líneas canceladas). */
    public function currentTotal(): float
    {
        if ($this->relationLoaded('orderItems')) {
            return (float) $this->orderItems
                ->reject(fn (OrderItem $item) => $item->estado === OrderItemStatus::Cancelado)
                ->sum(fn (OrderItem $item) => (float) $item->unit_price * $item->quantity);
        }

        return (float) $this->orderItems()
            ->where('order_items.estado', '!=', OrderItemStatus::Cancelado->value)
            ->sum(DB::raw('unit_price * quantity'));
    }

    // ----------------------------------------------------------------
    // Scopes
    // ----------------------------------------------------------------

    /** @param  Builder<RestaurantSession>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('estado', SessionStatus::Activa->value);
    }
}
