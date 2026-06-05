<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Catálogo de permisos: slug => [name, group].
     */
    private array $permissions = [
        // Operación
        'orders.view' => ['Ver pedidos', 'Operación'],
        'kitchen.operate' => ['Operar cocina', 'Operación'],
        'bar.operate' => ['Operar bar', 'Operación'],
        'waiter.operate' => ['Operar meseros', 'Operación'],
        // Mesas y sesiones
        'mesas.view' => ['Ver mesas', 'Mesas'],
        'mesas.manage' => ['Gestionar mesas', 'Mesas'],
        'sessions.manage' => ['Gestionar sesiones de mesa', 'Mesas'],
        // Menú
        'menu.view' => ['Ver menú', 'Menú'],
        'menu.manage' => ['Gestionar menú (productos y categorías)', 'Menú'],
        // Pagos
        'payments.view' => ['Ver pagos', 'Pagos'],
        'payments.manage' => ['Confirmar y gestionar pagos', 'Pagos'],
        // Administración
        'users.manage' => ['Gestionar usuarios', 'Administración'],
        'roles.manage' => ['Gestionar roles y permisos', 'Administración'],
        'settings.manage' => ['Gestionar configuración', 'Administración'],
        'stats.view' => ['Ver estadísticas', 'Administración'],
    ];

    /**
     * Permisos por rol (el admin recibe todos).
     */
    private array $rolePermissions = [
        'mesero' => [
            'orders.view', 'waiter.operate', 'mesas.view',
            'sessions.manage', 'payments.view', 'payments.manage',
        ],
        'cocina' => ['orders.view', 'kitchen.operate'],
        'bar' => ['orders.view', 'bar.operate'],
    ];

    public function run(): void
    {
        // 1) Permisos
        foreach ($this->permissions as $slug => [$name, $group]) {
            Permission::updateOrCreate(['slug' => $slug], ['name' => $name, 'group' => $group]);
        }

        // 2) Roles
        $roles = [
            RoleType::Admin->value => 'Administrador',
            RoleType::Mesero->value => 'Mesero',
            RoleType::Cocina->value => 'Cocina',
            RoleType::Bar->value => 'Bar',
        ];

        foreach ($roles as $slug => $name) {
            $role = Role::updateOrCreate(['slug' => $slug], ['name' => $name]);

            $slugs = $slug === RoleType::Admin->value
                ? array_keys($this->permissions)            // admin: todos
                : ($this->rolePermissions[$slug] ?? []);

            $role->permissions()->sync(Permission::whereIn('slug', $slugs)->pluck('id'));
        }
    }
}
