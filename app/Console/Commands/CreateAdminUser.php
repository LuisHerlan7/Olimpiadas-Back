<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use App\Models\Rol;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin';
    protected $description = 'Crea el usuario administrador si no existe';

    public function handle()
    {
        $adminRol = Rol::where('slug', 'administrador')->first();
        
        if (!$adminRol) {
            $this->error('❌ El rol administrador no existe. Ejecuta primero los seeders de roles.');
            return 1;
        }

        $admin = Usuario::updateOrCreate(
            ['correo' => 'admin@ohsansi.bo'],
            [
                'nombres'   => 'Administrador',
                'apellidos' => 'OH Sansi',
                'telefono'  => '+591 70000000',
                'ci'        => 'CI-0001',
                'password'  => 'admin123',
                'estado'    => true,
            ]
        );

        $admin->roles()->syncWithoutDetaching([$adminRol->id]);

        $this->info('✅ Usuario admin creado/actualizado: admin@ohsansi.bo / admin123');
        return 0;
    }
}

