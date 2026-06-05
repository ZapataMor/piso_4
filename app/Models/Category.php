<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'slug', 'name', 'kicker', 'subtitle', 'photo', 'bg', 'display_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Productos disponibles, ordenados — para el menú interactivo. */
    public function availableProducts(): HasMany
    {
        return $this->products()->where('is_available', true)->orderBy('display_order');
    }

    /** @param  Builder<Category>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param  Builder<Category>  $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('display_order')->orderBy('name');
    }
}
