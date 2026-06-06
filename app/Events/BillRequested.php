<?php

namespace App\Events;

use App\Models\Bill;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Solicitud de cuenta — notifica a los meseros. */
class BillRequested implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Bill $bill) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('waiters')];
    }

    public function broadcastAs(): string
    {
        return 'bill.requested';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'bill_id' => $this->bill->id,
            'mesa_id' => $this->bill->session->mesa_id,
        ];
    }
}
