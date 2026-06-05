<?php

namespace App\Helpers;

/**
 * Formateo de moneda colombiana (COP). Se implementa sin la extensión
 * intl (deshabilitada en este entorno): separador de miles con punto,
 * sin decimales. Ej: 45000 -> "$45.000".
 */
class Money
{
    public static function format(int|float|string|null $amount, bool $symbol = true): string
    {
        $value = (float) ($amount ?? 0);
        $formatted = number_format($value, 0, ',', '.');

        return $symbol ? '$'.$formatted : $formatted;
    }
}
