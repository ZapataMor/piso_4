<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuarios demo del personal (uno por rol). Contraseña: "password".
 */
class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            [RoleType::Admin, 'Administrador Piso 4', 'admin@piso4.test'],
            [RoleType::Mesero, 'Mesero Demo', 'mesero@piso4.test'],
            [RoleType::Cocina, 'Cocina Demo', 'cocina@piso4.test'],
            [RoleType::Bar, 'Bar Demo', 'bar@piso4.test'],
        ];

        foreach ($staff as [$roleType, $name, $email]) {
            $role = Role::where('slug', $roleType->value)->first();

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'password',           // se hashea por el cast 'hashed'
                    'role_id' => $role?->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
