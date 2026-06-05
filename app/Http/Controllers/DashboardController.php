<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Punto de entrada genérico /dashboard: reenvía a cada usuario al panel
 * de su rol. Sirve de red de seguridad para enlaces que apunten a
 * 'dashboard' (p. ej. el logo del sidebar).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $role = $request->user()?->role?->slug;

        return $role
            ? redirect()->route($role->homeRoute())
            : view('dashboard');
    }
}
