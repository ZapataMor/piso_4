<?php

namespace App\Concerns;

/**
 * Re-autoriza un componente Livewire de administración en CADA petición
 * (mount y updates). Livewire ejecuta automáticamente boot{NombreTrait}(),
 * por lo que esto cubre el hueco de que el middleware de ruta no corre en
 * las actualizaciones de Livewire (/livewire/update).
 */
trait AdminOnly
{
    public function boot(): void
    {
        $user = auth()->user();

        abort_unless($user && $user->is_active && $user->isAdmin(), 403);
    }
}
