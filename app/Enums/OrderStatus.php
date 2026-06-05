<?php

namespace App\Enums;

/**
 * Estado agregado de un pedido (derivado de sus order_items).
 */
enum OrderStatus: string
{
    case Pendiente = 'pendiente';
    case EnPreparacion = 'en_preparacion';
    case Listo = 'listo';
    case Entregado = 'entregado';
    case Facturado = 'facturado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnPreparacion => 'En preparación',
            self::Listo => 'Listo',
            self::Entregado => 'Entregado',
            self::Facturado => 'Facturado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'zinc',
            self::EnPreparacion => 'amber',
            self::Listo => 'blue',
            self::Entregado => 'green',
            self::Facturado => 'emerald',
            self::Cancelado => 'red',
        };
    }

    /** Estados que cuentan como "cerrados" (no operan en tableros). */
    public function isClosed(): bool
    {
        return in_array($this, [self::Facturado, self::Cancelado], true);
    }
}
