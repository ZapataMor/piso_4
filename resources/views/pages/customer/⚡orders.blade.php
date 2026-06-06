<?php

use App\Concerns\ResolvesParticipant;
use App\Models\Mesa;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.customer')] #[Title('Mis pedidos · Piso Cuatro')] class extends Component
{
    use ResolvesParticipant;

    public Mesa $mesa;

    public function mount(Mesa $mesa): void
    {
        $this->mesa = $mesa;
    }

    #[Computed]
    public function orders(): Collection
    {
        return $this->participant()->orders()->with('items')->latest()->get();
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

<div class="flex min-h-svh flex-col"
     x-data
     x-init="window.Echo && window.Echo.channel('mesa.{{ $mesa->qr_token }}').listen('.order.item.status', () => $wire.$refresh())">
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/90 px-5 py-4 backdrop-blur">
        <div>
            <p class="header-subtitle">Mesa {{ $mesa->numero }}</p>
            <h1 class="text-lg font-semibold text-zinc-100">Mis pedidos</h1>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="callWaiter" class="btn-secondary text-sm">
                🔔 Mesero
            </button>
            <a href="{{ route('mesa.bill', $mesa) }}" wire:navigate class="btn-primary text-sm">
                Cuenta
            </a>
            <a href="{{ route('mesa.menu', $mesa) }}" wire:navigate class="btn-secondary text-sm">
                Menú
            </a>
        </div>
    </header>

    <main class="flex-1 space-y-4 px-5 py-6">
        @forelse ($this->orders as $order)
            <article wire:key="order-{{ $order->id }}" class="card-base">
                <div class="flex items-center justify-between mb-4">
                    <p class="font-semibold text-zinc-100">Pedido #{{ $order->numero }}</p>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $colorMap[$order->estado->color()] ?? 'bg-zinc-700 text-zinc-300' }}">
                        {{ $order->estado->label() }}
                    </span>
                </div>

                <ul class="space-y-2 py-3">
                    @foreach ($order->items as $item)
                        <li class="flex items-center justify-between gap-3 text-sm py-1.5 border-b border-zinc-800 last:border-0">
                            <span class="min-w-0 flex-1">
                                <span class="text-muted-sm">{{ $item->quantity }}×</span>
                                <span class="text-zinc-300">{{ $item->product_name }}</span>
                                @if ($item->notes)<span class="block text-xs text-muted-sm mt-0.5">{{ $item->notes }}</span>@endif
                            </span>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold {{ $colorMap[$item->estado->color()] ?? 'bg-zinc-700 text-zinc-300' }}">
                                {{ $item->estado->label() }}
                            </span>
                        </li>
                    @endforeach
                </ul>

                <div class="flex items-center justify-between border-t border-zinc-800 pt-3 mt-3">
                    <span class="text-sm text-muted">Subtotal</span>
                    <span class="font-semibold text-zinc-100">{{ $order->subtotal_formatted }}</span>
                </div>
            </article>
        @empty
            <div class="py-16 text-center">
                <p class="text-muted mb-4">Aún no has enviado pedidos</p>
                <a href="{{ route('mesa.menu', $mesa) }}" wire:navigate class="btn-primary inline-block">
                    ← Volver al menú
                </a>
            </div>
        @endforelse
    </main>
</div>
