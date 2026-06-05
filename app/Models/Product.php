<?php

namespace App\Models;

use App\Enums\PreparationType;
use App\Helpers\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'tipo_preparacion',
        'group_label', 'image', 'note', 'is_available', 'is_featured', 'display_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'tipo_preparacion' => PreparationType::class,
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    /** Precio formateado en COP, ej: "$45.000" (o "—" si no tiene). */
    protected function priceFormatted(): Attribute
    {
        return Attribute::get(fn () => $this->price !== null ? Money::format($this->price) : '—');
    }

    public function goesToBar(): bool
    {
        return $this->tipo_preparacion === PreparationType::Bar;
    }

    public function goesToKitchen(): bool
    {
        return $this->tipo_preparacion === PreparationType::Cocina;
    }

    // ----------------------------------------------------------------
    // Scopes
    // ----------------------------------------------------------------

    /** @param  Builder<Product>  $query */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_available', true);
    }

    /** @param  Builder<Product>  $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('display_order')->orderBy('name');
    }

    /** @param  Builder<Product>  $query */
    public function scopeForStation(Builder $query, PreparationType $type): void
    {
        $query->where('tipo_preparacion', $type->value);
    }
}
