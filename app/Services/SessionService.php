<?php

namespace App\Services;

use App\Enums\SessionStatus;
use App\Enums\TableStatus;
use App\Models\Mesa;
use App\Models\RestaurantSession;
use App\Models\SessionParticipant;
use Illuminate\Support\Facades\DB;

/**
 * Ciclo de vida de las sesiones de mesa y sus participantes.
 *
 * Regla central: una mesa solo puede tener UNA sesión activa. Se
 * garantiza abriendo/leyendo la sesión dentro de una transacción con
 * bloqueo de fila (lockForUpdate), evitando que dos clientes que
 * escanean a la vez creen dos sesiones.
 */
class SessionService
{
    public function openOrGetActiveSession(Mesa $mesa): RestaurantSession
    {
        return DB::transaction(function () use ($mesa) {
            // Bloquea la mesa para serializar escaneos concurrentes.
            $mesa = Mesa::whereKey($mesa->getKey())->lockForUpdate()->firstOrFail();

            $active = RestaurantSession::query()
                ->where('mesa_id', $mesa->id)
                ->where('estado', SessionStatus::Activa->value)
                ->lockForUpdate()
                ->first();

            if ($active) {
                return $active;
            }

            $session = RestaurantSession::create([
                'mesa_id' => $mesa->id,
                'codigo' => 'TMP-'.bin2hex(random_bytes(8)),
                'estado' => SessionStatus::Activa,
                'fecha_inicio' => now(),
            ]);

            // Código definitivo basado en el id: único y sin condición de carrera.
            $session->update(['codigo' => sprintf('S-%s-%04d', now()->format('Ymd'), $session->id)]);

            if ($mesa->estado !== TableStatus::FueraDeServicio) {
                $mesa->update(['estado' => TableStatus::Ocupada]);
            }

            return $session;
        });
    }

    public function addParticipant(RestaurantSession $session, string $nombre): SessionParticipant
    {
        return $session->participants()->create([
            'nombre' => $nombre,
            'token' => $this->generateParticipantToken(),
            'is_host' => $session->participants()->count() === 0,
            'last_seen_at' => now(),
        ]);
    }

    public function closeSession(RestaurantSession $session): void
    {
        DB::transaction(function () use ($session) {
            $session->update([
                'estado' => SessionStatus::Cerrada,
                'fecha_fin' => now(),
            ]);

            $session->mesa->update(['estado' => TableStatus::Disponible]);
        });
    }

    private function generateParticipantToken(): string
    {
        do {
            $token = bin2hex(random_bytes(20));
        } while (SessionParticipant::where('token', $token)->exists());

        return $token;
    }
}
