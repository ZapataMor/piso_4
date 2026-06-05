<?php

namespace App\Models;

use App\Enums\WaiterCallStatus;
use App\Enums\WaiterCallType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaiterCall extends Model
{
    protected $fillable = [
        'restaurant_session_id', 'session_participant_id', 'mesa_id',
        'tipo', 'estado', 'note', 'attended_by', 'attended_at',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => WaiterCallType::class,
            'estado' => WaiterCallStatus::class,
            'attended_at' => 'datetime',
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

    public function attendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attended_by');
    }

    /** @param  Builder<WaiterCall>  $query */
    public function scopePending(Builder $query): void
    {
        $query->where('estado', WaiterCallStatus::Pendiente->value);
    }
}
