<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreasSeeder extends Seeder
{
    public function run(): void
    {
        // Usar updateOrCreate para evitar duplicados si se ejecuta múltiples veces
        $areas = [
            'Matematicas',
            'Fisica',
            'Quimica',
            'Biologia',
            'Robotica',
            'Informatica',
            'Ingenieria de Sistemas',
            'Ingenieria Civil',
        ];

        foreach ($areas as $nombre) {
            DB::table('areas')->updateOrInsert(
                ['nombre' => $nombre],
                ['activo' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
