<?php

namespace Database\Seeders;

use App\Models\Mesa;
use Illuminate\Database\Seeder;

/**
 * Mesas demo (1 a 8) con token QR único.
 */
class MesaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 8) as $numero) {
            Mesa::firstOrCreate(
                ['numero' => $numero],
                [
                    'qr_token' => bin2hex(random_bytes(20)),
                    'estado' => 'disponible',
                    'capacidad' => 4,
                ],
            );
        }
    }
}
