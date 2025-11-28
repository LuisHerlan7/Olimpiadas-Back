<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class TestLogin extends Command
{
    protected $signature = 'test:login';
    protected $description = 'Prueba el proceso de login del usuario admin';

    public function handle()
    {
        $correo = 'admin@ohsansi.bo';
        $password = 'admin123';
        
        $this->info("Probando login con:");
        $this->info("  Correo: {$correo}");
        $this->info("  Password: {$password}");
        $this->newLine();
        
        // Normalizar correo como lo hace el AuthController
        $correoNormalizado = strtolower(trim($correo));
        $this->info("Correo normalizado: {$correoNormalizado}");
        
        // Buscar usuario
        $user = Usuario::where('correo', $correoNormalizado)->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado con correo: {$correoNormalizado}");
            $this->info("Usuarios existentes:");
            Usuario::all(['correo'])->each(function($u) {
                $this->line("  - {$u->correo}");
            });
            return 1;
        }
        
        $this->info("✅ Usuario encontrado: {$user->correo}");
        
        // Verificar password
        $passwordCorrecto = Hash::check($password, $user->password);
        $this->info("Password correcto: " . ($passwordCorrecto ? "✅ SÍ" : "❌ NO"));
        
        if ($passwordCorrecto) {
            $this->info("✅ Login debería funcionar correctamente");
        } else {
            $this->error("❌ El password no coincide");
            $this->info("Hash almacenado: " . substr($user->password, 0, 30) . "...");
        }
        
        return 0;
    }
}

