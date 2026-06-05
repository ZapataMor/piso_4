<?php

namespace App\Models;

use App\Helpers\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['session_participant_id', 'product_id', 'quantity', 'notes'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'session_participant_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Importe de la línea (precio * cantidad), en número crudo. */
    public function lineTotalRaw(): float
    {
        return (float) ($this->product->price ?? 0) * $this->quantity;
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->lineTotalRaw()));
    }
}
