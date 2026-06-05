<?php

use App\Models\Mesa;
use App\Models\SessionParticipant;
use App\Services\WaiterService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.customer')] #[Title('Mis pedidos · Piso Cuatro')] class extends Component
{
    public Mesa $mesa;

    public function mount(Mesa $mesa): void
    {
        $this->mesa = $mesa;
    }

    private function participant(): SessionParticipant
    {
        $token = request()->cookie('participant_token');
        $session = $this->mesa->activeSession;

        $participant = ($token && $session)
            ? SessionParticipant::where('token', $token)
                ->where('restaurant_session_id', $session->id)
                ->first()
            : null;

        abort_unless($participant, 403);

        return $participant;
    }

    #[Computed]
    public function orders(): Collection
    {
        return $this->participant()->orders()->with('items')->latest()->get();
    }

    public function callWaiter(WaiterService $waiters): void
    {
        $participant = $this->participant();
        $waiters->call($participant->session, $participant);

        Flux::toast(text: 'Un mesero viene en camino 🔔', variant: 'success', duration: 2000);
    }
}; ?>

@php
    $colorMap = [
        'zinc' => 'bg-zinc-700/60 text-zinc-200',
        'amber' => 'bg-amber-500/20 text-amber-300',
        'blue' => 'bg-blue-500/20 text-blue-300',
        'green' => 'bg-green-500/20 text-green-300',
        'emerald' => 'bg-emerald-500/20 text-emerald-300',
        'red' => 'bg-red-500/20 text-red-300',
    ];
@endphp

<div class="flex min-h-svh flex-col">
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/90 px-5 py-3 backdrop-blur">
        <div>
            <p class="text-xs uppercase tracking-widest text-amber-400/80">Mesa {{ $mesa->numero }}</p>
            <h1 class="text-lg font-semibold">Mis pedidos</h1>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="callWaiter"
                class="rounded-lg border border-zinc-700 px-3 py-1.5 text-sm text-zinc-200 active:scale-95">
                🔔 Mesero
            </button>
            <a href="{{ route('mesa.menu', $mesa) }}" wire:navigate
               class="rounded-lg border border-zinc-700 px-3 py-1.5 text-sm text-zinc-200 active:scale-95">
                Volver al menú
            </a>
        </div>
    </header>

    <main class="flex-1 space-y-4 px-5 py-6">
        @forelse ($this->orders as $order)
            <article wire:key="order-{{ $order->id }}" class="rounded-xl border border-zinc-800 bg-zinc-900 p-4">
                <div class="flex items-center justify-between">
                    <p class="font-semibold">Pedido #{{ $order->numero }}</p>
                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $colorMap[$order->estado->color()] ?? 'bg-zinc-700' }}">
                        {{ $order->estado->label() }}
                    </span>
                </div>

                <ul class="mt-3 space-y-2">
                    @foreach ($order->items as $item)
                        <li class="flex items-center justify-between gap-3 text-sm">
                            <span class="min-w-0">
                                <span class="text-zinc-300">{{ $item->quantity }}×</span>
                                {{ $item->product_name }}
                                @if ($item->notes)<span class="block text-xs text-zinc-500">{{ $item->notes }}</span>@endif
                            </span>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-xs {{ $colorMap[$item->estado->color()] ?? 'bg-zinc-700' }}">
                                {{ $item->estado->label() }}
                            </span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-3 flex items-center justify-between border-t border-zinc-800 pt-3">
                    <span class="text-sm text-zinc-400">Subtotal</span>
                    <span class="font-semibold">{{ $order->subtotal_formatted }}</span>
                </div>
            </article>
        @empty
            <div class="py-16 text-center text-zinc-500">
                <p>Aún no has enviado pedidos.</p>
                <a href="{{ route('mesa.menu', $mesa) }}" wire:navigate class="mt-3 inline-block text-amber-400">Ir al menú</a>
            </div>
        @endforelse
    </main>
</div>
