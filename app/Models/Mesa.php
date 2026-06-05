<?php

namespace App\Models;

use App\Enums\SessionStatus;
use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model
{
    use SoftDeletes;

    protected $table = 'mesas';

    protected $fillable = ['numero', 'nombre', 'qr_token', 'estado', 'capacidad'];

    protected function casts(): array
    {
        return [
            'estado' => TableStatus::class,
            'numero' => 'integer',
            'capacidad' => 'integer',
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(RestaurantSession::class);
    }

    /** La sesión activa actual de la mesa (si existe). */
    public function activeSession(): HasOne
    {
        return $this->hasOne(RestaurantSession::class)
            ->where('estado', SessionStatus::Activa->value)
            ->latestOfMany();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ----------------------------------------------------------------
    // Accessors / helpers
    // ----------------------------------------------------------------

    /** URL pública del QR: /mesa/{token}. */
    protected function publicUrl(): Attribute
    {
        return Attribute::get(fn () => url('/mesa/'.$this->qr_token));
    }

    public function isAvailable(): bool
    {
        return $this->estado === TableStatus::Disponible;
    }

    // ----------------------------------------------------------------
    // Scopes
    // ----------------------------------------------------------------

    /** @param  Builder<Mesa>  $query */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('estado', TableStatus::Disponible->value);
    }
}
