<?php

use App\Concerns\AdminOnly;
use App\Enums\OrderStatus;
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

    /** Reverb: refresca la lista al llegar pedidos nuevos o cambios de estado. */
    #[On('echo-private:waiters,.order.placed')]
    #[On('echo-private:waiters,.order.item.status')]
    public function onRealtime(): void
    {
        unset($this->orders, $this->totalCount);
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
    public function totalCount(): int
    {
        return Order::count();
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        unset($this->orders);
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

    <div class="ptable piso-in piso-in-2" style="--ptpl: minmax(190px, 1fr) 96px minmax(170px, 1fr) 96px 150px 122px 118px;">
        <div class="ptable__head">
            <div class="pth">Pedido</div>
            <div class="pth">Mesa</div>
            <div class="pth">Cliente</div>
            <div class="pth">Ítems</div>
            <div class="pth">Estado</div>
            <div class="pth pth--r">Total</div>
            <div class="pth pth--r">Hora</div>
        </div>

        @forelse ($this->orders as $order)
            <div wire:key="order-{{ $order->id }}" class="prow">
                <div class="pname">
                    <span class="pmono"><span>#</span></span>
                    <span class="pstack">
                        <span class="pname__t">Pedido #{{ $order->numero }}</span>
                        <span class="pname__sub">{{ $order->placed_at?->format('d/m/Y') ?? 'Sin fecha' }}</span>
                    </span>
                </div>
                <div><span class="pcat">Mesa {{ $order->mesa?->numero ?? '—' }}</span></div>
                <div class="pmuted">{{ $order->participant?->nombre ?? '—' }}</div>
                <div class="pvalue">{{ $order->items->count() }}</div>
                <div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $colorMap[$order->estado->color()] ?? '' }}">
                        {{ $order->estado->label() }}
                    </span>
                </div>
                <div class="pprice metal">{{ $order->subtotal_formatted }}</div>
                <div class="pvalue pvalue--r">{{ $order->placed_at?->format('H:i') ?? '—' }}</div>
            </div>
        @empty
            <div class="pempty">No hay pedidos con este filtro.</div>
        @endforelse
    </div>

    <div class="pfoot piso-in piso-in-2">
        Mostrando <b>{{ $this->orders->count() }}</b> de <b>{{ $this->totalCount }}</b> pedidos
    </div>
</div>
