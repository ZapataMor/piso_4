<?php

namespace App\Enums;

/**
 * Método de pago de un Payment.
 */
enum PaymentMethod: string
{
    case Efectivo = 'efectivo';
    case Transferencia = 'transferencia';
    case Tarjeta = 'tarjeta';

    public function label(): string
    {
        return match ($this) {
            self::Efectivo => 'Efectivo',
            self::Transferencia => 'Transferencia',
            self::Tarjeta => 'Tarjeta',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Efectivo => 'banknotes',
            self::Transferencia => 'device-phone-mobile',
            self::Tarjeta => 'credit-card',
        };
    }

    /** Solo la transferencia dispara el flujo de WhatsApp. */
    public function requiresWhatsApp(): bool
    {
        return $this === self::Transferencia;
    }
}
