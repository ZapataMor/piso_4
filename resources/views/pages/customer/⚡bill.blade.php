<?php

use App\Concerns\ResolvesParticipant;
use App\Contracts\WhatsAppGateway;
use App\Enums\BillModality;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\WaiterCallType;
use App\Helpers\Money;
use App\Models\Bill;
use App\Models\Mesa;
use App\Models\Payment;
use App\Models\SessionParticipant;
use App\Services\BillService;
use App\Services\PaymentService;
use App\Services\WaiterService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.customer')] #[Title('Cuenta · Piso 4')] class extends Component
{
    use ResolvesParticipant;

    public Mesa $mesa;

    public string $modalidad = 'unica';

    /** @var array<int,int> item_id => participant_id */
    public array $assignments = [];

    /** @var array<int,string> payment_id => nombre */
    public array $payerName = [];

    /** @var array<int,string> payment_id => teléfono */
    public array $payerPhone = [];

    public bool $editing = false;

    public function mount(Mesa $mesa): void
    {
        $this->mesa = $mesa;

        if ($bill = $this->bill) {
            $this->modalidad = $bill->modalidad->value;
            $this->hydratePayerData($bill);
        }

        $this->prefillAssignments();
    }

    #[Computed]
    public function bill(): ?Bill
    {
        return $this->mesa->activeSession?->bill()
            ->with(['payments.participant', 'payments.orderItems'])
            ->first();
    }

    #[Computed]
    public function items(): Collection
    {
        $session = $this->mesa->activeSession;

        return $session
            ? $session->orderItems()->where('order_items.estado', '!=', 'cancelado')->with('order.participant')->get()
            : collect();
    }

    #[Computed]
    public function participants(): Collection
    {
        return $this->mesa->activeSession?->participants()->get() ?? collect();
    }

    #[Computed]
    public function previewShares(): Collection
    {
        return $this->bill
            ? app(PaymentService::class)->shares($this->bill, BillModality::from($this->modalidad), $this->assignments)
            : collect();
    }

    public function step(): string
    {
        if (! $this->bill) {
            return 'request';
        }

        return ($this->editing || $this->bill->payments->isEmpty()) ? 'split' : 'pay';
    }

    public function requestBill(BillService $bills, WaiterService $waiters): void
    {
        $participant = $this->participant();
        $bill = $bills->requestBill($participant->session, $participant);

        if ($bill->wasRecentlyCreated) {
            $waiters->call($participant->session, $participant, WaiterCallType::Cuenta);
        }

        unset($this->bill);
        $this->prefillAssignments();
        Flux::toast(text: 'Cuenta solicitada', variant: 'success');
    }

    public function generate(PaymentService $payments): void
    {
        $this->participant();

        if ($bill = $this->bill) {
            $payments->generate($bill, BillModality::from($this->modalidad), $this->assignments);
            $this->editing = false;
            unset($this->bill);
            $this->hydratePayerData($this->bill);
        }
    }

    public function recalculate(): void
    {
        $this->participant();
        $this->editing = true;
    }

    public function updateMethod(int $paymentId, string $metodo, PaymentService $payments): void
    {
        $this->participant();

        if ($payment = $this->bill?->payments->firstWhere('id', $paymentId)) {
            $payments->setMethod(
                $payment,
                PaymentMethod::from($metodo),
                $this->payerName[$paymentId] ?? null,
                $this->payerPhone[$paymentId] ?? null,
            );
            unset($this->bill);
        }
    }

    public function saveTransfer(int $paymentId, PaymentService $payments): void
    {
        $this->participant();

        if ($payment = $this->bill?->payments->firstWhere('id', $paymentId)) {
            $payments->setMethod(
                $payment,
                PaymentMethod::Transferencia,
                $this->payerName[$paymentId] ?? null,
                $this->payerPhone[$paymentId] ?? null,
            );
            unset($this->bill);
            Flux::toast(text: 'Datos guardados. Abre WhatsApp para enviar el comprobante.', variant: 'success', duration: 2500);
        }
    }

    public function whatsappLink(Payment $payment): string
    {
        return app(WhatsAppGateway::class)->paymentLink($payment);
    }

    public function money(float|string|null $v): string
    {
        return Money::format($v);
    }

    private function prefillAssignments(): void
    {
        foreach ($this->items as $item) {
            $this->assignments[$item->id] ??= $item->order->session_participant_id;
        }
    }

    private function hydratePayerData(?Bill $bill): void
    {
        foreach ($bill?->payments ?? [] as $payment) {
            $this->payerName[$payment->id] ??= $payment->payer_nombre ?? '';
            $this->payerPhone[$payment->id] ??= $payment->payer_telefono ?? '';
        }
    }
}; ?>

@php($modalidades = App\Enums\BillModality::cases())

