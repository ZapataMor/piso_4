<?php

use App\Concerns\AdminOnly;
use App\Helpers\Money;
use App\Services\StatsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Panel de Administración · Piso 4')] class extends Component
{
    use AdminOnly;

    #[Computed]
    public function snap(): array
    {
        return app(StatsService::class)->liveSnapshot();
    }

    public function money(float|int $v): string
    {
        return Money::format($v);
    }
}; ?>

<div class="flex flex-col gap-8">
    <div class="piso-in">
        <p class="header-subtitle mb-2">Sistema</p>
        <div class="flex items-center justify-between gap-4">
            <h1 class="header-title">Panel de Control</h1>
            <button type="button" wire:click="$refresh" class="btn-secondary shrink-0">↻ Actualizar</button>
        </div>
    </div>

    {{-- Métricas en vivo --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.metric-card label="Ventas de hoy" :value="$this->money($this->snap['sales_today'])" icon="currency-dollar" variant="success" />
        <x-dashboard.metric-card label="Mesas activas" :value="$this->snap['active_tables']" icon="rectangle-group" variant="info" />
        <x-dashboard.metric-card label="Ítems en preparación" :value="$this->snap['pending_items']" icon="fire" variant="warning" />
        <x-dashboard.metric-card label="Pedidos de hoy" :value="$this->snap['orders_today']" icon="receipt-percent" variant="default" />
    </div>

    {{-- Accesos rápidos --}}
    <div>
        <h2 class="serif text-2xl font-medium text-[var(--piso-fg)] mb-4">Gestión Rápida</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @php($links = [
                ['Productos', 'admin.products', '🍰'],
                ['Categorías', 'admin.categories', '🏷️'],
                ['Mesas y QR', 'admin.mesas.index', '📱'],
                ['Usuarios', 'admin.users', '👥'],
                ['Pedidos', 'admin.orders', '📋'],
                ['Estadísticas', 'admin.statistics', '📊'],
                ['Configuración', 'admin.settings', '⚙️'],
            ])
            @foreach ($links as [$label, $route, $icon])
                <a href="{{ route($route) }}" wire:navigate
                   class="group flex items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-900 p-4 transition hover:border-amber-500 hover:bg-amber-500/5">
                    <span class="text-2xl">{{ $icon }}</span>
                    <span class="font-medium text-zinc-200 group-hover:text-zinc-100 flex-1">{{ $label }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ml-auto size-4 text-zinc-600 group-hover:text-amber-400 transition">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            @endforeach
        </div>
    </div>
</div>
