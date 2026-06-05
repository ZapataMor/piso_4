<?php

namespace App\Enums;

/**
 * Estado de un llamado al mesero.
 */
enum WaiterCallStatus: string
{
    case Pendiente = 'pendiente';
    case Atendido = 'atendido';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Atendido => 'Atendido',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'red',
            self::Atendido => 'green',
        };
    }
}
