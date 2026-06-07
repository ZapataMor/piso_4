<?php

namespace App\Events;

use App\Models\RestaurantSession;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Una sesión de mesa se abrió o se cerró. Notifica a meseros y admin
 * (canal `waiters`) para refrescar el conteo de mesas activas y la
 * disponibilidad sin recargar la página.
 */
class SessionStatusChanged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public RestaurantSession $session) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('waiters')];
    }

    public function broadcastAs(): string
    {
        return 'session.changed';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'mesa_id' => $this->session->mesa_id,
            'estado' => $this->session->estado->value,
        ];
    }
}
