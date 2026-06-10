<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Nuevo pedido confirmado. Llega a las estaciones que correspondan
 * (cocina/bar según los productos) y a los meseros.
 */
class OrderPlaced implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order) {}

    /** @return array<int, Channel|PrivateChannel> */
    public function broadcastOn(): array
    {
        $this->order->loadMissing(['items', 'mesa']);
        $types = $this->order->items->map(fn ($i) => $i->tipo_preparacion->value)->unique();

        $channels = [new PrivateChannel('waiters')];

        if ($types->contains('cocina')) {
            $channels[] = new PrivateChannel('kitchen');
        }

        if ($types->contains('bar')) {
            $channels[] = new PrivateChannel('bar');
        }

        $channels[] = new Channel('mesa.'.$this->order->mesa->qr_token);

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'mesa_id' => $this->order->mesa_id,
            'numero' => $this->order->numero,
        ];
    }
}
