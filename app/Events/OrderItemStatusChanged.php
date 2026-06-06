<?php

namespace App\Events;

use App\Enums\PreparationType;
use App\Models\OrderItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Cambió el estado de una línea (aceptado / listo / entregado).
 * Va a su estación, a meseros y al canal público de la mesa (cliente).
 */
class OrderItemStatusChanged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public OrderItem $item) {}

    /** @return array<int, Channel|PrivateChannel> */
    public function broadcastOn(): array
    {
        $this->item->loadMissing('order.mesa');
        $station = $this->item->tipo_preparacion === PreparationType::Cocina ? 'kitchen' : 'bar';

        return [
            new PrivateChannel($station),
            new PrivateChannel('waiters'),
            new Channel('mesa.'.$this->item->order->mesa->qr_token),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.item.status';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'item_id' => $this->item->id,
            'order_id' => $this->item->order_id,
            'estado' => $this->item->estado->value,
        ];
    }
}
