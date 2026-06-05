<?php

namespace App\Enums;

/**
 * Estado de una línea de pedido (la unidad de trabajo de cada estación).
 * En cocina/bar el cliente ve: Pendiente -> En preparación -> Listo,
 * y el mesero cierra el ciclo marcando Entregado.
 */
enum OrderItemStatus: string
{
    case Pendiente = 'pendiente';
    case EnPreparacion = 'en_preparacion';
    case Listo = 'listo';
    case Entregado = 'entregado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnPreparacion => 'En preparación',
            self::Listo => 'Listo',
            self::Entregado => 'Entregado',
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
            self::Cancelado => 'red',
        };
    }

    public function isActive(): bool
    {
        return ! in_array($this, [self::Entregado, self::Cancelado], true);
    }
}
