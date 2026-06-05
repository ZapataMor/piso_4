<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe una ruta a un permiso concreto: permission:mesas.manage.
 * El administrador tiene acceso total (bypass).
 */
class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'Cuenta inactiva o sin autenticación.');
        }

        if ($user->isAdmin() || $user->hasPermission($permission)) {
            return $next($request);
        }

        abort(403, 'No cuentas con el permiso requerido.');
    }
}
