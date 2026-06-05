<?php

namespace App\Http\Middleware;

use App\Models\Mesa;
use App\Models\SessionParticipant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege las rutas del cliente (menú, carrito, pedidos). Resuelve al
 * participante desde la cookie cifrada 'participant_token' y verifica
 * que pertenezca a la sesión activa de la mesa escaneada. Si no, lo
 * reenvía a la pantalla de ingreso de nombre.
 */
class EnsureParticipant
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Mesa|null $mesa */
        $mesa = $request->route('mesa');
        $session = $mesa?->activeSession;
        $token = $request->cookie('participant_token');

        $participant = ($session && $token)
            ? SessionParticipant::where('token', $token)
                ->where('restaurant_session_id', $session->id)
                ->first()
            : null;

        if (! $participant) {
            return redirect()->route('mesa.show', $mesa);
        }

        $participant->forceFill(['last_seen_at' => now()])->saveQuietly();
        $request->attributes->set('participant', $participant);

        return $next($request);
    }
}
