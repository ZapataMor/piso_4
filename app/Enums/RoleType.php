<?php

namespace App\Enums;

/**
 * Roles del personal. El valor coincide con roles.slug.
 */
enum RoleType: string
{
    case Admin = 'admin';
    case Mesero = 'mesero';
    case Cocina = 'cocina';
    case Bar = 'bar';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Mesero => 'Mesero',
            self::Cocina => 'Cocina',
            self::Bar => 'Bar',
        };
    }

    /**
     * Ruta de inicio (dashboard) a la que se redirige cada rol tras login.
     * Las rutas se definen en las fases 9-11 y 15.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Mesero => 'waiter.dashboard',
            self::Cocina => 'kitchen.board',
            self::Bar => 'bar.board',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
