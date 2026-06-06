<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Pago confirmado por el personal — notifica a meseros y al cliente
 * (canal público de la mesa).
 */
class PaymentConfirmed implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment) {}

    /** @return array<int, Channel|PrivateChannel> */
    public function broadcastOn(): array
    {
        $mesa = $this->payment->bill->session->mesa;

        return [
            new PrivateChannel('waiters'),
            new Channel('mesa.'.$mesa->qr_token),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.confirmed';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'payment_id' => $this->payment->id,
            'bill_id' => $this->payment->bill_id,
        ];
    }
}
