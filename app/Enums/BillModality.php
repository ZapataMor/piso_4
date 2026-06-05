<?php

namespace App\Enums;

/**
 * Modalidad de pago de una cuenta.
 */
enum BillModality: string
{
    case Unica = 'unica';                 // una persona paga todo
    case Automatica = 'automatica';       // cada quien paga lo que pidió
    case Personalizada = 'personalizada'; // se asigna manualmente cada producto

    public function label(): string
    {
        return match ($this) {
            self::Unica => 'Cuenta única',
            self::Automatica => 'División automática',
            self::Personalizada => 'División personalizada',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Unica => 'Una sola persona paga el total.',
            self::Automatica => 'Cada participante paga únicamente lo que pidió.',
            self::Personalizada => 'Se selecciona qué participante paga cada producto.',
        };
    }
}
