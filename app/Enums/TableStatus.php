<?php

namespace App\Enums;

/**
 * Estado de una mesa física.
 */
enum TableStatus: string
{
    case Disponible = 'disponible';
    case Ocupada = 'ocupada';
    case FueraDeServicio = 'fuera_de_servicio';

    public function label(): string
    {
        return match ($this) {
            self::Disponible => 'Disponible',
            self::Ocupada => 'Ocupada',
            self::FueraDeServicio => 'Fuera de servicio',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Disponible => 'green',
            self::Ocupada => 'amber',
            self::FueraDeServicio => 'zinc',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
