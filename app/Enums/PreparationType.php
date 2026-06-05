<?php

namespace App\Enums;

/**
 * Estación que prepara un producto. Enruta cada order_item a cocina o bar.
 */
enum PreparationType: string
{
    case Cocina = 'cocina';
    case Bar = 'bar';

    public function label(): string
    {
        return match ($this) {
            self::Cocina => 'Cocina',
            self::Bar => 'Bar',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cocina => 'amber',
            self::Bar => 'sky',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
