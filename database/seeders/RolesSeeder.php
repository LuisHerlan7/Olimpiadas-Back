<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Rol;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Inserta roles base del sistema OH Sansi (usando updateOrCreate para evitar duplicados)
        // No hacemos truncate porque hay foreign keys que lo impiden
        
        $roles = [
            [
                'id'         => 1,
                'nombre'     => 'Administrador',
                'slug'       => Str::slug('administrador'),
                'activo'     => true,
            ],
            [
                'id'         => 2,
                'nombre'     => 'Responsable',
                'slug'       => Str::slug('responsable'),
                'activo'     => true,
            ],
            [
                'id'         => 3,
                'nombre'     => 'Evaluador',
                'slug'       => Str::slug('evaluador'),
                'activo'     => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Rol::updateOrCreate(
                ['id' => $roleData['id']],
                $roleData
            );
        }

        $this->command->info('✅ Roles base insertados correctamente.');
    }
}
