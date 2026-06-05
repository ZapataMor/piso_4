<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use App\Enums\PreparationType;
use App\Helpers\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'unit_price', 'quantity',
        'tipo_preparacion', 'estado', 'notes', 'prepared_by',
        'started_at', 'ready_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'tipo_preparacion' => PreparationType::class,
            'estado' => OrderItemStatus::class,
            'started_at' => 'datetime',
            'ready_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /** Pagos que cubren esta línea (división personalizada). */
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_order_item');
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function lineTotalRaw(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->lineTotalRaw()));
    }

    // ----------------------------------------------------------------
    // Scopes
    // ----------------------------------------------------------------

    /** @param  Builder<OrderItem>  $query */
    public function scopeStation(Builder $query, PreparationType $type): void
    {
        $query->where('tipo_preparacion', $type->value);
    }

    /** @param  Builder<OrderItem>  $query */
    public function scopeKitchen(Builder $query): void
    {
        $query->where('tipo_preparacion', PreparationType::Cocina->value);
    }

    /** @param  Builder<OrderItem>  $query */
    public function scopeBar(Builder $query): void
    {
        $query->where('tipo_preparacion', PreparationType::Bar->value);
    }

    /** Líneas que siguen activas (no entregadas ni canceladas). @param  Builder<OrderItem>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->whereNotIn('estado', [OrderItemStatus::Entregado->value, OrderItemStatus::Cancelado->value]);
    }
}
