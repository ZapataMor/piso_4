<?php

use App\Concerns\HasStationBoard;
use App\Enums\PreparationType;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Bar · Piso 4')] class extends Component
{
    use HasStationBoard;

    public function stationType(): PreparationType
    {
        return PreparationType::Bar;
    }

    /** Reverb: refresca el tablero al llegar pedidos o cambios de estado. */
    #[On('echo-private:bar,.order.placed')]
    #[On('echo-private:bar,.order.item.status')]
    public function onRealtime(): void
    {
        unset($this->columns);
    }
}; ?>

@include('partials.station-board', ['title' => 'Panel de Bar'])
