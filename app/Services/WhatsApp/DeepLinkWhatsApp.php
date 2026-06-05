<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppGateway;
use App\Helpers\Money;
use App\Models\Payment;
use App\Models\Setting;

/**
 * Implementación por deep link wa.me. NO sube ni almacena comprobantes:
 * el cliente envía el comprobante por WhatsApp al número del restaurante y
 * el personal confirma el pago manualmente en el sistema.
 */
class DeepLinkWhatsApp implements WhatsAppGateway
{
    public function paymentMessage(Payment $payment): string
    {
        $bill = $payment->bill;
        $session = $bill->session;
        $mesa = $session->mesa;
        $payer = $payment->payer_nombre ?: $payment->participant?->nombre ?: 'Cliente';

        $lines = [
            "Hola, soy {$payer}.",
            '',
            "Mesa {$mesa->numero} · Sesión {$session->codigo}",
            '',
            'Valor a pagar:',
            Money::format($payment->monto),
            '',
            'Realiza la transferencia a:',
            (string) Setting::get('bank_name', 'Banco'),
            'Tipo: '.Setting::get('bank_account_type', 'Ahorros'),
            'Cuenta: '.Setting::get('bank_account', '—'),
            'Titular: '.Setting::get('bank_holder', '—'),
            'NIT/CC: '.Setting::get('bank_doc', '—'),
            '',
            'Una vez realizado el pago, envía el comprobante respondiendo a este mismo chat. ¡Gracias!',
        ];

        return implode("\n", $lines);
    }

    public function paymentLink(Payment $payment): string
    {
        $phone = preg_replace('/\D/', '', (string) Setting::get('whatsapp_number', ''));

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($this->paymentMessage($payment));
    }
}
