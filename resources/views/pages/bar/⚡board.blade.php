<?php

use App\Concerns\HasStationBoard;
use App\Enums\PreparationType;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Bar · Piso 4')] class extends Component
{
    use HasStationBoard;

    public function stationType(): PreparationType
    {
        return PreparationType::Bar;
    }
}; ?>

@include('partials.station-board', ['title' => 'Panel de Bar'])
