<?php

use App\Concerns\AdminOnly;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Pedidos · Piso 4')] class extends Component
{
    use AdminOnly;

    public string $filter = 'all';

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

<div class="flex flex-col gap-6">
    <flux:heading size="xl">Pedidos</flux:heading>

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-2">
        @php($filters = array_merge(['all' => 'Todos'], collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()))
        @foreach ($filters as $value => $label)
            <button type="button" wire:click="setFilter('{{ $value }}')"
                class="rounded-full border px-3 py-1.5 text-sm transition {{ $filter === $value ? 'border-amber-400 bg-amber-500/10 text-amber-600 dark:text-amber-300' : 'border-zinc-300 text-zinc-500 dark:border-zinc-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Pedido</th>
                    <th class="px-4 py-3 font-medium">Mesa</th>
                    <th class="px-4 py-3 font-medium">Cliente</th>
                    <th class="px-4 py-3 font-medium">Ítems</th>
                    <th class="px-4 py-3 font-medium">Estado</th>
                    <th class="px-4 py-3 font-medium">Total</th>
                    <th class="px-4 py-3 font-medium">Hora</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->orders as $order)
                    <tr wire:key="order-{{ $order->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3 font-medium">#{{ $order->numero }}</td>
                        <td class="px-4 py-3">{{ $order->mesa?->numero ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $order->participant?->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $order->items->count() }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $colorMap[$order->estado->color()] ?? '' }}">
                                {{ $order->estado->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $order->subtotal_formatted }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $order->placed_at?->format('d/m H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-zinc-500">No hay pedidos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
