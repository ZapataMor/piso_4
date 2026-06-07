<?php

use App\Concerns\AdminOnly;
use App\Helpers\Money;
use App\Services\StatsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Panel de Administración · Piso 4')] class extends Component
{
    use AdminOnly;

    /** Reverb: refresca las métricas al abrir/cerrar mesas o moverse los pedidos. */
    #[On('echo-private:waiters,.session.changed')]
    #[On('echo-private:waiters,.order.placed')]
    #[On('echo-private:waiters,.order.item.status')]
    public function onRealtime(): void
    {
        unset($this->snap);
    }

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

<div class="admin-dashboard">
    {{-- Encabezado --}}
    <div class="piso-in">
        <p class="kicker">Sistema</p>
        <div class="head-row flex items-end justify-between gap-6 mt-2.5">
            <h1 class="header-title">Panel de Control</h1>
            <button type="button" wire:click="$refresh" class="btn-secondary shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4" stroke-width="2"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
                Actualizar
            </button>
        </div>
    </div>
    <div class="piso-rule my-7"></div>

    {{-- Métricas en vivo --}}
    <div class="metrics piso-in piso-in-1">
        <div class="metric" style="--accent:oklch(0.78 0.09 155)">
            <div class="metric__top">
                <span class="metric__label">Ventas de hoy</span>
                <span class="metric__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2v20"/><path d="M17 5.5c-1-1.2-2.8-2-5-2-2.8 0-5 1.3-5 3.3 0 4.7 10 2.3 10 7 0 2-2.2 3.4-5 3.4-2.4 0-4.4-.9-5.3-2.2"/></svg></span>
            </div>
            <div class="metric__val metal">{{ $this->money($this->snap['sales_today']) }}</div>
            <div class="metric__foot"><span class="metric__dot"></span> Ingresos acumulados del día</div>
        </div>

        <div class="metric" style="--accent:oklch(0.74 0.09 240)">
            <div class="metric__top">
                <span class="metric__label">Mesas activas</span>
                <span class="metric__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="11" rx="2"/><path d="M7 19v-4M17 19v-4M5 15h14"/></svg></span>
            </div>
            <div class="metric__val metal">{{ $this->snap['active_tables'] }}</div>
            <div class="metric__foot"><span class="metric__dot"></span> En servicio ahora mismo</div>
        </div>

        <div class="metric" style="--accent:oklch(0.82 0.055 84)">
            <div class="metric__top">
                <span class="metric__label">Ítems en preparación</span>
                <span class="metric__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.07-2.14-.22-4.05 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.29-2.26.9-3.2"/></svg></span>
            </div>
            <div class="metric__val metal">{{ $this->snap['pending_items'] }}</div>
            <div class="metric__foot"><span class="metric__dot"></span> En cocina y barra</div>
        </div>

        <div class="metric" style="--accent:oklch(0.82 0.055 84)">
            <div class="metric__top">
                <span class="metric__label">Pedidos de hoy</span>
                <span class="metric__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8 4h8a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2V6a2 2 0 0 1 2-2z"/><path d="M9 9h6M9 13h4"/></svg></span>
            </div>
            <div class="metric__val metal">{{ $this->snap['orders_today'] }}</div>
            <div class="metric__foot"><span class="metric__dot"></span> Registrados desde la apertura</div>
        </div>
    </div>

    {{-- Gestión rápida --}}
    <div class="section-h piso-in piso-in-2">
        <h2 class="serif">Gestión Rápida</h2>
        <span class="line"></span>
    </div>
    <div class="quick piso-in piso-in-2">
        @php($links = [
            ['Productos', 'Carta y disponibilidad', 'admin.products', '<path d="M4 11h16v8a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/><path d="M3 8.5a2.5 2.5 0 0 1 2.5-2.5h13A2.5 2.5 0 0 1 21 8.5V11H3z"/><path d="M12 6V4M9 6c0-1.5 3-1.5 3 0M15 6c0-1.5-3-1.5-3 0"/>'],
            ['Categorías', 'Organización del menú', 'admin.categories', '<path d="M3 7.5A1.5 1.5 0 0 1 4.5 6h6.3a2 2 0 0 1 1.4.6l8 8a1.8 1.8 0 0 1 0 2.5l-5.1 5.1a1.8 1.8 0 0 1-2.5 0l-8-8A2 2 0 0 1 4 12.8z"/><circle cx="8" cy="11" r="1.4"/>'],
            ['Mesas y QR', 'Códigos y disposición', 'admin.mesas.index', '<rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M20 17v4M17 20h4"/>'],
            ['Usuarios', 'Equipo y permisos', 'admin.users', '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><path d="M16 5.2a3 3 0 0 1 0 5.6M17.5 20a5.5 5.5 0 0 0-2.8-4.8"/>'],
            ['Pedidos', 'Comandas en curso', 'admin.orders', '<path d="M8 4h8a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2V6a2 2 0 0 1 2-2z"/><path d="M9 9h6M9 13h4"/>'],
            ['Estadísticas', 'Rendimiento del local', 'admin.statistics', '<path d="M4 20V4M4 20h16M8 17v-5M12.5 17V8M17 17v-8"/>'],
            ['Configuración', 'Ajustes del sistema', 'admin.settings', '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 0 1-4 0v-.2A1.6 1.6 0 0 0 7 19.4a1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3 14.9a1.6 1.6 0 0 0-1.5-1H1.4a2 2 0 0 1 0-4h.2A1.6 1.6 0 0 0 3 8.1l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.6 1.6 0 0 0 8.6 5 1.6 1.6 0 0 0 9.6 3.5V3.4a2 2 0 0 1 4 0v.2a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8 1.6 1.6 0 0 0 1.5 1h.1a2 2 0 0 1 0 4h-.2a1.6 1.6 0 0 0-1.5 1z"/>'],
        ])
        @foreach ($links as [$label, $desc, $route, $svg])
            <a href="{{ route($route) }}" wire:navigate>
                <span class="quick__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor">{!! $svg !!}</svg></span>
                <span class="quick__txt"><span class="t">{{ $label }}</span><span class="s">{{ $desc }}</span></span>
                <svg class="quick__arr" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        @endforeach
    </div>
</div>
