<?php

namespace App\Services;

use App\Enums\BillModality;
use App\Enums\BillStatus;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\PaymentConfirmed;
use App\Models\Bill;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Generación y confirmación de pagos según las tres modalidades:
 *  - única:          un pago por el total.
 *  - automática:     un pago por participante (lo que cada quien pidió).
 *  - personalizada:  se asigna cada producto a un participante (pivote).
 */
class PaymentService
{
    /**
     * Calcula las porciones de una cuenta SIN persistir (para previsualizar).
     *
     * @param  array<int,int>  $assignments  [order_item_id => participant_id] (solo personalizada)
     * @return Collection<int, array{participant: ?SessionParticipant, amount: float, order_item_ids: array<int,int>}>
     */
    public function shares(Bill $bill, BillModality $modalidad, array $assignments = []): Collection
    {
        $items = $bill->session->orderItems()
            ->where('order_items.estado', '!=', OrderItemStatus::Cancelado->value)
            ->with('order')
            ->get();

        return match ($modalidad) {
            BillModality::Unica => collect([[
                'participant' => $bill->requestedBy,
                'amount' => (float) $bill->total,
                'order_item_ids' => [],
            ]]),

            BillModality::Automatica => $items
                ->groupBy(fn (OrderItem $i) => $i->order->session_participant_id)
                ->map(fn (Collection $group, $pid) => [
                    'participant' => SessionParticipant::find($pid),
                    'amount' => (float) $group->sum(fn (OrderItem $i) => $i->lineTotalRaw()),
                    'order_item_ids' => $group->pluck('id')->all(),
                ])->values(),

            BillModality::Personalizada => $items
                ->groupBy(fn (OrderItem $i) => $assignments[$i->id] ?? $i->order->session_participant_id)
                ->map(fn (Collection $group, $pid) => [
                    'participant' => SessionParticipant::find($pid),
                    'amount' => (float) $group->sum(fn (OrderItem $i) => $i->lineTotalRaw()),
                    'order_item_ids' => $group->pluck('id')->all(),
                ])->values(),
        };
    }

    /**
     * Genera los pagos pendientes (reemplaza los previos NO confirmados, de
     * modo que se pueda recalcular). Vincula líneas en la personalizada.
     *
     * @param  array<int,int>  $assignments
     * @return Collection<int, Payment>
     */
    public function generate(Bill $bill, BillModality $modalidad, array $assignments = []): Collection
    {
        return DB::transaction(function () use ($bill, $modalidad, $assignments) {
            $bill->payments()
                ->where('estado', '!=', PaymentStatus::Confirmado->value)
                ->get()
                ->each(fn (Payment $p) => $p->delete());

            $bill->update(['modalidad' => $modalidad, 'estado' => BillStatus::EnPago]);

            $payments = collect();

            foreach ($this->shares($bill, $modalidad, $assignments) as $share) {
                if ($share['amount'] <= 0) {
                    continue;
                }

                $payment = $bill->payments()->create([
                    'session_participant_id' => $share['participant']?->id,
                    'metodo' => PaymentMethod::Efectivo,
                    'estado' => PaymentStatus::Pendiente,
                    'monto' => $share['amount'],
                ]);

                if ($modalidad === BillModality::Personalizada && $share['order_item_ids']) {
                    $payment->orderItems()->sync($share['order_item_ids']);
                }

                $payments->push($payment);
            }

            return $payments;
        });
    }

    public function setMethod(Payment $payment, PaymentMethod $metodo, ?string $payerNombre = null, ?string $payerTelefono = null): void
    {
        $payment->update([
            'metodo' => $metodo,
            'payer_nombre' => $metodo === PaymentMethod::Transferencia ? $payerNombre : null,
            'payer_telefono' => $metodo === PaymentMethod::Transferencia ? $payerTelefono : null,
        ]);
    }

    /** Confirmación manual por el personal. */
    public function confirm(Payment $payment, User $by): void
    {
        $payment->update([
            'estado' => PaymentStatus::Confirmado,
            'confirmed_by' => $by->id,
            'confirmed_at' => now(),
        ]);

        $this->syncBillStatus($payment->bill);

        PaymentConfirmed::dispatch($payment);
    }

    /** Cuando todos los pagos están confirmados, la cuenta queda pagada. */
    public function syncBillStatus(Bill $bill): void
    {
        $bill->load('payments');

        if ($bill->payments->isNotEmpty()
            && $bill->payments->every(fn (Payment $p) => $p->estado === PaymentStatus::Confirmado)) {
            $bill->update(['estado' => BillStatus::Pagada]);

            $bill->session->orders()
                ->where('estado', '!=', OrderStatus::Cancelado->value)
                ->update(['estado' => OrderStatus::Facturado->value]);
        }
    }
}
