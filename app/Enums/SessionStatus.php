<?php

namespace App\Enums;

/**
 * Estado de una sesión de mesa.
 */
enum SessionStatus: string
{
    case Activa = 'activa';
    case Cerrada = 'cerrada';

    public function label(): string
    {
        return match ($this) {
            self::Activa => 'Activa',
            self::Cerrada => 'Cerrada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Activa => 'green',
            self::Cerrada => 'zinc',
        };
    }
}
