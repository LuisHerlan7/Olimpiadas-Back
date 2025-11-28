<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class TestLoginComplete extends Command
{
    protected $signature = 'test:login-complete';
    protected $description = 'Prueba el proceso completo de login';

    public function handle()
    {
        $correoInput = 'admin@ohsansi.bo';
        $passwordInput = 'admin123';
        
        $this->info("=== TEST DE LOGIN COMPLETO ===");
        $this->newLine();
        
        // Normalizar como lo hace AuthController
        $correoNormalizado = strtolower(trim($correoInput));
        $passwordNormalizado = trim($passwordInput);
        
        $this->info("Input original:");
        $this->line("  Correo: '{$correoInput}'");
        $this->line("  Password: '{$passwordInput}'");
        $this->newLine();
        
        $this->info("Después de normalizar:");
        $this->line("  Correo: '{$correoNormalizado}'");
        $this->line("  Password: '{$passwordNormalizado}'");
        $this->newLine();
        
        // Buscar usuario
        $user = Usuario::where('correo', $correoNormalizado)->first();
        
        if (!$user) {
            $this->error("❌ Usuario NO encontrado con correo: '{$correoNormalizado}'");
            $this->info("Usuarios en la base de datos:");
            Usuario::all(['correo'])->each(function($u) {
                $this->line("  - '{$u->correo}' (length: " . strlen($u->correo) . ")");
            });
            return 1;
        }
        
        $this->info("✅ Usuario encontrado:");
        $this->line("  ID: {$user->id}");
        $this->line("  Correo en DB: '{$user->correo}' (length: " . strlen($user->correo) . ")");
        $this->line("  Match exacto: " . ($user->correo === $correoNormalizado ? '✅ SÍ' : '❌ NO'));
        $this->newLine();
        
        // Verificar password
        $passwordCheck = Hash::check($passwordNormalizado, $user->password);
        $this->info("Verificación de password:");
        $this->line("  Hash::check('{$passwordNormalizado}', password_hash): " . ($passwordCheck ? '✅ SÍ' : '❌ NO'));
        $this->newLine();
        
        if ($passwordCheck) {
            $this->info("✅✅✅ LOGIN DEBERÍA FUNCIONAR ✅✅✅");
            $this->info("Si sigue fallando, el problema está en otro lugar.");
        } else {
            $this->error("❌ El password no coincide");
            $this->info("Hash almacenado: " . substr($user->password, 0, 30) . "...");
        }
        
        return 0;
    }
}

