<?php

use App\Concerns\HasStationBoard;
use App\Enums\PreparationType;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Cocina · Piso 4')] class extends Component
{
    use HasStationBoard;

    public function stationType(): PreparationType
    {
        return PreparationType::Cocina;
    }

    /** Reverb: refresca el tablero al llegar pedidos o cambios de estado. */
    #[On('echo-private:kitchen,.order.placed')]
    #[On('echo-private:kitchen,.order.item.status')]
    public function onRealtime(): void
    {
        unset($this->columns);
    }
}; ?>

@include('partials.station-board', ['title' => 'Panel de Cocina'])
