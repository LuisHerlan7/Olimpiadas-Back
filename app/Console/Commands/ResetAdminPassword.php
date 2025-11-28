<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'user:reset-admin-password';
    protected $description = 'Resetea el password del usuario admin a admin123';

    public function handle()
    {
        $admin = Usuario::where('correo', 'admin@ohsansi.bo')->first();
        
        if (!$admin) {
            $this->error('❌ El usuario admin no existe.');
            return 1;
        }

        // Forzar el hash del password
        $admin->password = Hash::make('admin123');
        $admin->save();

        $this->info('✅ Password del admin reseteado a: admin123');
        $this->info('   Correo: admin@ohsansi.bo');
        return 0;
    }
}

