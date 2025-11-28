<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class VerifyAdminUser extends Command
{
    protected $signature = 'user:verify-admin';
    protected $description = 'Verifica que el usuario admin existe y el password es correcto';

    public function handle()
    {
        $admin = Usuario::where('correo', 'admin@ohsansi.bo')->first();
        
        if (!$admin) {
            $this->error('❌ El usuario admin NO existe.');
            return 1;
        }

        $this->info('✅ Usuario encontrado:');
        $this->info('   ID: ' . $admin->id);
        $this->info('   Correo: ' . $admin->correo);
        $this->info('   Nombres: ' . $admin->nombres . ' ' . $admin->apellidos);
        $this->info('   Estado: ' . ($admin->estado ? 'ACTIVO' : 'INACTIVO'));
        
        // Verificar password
        $passwordCorrecto = Hash::check('admin123', $admin->password);
        $this->info('   Password correcto: ' . ($passwordCorrecto ? '✅ SÍ' : '❌ NO'));
        
        // Verificar roles
        $roles = $admin->roles()->pluck('slug')->toArray();
        $this->info('   Roles: ' . (empty($roles) ? 'NINGUNO ❌' : implode(', ', $roles)));
        
        if (!$passwordCorrecto) {
            $this->warn('⚠️ El password no coincide. Reseteando...');
            $admin->password = Hash::make('admin123');
            $admin->save();
            $this->info('✅ Password reseteado.');
        }
        
        if (empty($roles)) {
            $this->warn('⚠️ El usuario no tiene roles. Asignando rol administrador...');
            $adminRol = \App\Models\Rol::where('slug', 'administrador')->first();
            if ($adminRol) {
                $admin->roles()->syncWithoutDetaching([$adminRol->id]);
                $this->info('✅ Rol administrador asignado.');
            } else {
                $this->error('❌ El rol administrador no existe.');
            }
        }
        
        return 0;
    }
}

