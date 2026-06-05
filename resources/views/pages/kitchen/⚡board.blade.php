<?php

use App\Concerns\HasStationBoard;
use App\Enums\PreparationType;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Cocina · Piso 4')] class extends Component
{
    use HasStationBoard;

    public function stationType(): PreparationType
    {
        return PreparationType::Cocina;
    }
}; ?>

@include('partials.station-board', ['title' => 'Panel de Cocina'])
