<?php

use App\Concerns\AdminOnly;
use App\Enums\OrderStatus;
use App\Helpers\Money;
use App\Models\Order;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Pedidos · Piso 4')] class extends Component
{
    use AdminOnly;

    public string $filter = 'all';

    #[On('echo-private:waiters,.order.placed')]
    #[On('echo-private:waiters,.order.item.status')]
    public function onRealtime(): void
    {
        unset($this->orders, $this->tableGroups, $this->totalCount);
    }

    #[Computed]
    public function orders(): Collection
    {
        return Order::query()
            ->with(['mesa', 'participant', 'items'])
            ->when($this->filter !== 'all', fn ($q) => $q->where('estado', $this->filter))
            ->latest('placed_at')
            ->limit(80)
            ->get();
    }

    #[Computed]
    public function tableGroups(): Collection
    {
        return $this->orders
            ->groupBy(fn (Order $order) => $order->mesa_id ?: 'sin-mesa')
            ->map(function (Collection $orders) {
                $firstOrder = $orders->first();
                $mesa = $firstOrder?->mesa;
                $clients = $orders
                    ->pluck('participant.nombre')
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'mesa' => $mesa,
                    'orders' => $orders->sortByDesc('placed_at')->values(),
                    'clients' => $clients,
                    'ordersCount' => $orders->count(),
                    'itemsCount' => (int) $orders->sum(fn (Order $order) => $order->items->sum('quantity')),
                    'total' => (float) $orders->sum(fn (Order $order) => (float) $order->subtotal),
                    'latestAt' => $orders->max('placed_at'),
                    'sortNumber' => $mesa?->numero ?? 999999,
                ];
            })
            ->sortBy('sortNumber')
            ->values();
    }

    #[Computed]
    public function totalCount(): int
    {
        return Order::count();
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        unset($this->orders, $this->tableGroups);
    }
}; ?>

@php($colorMap = [
    'zinc' => 'bg-zinc-700/60 text-zinc-200',
    'amber' => 'bg-amber-500/20 text-amber-700 dark:text-amber-300',
    'blue' => 'bg-blue-500/20 text-blue-700 dark:text-blue-300',
    'green' => 'bg-green-500/20 text-green-700 dark:text-green-300',
    'emerald' => 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300',
    'red' => 'bg-red-500/20 text-red-700 dark:text-red-300',
])

<div>
    <div class="piso-in">
        <p class="kicker">Administración</p>
        <div class="head-row mt-2.5 flex items-end justify-between gap-6">
            <h1 class="header-title">Pedidos</h1>
        </div>
    </div>
    <div class="piso-rule my-7"></div>

    <div class="ptoolbar piso-in piso-in-1">
        <div class="pchips">
            @php($filters = array_merge(['all' => 'Todos'], collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()))
            @foreach ($filters as $value => $label)
                <button type="button" wire:click="setFilter('{{ $value }}')" class="pchip {{ $filter === $value ? 'active' : '' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <section class="piso-in piso-in-2 grid gap-5 lg:grid-cols-2 2xl:grid-cols-3">
        @forelse ($this->tableGroups as $group)
            <article wire:key="orders-table-{{ $group['mesa']?->id ?? 'none' }}" class="rounded-xl border border-zinc-800 bg-zinc-950/55 p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <p class="kicker mb-2">{{ $group['ordersCount'] }} {{ $group['ordersCount'] === 1 ? 'pedido' : 'pedidos' }} · {{ $group['itemsCount'] }} ítems</p>
                        <h2 class="text-2xl font-semibold text-zinc-100">
                            Mesa {{ $group['mesa']?->numero ?? 'sin asignar' }}
                        </h2>
                        @if ($group['clients']->isNotEmpty())
                            <p class="mt-2 text-sm text-zinc-400">
                                Clientes: {{ $group['clients']->join(', ') }}
                            </p>
                        @else
                            <p class="mt-2 text-sm text-zinc-500">Clientes sin nombre registrado</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-right sm:flex sm:items-center">
                        <div class="rounded-lg border border-zinc-800 bg-zinc-900/70 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Total mesa</p>
                            <p class="metal mt-1 text-xl font-bold">{{ Money::format($group['total']) }}</p>
                        </div>
                        <div class="rounded-lg border border-zinc-800 bg-zinc-900/70 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Último pedido</p>
                            <p class="mt-1 text-lg font-semibold text-zinc-100">{{ $group['latestAt']?->format('H:i') ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid gap-3">
                    @foreach ($group['orders'] as $order)
                        <section wire:key="order-{{ $order->id }}" class="rounded-lg border border-zinc-800 bg-zinc-900/60 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-semibold text-zinc-100">Pedido #{{ $order->numero }}</h3>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $colorMap[$order->estado->color()] ?? '' }}">
                                            {{ $order->estado->label() }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-zinc-400">
                                        {{ $order->participant?->nombre ?? 'Cliente sin nombre' }} · {{ $order->placed_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                    </p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Subtotal</p>
                                    <p class="metal text-lg font-semibold">{{ $order->subtotal_formatted }}</p>
                                </div>
                            </div>

                            <ul class="mt-3 divide-y divide-zinc-800">
                                @foreach ($order->items as $item)
                                    <li class="flex items-center justify-between gap-3 py-2 text-sm">
                                        <span class="min-w-0">
                                            <span class="text-zinc-500">{{ $item->quantity }}×</span>
                                            <span class="text-zinc-300">{{ $item->product_name }}</span>
                                            @if ($item->notes)
                                                <span class="mt-0.5 block text-xs text-zinc-500">{{ $item->notes }}</span>
                                            @endif
                                        </span>
                                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $colorMap[$item->estado->color()] ?? '' }}">
                                            {{ $item->estado->label() }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="pempty">No hay pedidos con este filtro.</div>
        @endforelse
    </section>

    <div class="pfoot piso-in piso-in-2">
        Mostrando <b>{{ $this->orders->count() }}</b> de <b>{{ $this->totalCount }}</b> pedidos
    </div>
</div>
