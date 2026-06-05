<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Helpers\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    protected $fillable = [
        'bill_id', 'session_participant_id', 'metodo', 'estado', 'monto',
        'payer_nombre', 'payer_telefono', 'reference', 'confirmed_by', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'metodo' => PaymentMethod::class,
            'estado' => PaymentStatus::class,
            'monto' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'session_participant_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /** Líneas cubiertas por este pago (división personalizada). */
    public function orderItems(): BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class, 'payment_order_item');
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    protected function montoFormatted(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->monto));
    }

    // ----------------------------------------------------------------
    // Scopes
    // ----------------------------------------------------------------

    /** @param  Builder<Payment>  $query */
    public function scopeConfirmed(Builder $query): void
    {
        $query->where('estado', PaymentStatus::Confirmado->value);
    }

    /** @param  Builder<Payment>  $query */
    public function scopePending(Builder $query): void
    {
        $query->where('estado', PaymentStatus::Pendiente->value);
    }
}
