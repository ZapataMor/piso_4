<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Configuración key/value del restaurante. Provee get()/set() estáticos
 * con casteo según 'type' y cache para evitar consultas repetidas.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public const CACHE_KEY = 'settings.all';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** @return Collection<string, Setting> */
    public static function allCached()
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::all()->keyBy('key'));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::allCached()->get($key);

        return $setting ? $setting->castedValue() : $default;
    }

    public static function set(string $key, mixed $value, string $type = 'string', ?string $group = null): self
    {
        $stored = $type === 'json' ? json_encode($value) : (string) $value;

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type, 'group' => $group],
        );
    }

    public function castedValue(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $this->value, true),
            default => $this->value,
        };
    }
}
