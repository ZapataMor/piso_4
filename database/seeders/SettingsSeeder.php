<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Configuración por defecto del restaurante. El administrador la edita en
 * la Fase 15. Los valores bancarios/WhatsApp son de ejemplo.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['restaurant_name', 'Piso Cuatro', 'general'],
            ['whatsapp_number', '573000000000', 'whatsapp'],   // número del restaurante (+57...)
            ['bank_name', 'Bancolombia', 'banco'],
            ['bank_account_type', 'Ahorros', 'banco'],
            ['bank_account', '123-456789-00', 'banco'],
            ['bank_holder', 'Restaurante Piso 4 S.A.S.', 'banco'],
            ['bank_doc', '900.123.456-7', 'banco'],
        ];

        foreach ($defaults as [$key, $value, $group]) {
            // No sobrescribe si el admin ya lo cambió.
            if (! Setting::where('key', $key)->exists()) {
                Setting::set($key, $value, 'string', $group);
            }
        }
    }
}
