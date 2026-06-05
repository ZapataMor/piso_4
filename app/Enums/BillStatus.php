<?php

namespace App\Enums;

/**
 * Ciclo de vida de una cuenta.
 */
enum BillStatus: string
{
    case Solicitada = 'solicitada';
    case EnPago = 'en_pago';
    case Pagada = 'pagada';
    case Cerrada = 'cerrada';
    case Cancelada = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::Solicitada => 'Solicitada',
            self::EnPago => 'En pago',
            self::Pagada => 'Pagada',
            self::Cerrada => 'Cerrada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Solicitada => 'amber',
            self::EnPago => 'blue',
            self::Pagada => 'green',
            self::Cerrada => 'emerald',
            self::Cancelada => 'red',
        };
    }
}
