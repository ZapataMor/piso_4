<?php

namespace App\Concerns;

use App\Models\SessionParticipant;
use App\Services\WaiterService;
use Flux\Flux;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Resolución segura del participante para los componentes del cliente.
 * El participante se obtiene de la cookie + la sesión activa de la mesa
 * en CADA petición (también en updates de Livewire), por lo que las
 * acciones nunca confían en un id expuesto al cliente. Requiere que el
 * componente tenga una propiedad pública `Mesa $mesa`.
 */
trait ResolvesParticipant
{
    private ?SessionParticipant $resolvedParticipant = null;

    protected function participant(): SessionParticipant
    {
        if ($this->resolvedParticipant !== null) {
            return $this->resolvedParticipant;
        }

        $token = request()->cookie('participant_token');
        $session = $this->mesa->activeSession;

        $participant = ($token && $session)
            ? SessionParticipant::where('token', $token)
                ->where('restaurant_session_id', $session->id)
                ->first()
            : null;

        abort_unless($participant, 403);

        return $this->resolvedParticipant = $participant;
    }

    /** "Llamar Mesero" con límite anti-spam (1 cada 30 s por participante). */
    public function callWaiter(WaiterService $waiters): void
    {
        $participant = $this->participant();
        $key = 'waiter-call:'.$participant->id;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            Flux::toast(text: 'Ya avisaste al mesero. Espera un momento.', variant: 'warning');

            return;
        }

        RateLimiter::hit($key, 30);
        $waiters->call($participant->session, $participant);

        Flux::toast(text: 'Un mesero viene en camino 🔔', variant: 'success', duration: 2000);
    }
}
