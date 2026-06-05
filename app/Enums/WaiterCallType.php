<?php

namespace App\Enums;

/**
 * Motivo de un llamado al mesero.
 */
enum WaiterCallType: string
{
    case Llamado = 'llamado';   // "Llamar Mesero"
    case Cuenta = 'cuenta';     // "Solicitar Cuenta"

    public function label(): string
    {
        return match ($this) {
            self::Llamado => 'Llamado de mesero',
            self::Cuenta => 'Solicitud de cuenta',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Llamado => 'bell',
            self::Cuenta => 'receipt-percent',
        };
    }
}
