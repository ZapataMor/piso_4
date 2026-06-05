<?php

namespace App\Contracts;

use App\Models\Payment;

/**
 * Contrato desacoplado de WhatsApp. La implementación por defecto genera
 * un deep link wa.me (sin API, sin costo). Mañana se puede sustituir por
 * la WhatsApp Cloud API sin tocar el dominio: basta re-vincular esta
 * interfaz en el contenedor.
 */
interface WhatsAppGateway
{
    /** Mensaje de transferencia para un pago (mesa, sesión, valor, banco, instrucciones). */
    public function paymentMessage(Payment $payment): string;

    /** Enlace que abre WhatsApp hacia el número del restaurante con el mensaje prellenado. */
    public function paymentLink(Payment $payment): string;
}
