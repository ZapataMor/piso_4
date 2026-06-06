<?php

namespace App\Events;

use App\Models\WaiterCall;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Llamado de mesero (o solicitud de cuenta) — notifica a los meseros. */
class WaiterCalled implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public WaiterCall $call) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('waiters')];
    }

    public function broadcastAs(): string
    {
        return 'waiter.called';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'call_id' => $this->call->id,
            'mesa_id' => $this->call->mesa_id,
            'tipo' => $this->call->tipo->value,
        ];
    }
}
