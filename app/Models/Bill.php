<?php

namespace App\Models;

use App\Enums\BillModality;
use App\Enums\BillStatus;
use App\Enums\PaymentStatus;
use App\Helpers\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $fillable = [
        'restaurant_session_id', 'requested_by_participant_id', 'modalidad',
        'estado', 'total', 'requested_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'modalidad' => BillModality::class,
            'estado' => BillStatus::class,
            'total' => 'decimal:2',
            'requested_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(RestaurantSession::class, 'restaurant_session_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'requested_by_participant_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ----------------------------------------------------------------
    // Accessors / helpers
    // ----------------------------------------------------------------

    protected function totalFormatted(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->total));
    }

    /** Suma de pagos ya confirmados. */
    public function paidTotal(): float
    {
        return (float) $this->payments
            ->where('estado', PaymentStatus::Confirmado)
            ->sum('monto');
    }

    /** Saldo pendiente por confirmar. */
    public function balance(): float
    {
        return max(0, (float) $this->total - $this->paidTotal());
    }

    public function isFullyPaid(): bool
    {
        return $this->balance() <= 0 && $this->total > 0;
    }
}
