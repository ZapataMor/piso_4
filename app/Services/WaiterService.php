<?php

namespace App\Services;

use App\Enums\WaiterCallStatus;
use App\Enums\WaiterCallType;
use App\Models\RestaurantSession;
use App\Models\SessionParticipant;
use App\Models\User;
use App\Models\WaiterCall;

/**
 * Llamados al mesero ("Llamar Mesero" / "Solicitar Cuenta") y su atención.
 */
class WaiterService
{
    public function call(
        RestaurantSession $session,
        ?SessionParticipant $participant = null,
        WaiterCallType $tipo = WaiterCallType::Llamado,
        ?string $note = null,
    ): WaiterCall {
        return $session->waiterCalls()->create([
            'session_participant_id' => $participant?->id,
            'mesa_id' => $session->mesa_id,
            'tipo' => $tipo,
            'estado' => WaiterCallStatus::Pendiente,
            'note' => $note,
        ]);
    }

    public function attend(WaiterCall $call, User $by): void
    {
        $call->update([
            'estado' => WaiterCallStatus::Atendido,
            'attended_by' => $by->id,
            'attended_at' => now(),
        ]);
    }
}
