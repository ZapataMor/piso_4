<?php

use App\Concerns\AdminOnly;
use App\Helpers\Money;
use App\Services\StatsService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Estadísticas · Piso 4')] class extends Component
{
    use AdminOnly;

    private function stats(): StatsService
    {
        return app(StatsService::class);
    }

    #[Computed]
    public function summary(): array
    {
        return [
            'today' => $this->stats()->salesToday(),
            'week' => $this->stats()->salesThisWeek(),
            'month' => $this->stats()->salesThisMonth(),
            'prep' => $this->stats()->averagePrepMinutes(),
        ];
    }

    #[Computed]
    public function salesByDay(): Collection
    {
        return $this->stats()->salesByDay(7);
    }

    #[Computed]
    public function topProducts(): Collection
    {
        return $this->stats()->topProducts(8);
    }

    #[Computed]
    public function topTables(): Collection
    {
        return $this->stats()->topTables(8);
    }

    #[Computed]
    public function ordersByHour(): Collection
    {
        return $this->stats()->ordersByHour();
    }

    #[Computed]
    public function incomeByMethod(): Collection
    {
        return $this->stats()->incomeByMethod();
    }

    #[On('echo-private:waiters,.order.placed')]
    public function onOrderPlaced(): void
    {
        unset($this->topProducts, $this->topTables);
    }

    public function money(float|int|string|null $v): string
    {
        return Money::format($v);
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Estadísticas</flux:heading>
        <flux:button wire:click="$refresh" icon="arrow-path" variant="ghost" size="sm">Actualizar</flux:button>
    </div>

    {{-- Resumen --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php($summaryCards = [
            ['Ventas hoy', $this->money($this->summary['today'])],
            ['Ventas semana', $this->money($this->summary['week'])],
            ['Ventas mes', $this->money($this->summary['month'])],
            ['Tiempo prom. preparación', $this->summary['prep'] !== null ? $this->summary['prep'].' min' : '—'],
        ])
        @foreach ($summaryCards as [$label, $value])
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text class="text-sm">{{ $label }}</flux:text>
                <p class="mt-2 text-2xl font-semibold">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Ventas por día --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg" class="mb-4">Ventas (últimos 7 días)</flux:heading>
            @php($maxDay = max(1, $this->salesByDay->max('total')))
            <div class="flex h-40 items-end gap-2">
                @foreach ($this->salesByDay as $d)
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <div class="flex w-full items-end justify-center" style="height: 8rem">
                            <div class="w-full rounded-t bg-amber-500" style="height: {{ max(2, ($d['total'] / $maxDay) * 100) }}%"
                                 title="{{ $this->money($d['total']) }}"></div>
                        </div>
                        <span class="text-[10px] text-zinc-500">{{ $d['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Pedidos por hora --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg" class="mb-4">Pedidos por hora</flux:heading>
            @php($maxHour = max(1, $this->ordersByHour->max('count')))
            <div class="flex h-40 items-end gap-0.5">
                @foreach ($this->ordersByHour as $h)
                    <div class="flex flex-1 flex-col items-center">
                        <div class="flex w-full items-end justify-center" style="height: 8rem">
                            <div class="w-full rounded-t bg-blue-500" style="height: {{ max(1, ($h['count'] / $maxHour) * 100) }}%"
                                 title="{{ $h['hour'] }}h: {{ $h['count'] }}"></div>
                        </div>
                        @if ($h['hour'] % 6 === 0)
                            <span class="text-[10px] text-zinc-500">{{ $h['hour'] }}h</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Productos más vendidos --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg" class="mb-4">Productos más vendidos</flux:heading>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->topProducts as $p)
                        <tr>
                            <td class="py-2">{{ $p->product_name }}</td>
                            <td class="py-2 text-right text-zinc-500">{{ $p->qty }} uds</td>
                            <td class="py-2 text-right font-medium">{{ $this->money($p->revenue) }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-6 text-center text-zinc-400">Sin datos aún.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mesas más utilizadas + ingresos por método --}}
        <div class="flex flex-col gap-6">
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="lg" class="mb-4">Mesas más utilizadas</flux:heading>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($this->topTables as $t)
                            <tr>
                                <td class="py-2">Mesa {{ $t->mesa?->numero ?? '—' }}</td>
                                <td class="py-2 text-right text-zinc-500">{{ $t->orders_count }} pedidos</td>
                                <td class="py-2 text-right font-medium">{{ $this->money($t->revenue) }}</td>
                            </tr>
                        @empty
                            <tr><td class="py-6 text-center text-zinc-400">Sin datos aún.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="lg" class="mb-4">Ingresos por método</flux:heading>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($this->incomeByMethod as $m)
                            <tr>
                                <td class="py-2 capitalize">{{ $m->metodo->label() }}</td>
                                <td class="py-2 text-right text-zinc-500">{{ $m->count }} pagos</td>
                                <td class="py-2 text-right font-medium">{{ $this->money($m->total) }}</td>
                            </tr>
                        @empty
                            <tr><td class="py-6 text-center text-zinc-400">Sin pagos confirmados aún.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
