<?php

namespace App\Http\Controllers\Customer;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\JoinSessionRequest;
use App\Models\Mesa;
use App\Models\SessionParticipant;
use App\Services\SessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Entrada pública del cliente al escanear el QR (/mesa/{token}).
 * Sin autenticación: solo se pide el nombre. La mesa se resuelve por
 * qr_token (binding implícito {mesa:qr_token}).
 */
class TableEntryController extends Controller
{
    public function __construct(private readonly SessionService $sessions) {}

    public function show(Request $request, Mesa $mesa): View|RedirectResponse|Response
    {
        if ($mesa->estado === TableStatus::FueraDeServicio) {
            return response()->view('customer.unavailable', ['mesa' => $mesa], 503);
        }

        $session = $this->sessions->openOrGetActiveSession($mesa);

        // ¿Este dispositivo ya es participante de la sesión activa?
        if ($this->resolveParticipant($request, $session->id)) {
            return redirect()->route('mesa.menu', $mesa);
        }

        return view('customer.entry', ['mesa' => $mesa, 'session' => $session]);
    }

    public function join(JoinSessionRequest $request, Mesa $mesa): RedirectResponse
    {
        abort_if($mesa->estado === TableStatus::FueraDeServicio, 503);

        $session = $this->sessions->openOrGetActiveSession($mesa);
        $participant = $this->sessions->addParticipant($session, $request->validated()['nombre']);

        return redirect()
            ->route('mesa.menu', $mesa)
            ->withCookie(cookie('participant_token', $participant->token, 60 * 8)); // 8 horas
    }

    private function resolveParticipant(Request $request, int $sessionId): ?SessionParticipant
    {
        $token = $request->cookie('participant_token');

        if (! $token) {
            return null;
        }

        return SessionParticipant::query()
            ->where('token', $token)
            ->where('restaurant_session_id', $sessionId)
            ->first();
    }
}