<div class="flex min-h-svh flex-col"
     x-data
     x-init="window.Echo && window.Echo.channel('mesa.{{ $mesa->qr_token }}').listen('.payment.confirmed', () => $wire.$refresh())">
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/90 px-5 py-4 backdrop-blur">
        <div>
            <p class="header-subtitle">Mesa {{ $mesa->numero }}</p>
            <h1 class="text-lg font-semibold text-zinc-100">Cuenta</h1>
        </div>
        <a href="{{ route('mesa.orders', $mesa) }}" wire:navigate class="btn-secondary text-sm">
            Pedidos
        </a>
    </header>

    <main class="flex-1 space-y-5 px-5 py-6">
        @if ($this->step() === 'request')
            {{-- Paso 1: solicitar cuenta --}}
            <div class="card-base text-center space-y-4">
                <div>
                    <p class="text-muted text-sm">Total estimado de la mesa</p>
                    <p class="mt-2 text-4xl font-bold text-amber-400">{{ $this->money($this->items->sum(fn ($i) => $i->lineTotalRaw())) }}</p>
                </div>
                <button type="button" wire:click="requestBill" class="btn-primary w-full text-lg py-3.5">
                    🔔 Solicitar cuenta
                </button>
            </div>

        @elseif ($this->step() === 'split')
            {{-- Paso 2: elegir modalidad --}}
            <div class="card-base">
                <div class="flex items-center justify-between">
                    <span class="text-muted">Total</span>
                    <span class="text-2xl font-bold text-amber-400">{{ $this->money($this->bill->total) }}</span>
                </div>
            </div>

            <div class="space-y-3">
                <p class="text-sm font-semibold text-zinc-300">¿Cómo desean pagar?</p>
                @foreach ($modalidades as $m)
                    <button type="button" wire:click="$set('modalidad', '{{ $m->value }}')"
                        class="flex w-full flex-col rounded-xl border p-4 text-left transition {{ $modalidad === $m->value ? 'border-amber-400 bg-amber-500/10 ring-1 ring-amber-500/30' : 'border-zinc-800 bg-zinc-900 hover:border-zinc-700' }}">
                        <span class="font-semibold text-zinc-100">{{ $m->label() }}</span>
                        <span class="text-sm text-muted mt-1">{{ $m->description() }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Personalizada: asignar cada producto --}}
            @if ($modalidad === 'personalizada')
                <div class="space-y-2">
                    <p class="text-sm font-semibold text-zinc-300">¿Quién paga cada producto?</p>
                    @foreach ($this->items as $item)
                        <div wire:key="assign-{{ $item->id }}" class="flex items-center justify-between gap-3 card-base">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-100">{{ $item->quantity }}× {{ $item->product_name }}</p>
                                <p class="text-xs text-amber-400 font-semibold mt-0.5">{{ $item->line_total }}</p>
                            </div>
                            <select wire:model.live="assignments.{{ $item->id }}"
                                class="input-base text-xs w-auto">
                                @foreach ($this->participants as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Vista previa de las porciones --}}
            <div class="card-base space-y-2">
                <p class="text-sm font-semibold text-zinc-300">Resumen de pagos</p>
                <div class="space-y-1.5 pt-1">
                    @foreach ($this->previewShares as $share)
                        <div class="flex items-center justify-between py-1.5 border-b border-zinc-800 last:border-0">
                            <span class="text-zinc-300">{{ $share['participant']?->nombre ?? 'Cuenta única' }}</span>
                            <span class="font-semibold text-amber-400">{{ $this->money($share['amount']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="button" wire:click="generate" class="btn-primary w-full text-lg py-3.5">
                ✓ Generar pagos
            </button>

        @else
            {{-- Paso 3: pagar --}}
            <div class="card-base">
                <div class="flex items-center justify-between">
                    <span class="text-muted text-sm">Total · {{ $this->bill->modalidad->label() }}</span>
                    <span class="text-2xl font-bold text-amber-400">{{ $this->money($this->bill->total) }}</span>
                </div>
            </div>

            @foreach ($this->bill->payments as $payment)
                <div wire:key="pay-{{ $payment->id }}" class="card-base space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-zinc-100">{{ $payment->participant?->nombre ?? 'Cuenta única' }}</p>
                            <p class="text-lg font-bold text-amber-400 mt-1">{{ $this->money($payment->monto) }}</p>
                        </div>
                        @if ($payment->estado === PaymentStatus::Confirmado)
                            <span class="rounded-full bg-green-500/20 px-3 py-1 text-xs font-semibold text-green-300">✓ Confirmado</span>
                        @else
                            <span class="rounded-full bg-amber-500/20 px-3 py-1 text-xs font-semibold text-amber-300">Pendiente</span>
                        @endif
                    </div>

                    @if ($payment->estado !== PaymentStatus::Confirmado)
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (PaymentMethod::cases() as $method)
                                <button type="button" wire:click="updateMethod({{ $payment->id }}, '{{ $method->value }}')"
                                    class="rounded-lg border px-2 py-2 text-xs font-semibold transition {{ $payment->metodo === $method ? 'border-amber-400 bg-amber-500/10 text-amber-300' : 'border-zinc-700 text-zinc-300 hover:border-zinc-600' }}">
                                    {{ $method->label() }}
                                </button>
                            @endforeach
                        </div>

                        @if ($payment->metodo === PaymentMethod::Transferencia)
                            <div class="space-y-2 rounded-lg border border-zinc-700 bg-zinc-950 p-3">
                                <input wire:model="payerName.{{ $payment->id }}" placeholder="Nombre de quien transfiere"
                                    class="input-base w-full text-sm" />
                                <input wire:model="payerPhone.{{ $payment->id }}" placeholder="Número telefónico" inputmode="tel"
                                    class="input-base w-full text-sm" />
                                <button type="button" wire:click="saveTransfer({{ $payment->id }})" class="btn-secondary w-full text-sm">
                                    Guardar datos
                                </button>
                                @if ($payment->payer_nombre)
                                    <a href="{{ $this->whatsappLink($payment) }}" target="_blank" rel="noopener"
                                        class="block w-full rounded-lg bg-green-600 hover:bg-green-700 px-3 py-2.5 text-center text-sm font-semibold text-white active:scale-[0.98] transition">
                                        💬 Enviar por WhatsApp
                                    </a>
                                    <p class="text-center text-xs text-muted-sm">Comparte el comprobante; el personal confirmará el pago</p>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach

            <button type="button" wire:click="recalculate" class="btn-secondary w-full">
                ← Cambiar forma de pago
            </button>
        @endif
    </main>
</div>
