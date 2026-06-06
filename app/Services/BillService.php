<?php

namespace App\Services;

use App\Enums\BillModality;
use App\Enums\BillStatus;
use App\Enums\OrderItemStatus;
use App\Events\BillRequested;
use App\Models\Bill;
use App\Models\RestaurantSession;
use App\Models\SessionParticipant;
use Illuminate\Support\Facades\DB;

/**
 * Solicitud y cálculo de la cuenta de una sesión. Hay una sola cuenta por
 * sesión (relación 1:1); requestBill es idempotente.
 */
class BillService
{
    public function requestBill(RestaurantSession $session, ?SessionParticipant $participant = null): Bill
    {
        $bill = Bill::firstOrCreate(
            ['restaurant_session_id' => $session->id],
            [
                'modalidad' => BillModality::Unica,
                'estado' => BillStatus::Solicitada,
                'requested_at' => now(),
                'requested_by_participant_id' => $participant?->id,
            ],
        );

        $created = $bill->wasRecentlyCreated;
        $bill->update(['total' => $this->computeTotal($session)]);

        if ($created) {
            BillRequested::dispatch($bill);
        }

        return $bill;
    }

    /** Total de la sesión: suma de líneas no canceladas (sin propina ni IVA). */
    public function computeTotal(RestaurantSession $session): float
    {
        return (float) $session->orderItems()
            ->where('order_items.estado', '!=', OrderItemStatus::Cancelado->value)
            ->sum(DB::raw('unit_price * quantity'));
    }
}
