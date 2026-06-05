<?php

use App\Enums\OrderItemStatus;
use App\Enums\WaiterCallStatus;
use App\Helpers\Money;
use App\Models\OrderItem;
use App\Models\RestaurantSession;
use App\Models\WaiterCall;
use App\Services\OrderService;
use App\Services\SessionService;
use App\Services\WaiterService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Meseros · Piso 4')] class extends Component
{
    /** Llamados pendientes de todas las mesas. */
    #[Computed]
    public function calls(): Collection
    {
        return WaiterCall::where('estado', WaiterCallStatus::Pendiente->value)
            ->with(['mesa', 'participant'])
            ->latest()
            ->get();
    }

    /** Productos listos para entregar (cualquier estación). */
    #[Computed]
    public function readyItems(): Collection
    {
        return OrderItem::where('estado', OrderItemStatus::Listo->value)
            ->with(['order.mesa', 'order.participant'])
            ->orderBy('ready_at')
            ->get();
    }

    /** Mesas con sesión activa. */
    #[Computed]
    public function activeSessions(): Collection
    {
        return RestaurantSession::where('estado', 'activa')
            ->with(['mesa', 'participants'])
            ->withCount('orders')
            ->get()
            ->sortBy(fn (RestaurantSession $s) => $s->mesa->numero)
            ->values();
    }

    public function deliver(int $itemId, OrderService $orders): void
    {
        $item = OrderItem::find($itemId);

        if ($item && $item->estado === OrderItemStatus::Listo) {
            $orders->deliverItem($item);
            $this->forget();
        }
    }

    public function attend(int $callId, WaiterService $waiters): void
    {
        if ($call = WaiterCall::find($callId)) {
            $waiters->attend($call, auth()->user());
            $this->forget();
        }
    }

    public function closeSession(int $sessionId, SessionService $sessions): void
    {
        $session = RestaurantSession::find($sessionId);

        if ($session && $session->isActive()) {
            $sessions->closeSession($session);
            $this->forget();
        }
    }

    public function refreshBoard(): void
    {
        $this->forget();
    }

    private function forget(): void
    {
        unset($this->calls, $this->readyItems, $this->activeSessions);
    }

    public function money(float $amount): string
    {
        return Money::format($amount);
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Panel de Meseros</flux:heading>
        <flux:button wire:click="refreshBoard" icon="arrow-path" variant="ghost" size="sm">Actualizar</flux:button>
    </div>

    {{-- Llamados pendientes --}}
    @if ($this->calls->isNotEmpty())
        <div class="rounded-xl border border-red-300 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/40">
            <flux:heading size="lg" class="mb-3">🔔 Llamados ({{ $this->calls->count() }})</flux:heading>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->calls as $call)
                    <div wire:key="call-{{ $call->id }}" class="flex items-center justify-between gap-2 rounded-lg bg-white p-3 dark:bg-zinc-800">
                        <div class="min-w-0">
                            <p class="font-medium">Mesa {{ $call->mesa->numero }}</p>
                            <p class="text-xs text-zinc-500">{{ $call->tipo->label() }} · {{ $call->participant?->nombre ?? '—' }} · {{ $call->created_at->format('H:i') }}</p>
                        </div>
                        <flux:button wire:click="attend({{ $call->id }})" size="sm" variant="primary">Atender</flux:button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Productos listos para entregar --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="border-b border-zinc-200 px-4 py-2.5 font-semibold dark:border-zinc-700">
                Productos listos ({{ $this->readyItems->count() }})
            </div>
            <div class="space-y-2 p-3">
                @forelse ($this->readyItems as $item)
                    <div wire:key="ready-{{ $item->id }}" class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="min-w-0">
                            <p class="text-xs text-zinc-500">Mesa {{ $item->order->mesa->numero }} · {{ $item->order->participant?->nombre ?? '—' }}</p>
                            <p class="font-medium">{{ $item->quantity }}× {{ $item->product_name }}</p>
                        </div>
                        <flux:button wire:click="deliver({{ $item->id }})" size="sm" variant="primary">Entregar</flux:button>
                    </div>
                @empty
                    <p class="px-2 py-8 text-center text-sm text-zinc-400">No hay productos listos.</p>
                @endforelse
            </div>
        </div>

        {{-- Mesas activas --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="border-b border-zinc-200 px-4 py-2.5 font-semibold dark:border-zinc-700">
                Mesas activas ({{ $this->activeSessions->count() }})
            </div>
            <div class="space-y-2 p-3">
                @forelse ($this->activeSessions as $session)
                    <div wire:key="sess-{{ $session->id }}" class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="min-w-0">
                            <p class="font-medium">Mesa {{ $session->mesa->numero }}</p>
                            <p class="text-xs text-zinc-500">
                                {{ $session->participants->count() }} pers. · {{ $session->orders_count }} pedidos · {{ $this->money($session->currentTotal()) }}
                            </p>
                        </div>
                        <flux:button wire:click="closeSession({{ $session->id }})"
                            wire:confirm="¿Cerrar la mesa {{ $session->mesa->numero }}? La sesión finaliza y la mesa queda disponible."
                            size="sm" variant="ghost">
                            Cerrar mesa
                        </flux:button>
                    </div>
                @empty
                    <p class="px-2 py-8 text-center text-sm text-zinc-400">No hay mesas activas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
