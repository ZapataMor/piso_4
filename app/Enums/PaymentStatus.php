<?php

namespace App\Enums;

/**
 * Estado de un pago. La confirmación la realiza el personal manualmente
 * (no hay carga de comprobantes en la app).
 */
enum PaymentStatus: string
{
    case Pendiente = 'pago_pendiente';
    case Confirmado = 'pago_confirmado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pago pendiente',
            self::Confirmado => 'Pago confirmado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'amber',
            self::Confirmado => 'green',
            self::Cancelado => 'red',
        };
    }
}
