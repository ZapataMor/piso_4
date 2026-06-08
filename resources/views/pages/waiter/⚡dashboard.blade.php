<?php

use App\Enums\BillStatus;
use App\Enums\OrderItemStatus;
use App\Enums\PaymentStatus;
use App\Enums\WaiterCallStatus;
use App\Helpers\Money;
use App\Models\Bill;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantSession;
use App\Models\WaiterCall;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\SessionService;
use App\Services\WaiterService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Meseros · Piso 4')] class extends Component
{
    public ?int $closingSessionId = null;

    /** Re-autoriza en cada petición Livewire (mount + updates). */
    public function boot(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->is_active && ($user->isAdmin() || $user->hasRole('mesero')), 403);
    }

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

    /** Cuentas solicitadas o en pago, para confirmar cobros. */
    #[Computed]
    public function bills(): Collection
    {
        return Bill::whereIn('estado', [BillStatus::Solicitada->value, BillStatus::EnPago->value])
            ->with(['session.mesa', 'payments.participant'])
            ->latest('requested_at')
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

    #[Computed]
    public function closingSession(): ?RestaurantSession
    {
        return $this->closingSessionId
            ? RestaurantSession::where('estado', 'activa')->with(['mesa', 'participants'])->find($this->closingSessionId)
            : null;
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

    public function requestCloseSession(int $sessionId): void
    {
        $session = RestaurantSession::where('estado', 'activa')->find($sessionId);

        $this->closingSessionId = $session?->id;
        unset($this->closingSession);
    }

    public function cancelCloseSession(): void
    {
        $this->closingSessionId = null;
        unset($this->closingSession);
    }

    public function closeSelectedSession(SessionService $sessions): void
    {
        $session = $this->closingSessionId ? RestaurantSession::find($this->closingSessionId) : null;

        $this->closingSessionId = null;

        if ($session && $session->isActive()) {
            $sessions->closeSession($session);
            $this->forget();
        }

        unset($this->closingSession);
    }

    public function confirmPayment(int $paymentId, PaymentService $payments): void
    {
        $payment = Payment::find($paymentId);

        if ($payment && $payment->estado !== PaymentStatus::Confirmado) {
            $payments->confirm($payment, auth()->user());
            $this->forget();
        }
    }

    public function refreshBoard(): void
    {
        $this->forget();
    }

    /** Reverb: refresca al abrir/cerrar mesas, o al llegar pedidos, llamados, cuentas o pagos. */
    #[On('echo-private:waiters,.session.changed')]
    #[On('echo-private:waiters,.order.placed')]
    #[On('echo-private:waiters,.order.item.status')]
    #[On('echo-private:waiters,.waiter.called')]
    #[On('echo-private:waiters,.bill.requested')]
    #[On('echo-private:waiters,.payment.confirmed')]
    public function onRealtime(): void
    {
        $this->forget();
    }

    private function forget(): void
    {
        unset($this->calls, $this->readyItems, $this->activeSessions, $this->bills, $this->closingSession);
    }

    public function money(float $amount): string
    {
        return Money::format($amount);
    }
}; ?>

<div class="flex flex-col gap-8">
    <div class="piso-in">
        <p class="header-subtitle mb-2">Personal</p>
        <div class="flex items-center justify-between gap-4">
            <h1 class="header-title">Panel de Meseros</h1>
            <button type="button" wire:click="refreshBoard" class="btn-secondary shrink-0">↻ Actualizar</button>
        </div>
    </div>

    {{-- Llamados pendientes --}}
    @if ($this->calls->isNotEmpty())
        <x-dashboard.section title="🔔 Llamados urgentes" :count="$this->calls->count()" variant="error" icon="bell-alert">
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->calls as $call)
                    <div wire:key="call-{{ $call->id }}" class="flex flex-col gap-3 rounded-lg bg-zinc-800 p-3 border border-red-900/50">
                        <div>
                            <p class="font-semibold text-zinc-100">Mesa {{ $call->mesa->numero }}</p>
                            <p class="text-xs text-zinc-400 mt-0.5">
                                {{ $call->tipo->label() }} · {{ $call->participant?->nombre ?? '—' }} · {{ $call->created_at->format('H:i') }}
                            </p>
                        </div>
                        <button type="button" wire:click="attend({{ $call->id }})" class="w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700 active:scale-[0.98]">
                            Atender ahora
                        </button>
                    </div>
                @endforeach
            </div>
        </x-dashboard.section>
    @endif

    {{-- Cuentas por cobrar --}}
    @if ($this->bills->isNotEmpty())
        <x-dashboard.section title="💵 Cuentas pendientes" :count="$this->bills->count()" variant="warning" icon="currency-dollar">
            <div class="grid gap-3 lg:grid-cols-2">
                @foreach ($this->bills as $bill)
                    <div wire:key="bill-{{ $bill->id }}" class="rounded-lg bg-zinc-800 border border-amber-900/50 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-zinc-100">Mesa {{ $bill->session->mesa->numero }}</p>
                            <span class="text-xs font-semibold text-amber-400">{{ $bill->modalidad->label() }}</span>
                        </div>
                        <div class="text-2xl font-bold text-amber-400">{{ $this->money($bill->total) }}</div>

                        <div class="space-y-2 border-t border-zinc-700 pt-3">
                            @forelse ($bill->payments as $payment)
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <span class="text-zinc-300">
                                        {{ $payment->participant?->nombre ?? 'Único' }} · {{ $payment->metodo->label() }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-amber-400">{{ $this->money($payment->monto) }}</span>
                                        @if ($payment->estado === PaymentStatus::Confirmado)
                                            <span class="rounded-full bg-green-950 px-2 py-0.5 text-green-400 font-bold">✓</span>
                                        @else
                                            <button type="button" wire:click="confirmPayment({{ $payment->id }})" class="rounded-full bg-amber-600 px-2 py-0.5 text-xs font-semibold text-white hover:bg-amber-700">
                                                Confirmar
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-zinc-500 italic">Esperando que el cliente genere los pagos…</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </x-dashboard.section>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Productos listos para entregar --}}
        <x-dashboard.section title="📦 Listos para servir" :count="$this->readyItems->count()" variant="success" icon="check-circle">
            @forelse ($this->readyItems as $item)
                <div wire:key="ready-{{ $item->id }}" class="flex items-center justify-between gap-3 rounded-lg bg-zinc-800 border border-green-900/50 p-3">
                    <div class="min-w-0">
                        <p class="text-xs text-zinc-400">Mesa {{ $item->order->mesa->numero }} · {{ $item->order->participant?->nombre ?? '—' }}</p>
                        <p class="font-medium text-zinc-100">{{ $item->quantity }}× {{ $item->product_name }}</p>
                    </div>
                    <button type="button" wire:click="deliver({{ $item->id }})" class="shrink-0 rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700 active:scale-[0.98]">
                        Entregar
                    </button>
                </div>
            @empty
                <div class="flex items-center justify-center py-12 text-zinc-500">
                    <p class="text-sm font-medium">Todos los productos han sido servidos</p>
                </div>
            @endforelse
        </x-dashboard.section>

        {{-- Mesas activas --}}
        <x-dashboard.section title="🪑 Mesas activas" :count="$this->activeSessions->count()" variant="default" icon="rectangle-group">
            @forelse ($this->activeSessions as $session)
                <div wire:key="sess-{{ $session->id }}" class="rounded-lg bg-zinc-800 border border-zinc-700 p-3 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="font-semibold text-zinc-100">Mesa {{ $session->mesa->numero }}</p>
                            <p class="text-xs text-zinc-400">
                                {{ $session->participants->count() }} pers. · {{ $session->orders_count }} pedidos
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-amber-400">{{ $this->money($session->currentTotal()) }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="requestCloseSession({{ $session->id }})" class="w-full rounded-lg bg-zinc-700 hover:bg-zinc-600 px-3 py-2 text-xs font-semibold text-zinc-200 active:scale-[0.98]">
                        Cerrar mesa
                    </button>
                </div>
            @empty
                <div class="flex items-center justify-center py-12 text-zinc-500">
                    <p class="text-sm font-medium">No hay mesas activas en este momento</p>
                </div>
            @endforelse
        </x-dashboard.section>
    </div>

    @if ($this->closingSession)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 px-4 py-6 backdrop-blur-sm" wire:key="close-session-modal" wire:click="cancelCloseSession">
            <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl shadow-black/40" wire:click.stop>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="header-subtitle mb-2">Confirmar cierre</p>
                        <h3 class="text-xl font-semibold text-zinc-100">Cerrar mesa {{ $this->closingSession->mesa->numero }}</h3>
                    </div>
                    <button type="button" wire:click="cancelCloseSession" class="flex size-9 shrink-0 items-center justify-center rounded-full border border-zinc-700 text-xl leading-none text-zinc-400 transition hover:border-zinc-500 hover:bg-zinc-800 hover:text-zinc-100" aria-label="Cerrar modal">
                        &times;
                    </button>
                </div>

                <div class="mt-5 rounded-xl border border-zinc-800 bg-zinc-950/70 p-4">
                    <p class="text-sm leading-6 text-zinc-300">
                        Esta acción cerrará la sesión activa de la mesa y la marcará como disponible.
                    </p>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-zinc-900 px-3 py-2">
                            <span class="block text-xs text-zinc-500">Personas</span>
                            <span class="font-semibold text-zinc-100">{{ $this->closingSession->participants->count() }}</span>
                        </div>
                        <div class="rounded-lg bg-zinc-900 px-3 py-2">
                            <span class="block text-xs text-zinc-500">Total actual</span>
                            <span class="font-semibold text-amber-400">{{ $this->money($this->closingSession->currentTotal()) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" wire:click="cancelCloseSession" class="btn-secondary">
                        Cancelar
                    </button>
                    <button type="button" wire:click="closeSelectedSession" class="rounded-lg bg-red-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-700 active:scale-[0.98]">
                        Cerrar mesa
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
