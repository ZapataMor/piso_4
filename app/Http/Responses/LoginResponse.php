<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

/**
 * Tras el login, cada usuario aterriza en el dashboard de SU rol
 * (RoleType::homeRoute()). Si no tiene rol, va al dashboard genérico.
 */
class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false]);
        }

        return redirect()->intended($this->homeUrl($request));
    }

    protected function homeUrl(Request $request): string
    {
        $role = $request->user()?->role?->slug; // RoleType|null

        return $role ? route($role->homeRoute()) : route('dashboard');
    }
}
