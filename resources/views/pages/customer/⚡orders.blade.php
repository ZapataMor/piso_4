<?php

use App\Concerns\ResolvesParticipant;
use App\Helpers\Money;
use App\Models\Mesa;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.customer')] #[Title('Pedidos de la mesa · Piso Cuatro')] class extends Component
{
    use ResolvesParticipant;

    public Mesa $mesa;

    public function mount(Mesa $mesa): void
    {
        $this->mesa = $mesa;
    }

    #[Computed]
    public function orderGroups(): Collection
    {
        $participant = $this->participant();
        $orders = $participant->session->orders()
            ->with(['items', 'participant'])
            ->oldest('numero')
            ->get();

        return $orders
            ->groupBy('session_participant_id')
            ->map(function (Collection $orders) {
                $firstOrder = $orders->first();

                return [
                    'participant' => $firstOrder?->participant,
                    'orders' => $orders->values(),
                    'orderCount' => $orders->count(),
                    'subtotal' => (float) $orders->sum(fn ($order) => (float) $order->subtotal),
                    'firstOrderNumber' => (int) ($firstOrder?->numero ?? 0),
                ];
            })
            ->sortBy('firstOrderNumber')
            ->values();
    }

    #[Computed]
    public function tableTotal(): float
    {
        return (float) $this->orderGroups->sum('subtotal');
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
            <h1 class="text-lg font-semibold text-zinc-100">Pedidos de la mesa</h1>
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
        @if ($this->orderGroups->isNotEmpty())
            @foreach ($this->orderGroups as $group)
                <article wire:key="participant-orders-{{ $group['participant']?->id ?? 'guest' }}" class="card-base">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-semibold text-zinc-100">{{ $group['participant']?->nombre ?? 'Cliente' }}</h2>
                            <p class="mt-1 text-xs font-medium text-muted">{{ $group['orderCount'] }} {{ $group['orderCount'] === 1 ? 'pedido' : 'pedidos' }}</p>
                        </div>
                        <span class="shrink-0 text-lg font-bold text-amber-400">{{ Money::format($group['subtotal']) }}</span>
                    </div>

                    <div class="mt-4 space-y-4">
                        @foreach ($group['orders'] as $order)
                            <section wire:key="order-{{ $order->id }}" class="border-t border-zinc-800 pt-4 first:border-t-0 first:pt-0">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-semibold text-zinc-100">Pedido #{{ $order->numero }}</p>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $colorMap[$order->estado->color()] ?? 'bg-zinc-700 text-zinc-300' }}">
                                        {{ $order->estado->label() }}
                                    </span>
                                </div>

                                <ul class="space-y-2 py-3">
                                    @foreach ($order->items as $item)
                                        <li class="flex items-center justify-between gap-3 border-b border-zinc-800 py-1.5 text-sm last:border-0">
                                            <span class="min-w-0 flex-1">
                                                <span class="text-muted-sm">{{ $item->quantity }}×</span>
                                                <span class="text-zinc-300">{{ $item->product_name }}</span>
                                                @if ($item->notes)
                                                    <span class="mt-0.5 block text-xs text-muted-sm">{{ $item->notes }}</span>
                                                @endif
                                            </span>
                                            <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold {{ $colorMap[$item->estado->color()] ?? 'bg-zinc-700 text-zinc-300' }}">
                                                {{ $item->estado->label() }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endforeach
                    </div>

                    <div class="mt-3 flex items-center justify-between border-t border-zinc-800 pt-3">
                        <span class="text-sm text-muted">Subtotal de {{ $group['participant']?->nombre ?? 'cliente' }}</span>
                        <span class="font-semibold text-zinc-100">{{ Money::format($group['subtotal']) }}</span>
                    </div>
                </article>
            @endforeach

            <section class="card-base flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-muted">Total de la mesa</span>
                <span class="text-2xl font-bold text-zinc-100">{{ Money::format($this->tableTotal) }}</span>
            </section>
        @else
            <div class="py-16 text-center">
                <p class="mb-4 text-muted">Aún no hay pedidos en la mesa</p>
                <a href="{{ route('mesa.menu', $mesa) }}" wire:navigate class="btn-primary inline-block">
                    ← Volver al menú
                </a>
            </div>
        @endif
    </main>
</div>
