<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;

class ListUsers extends Command
{
    protected $signature = 'user:list';
    protected $description = 'Lista todos los usuarios';

    public function handle()
    {
        $users = Usuario::all(['id', 'correo', 'nombres', 'apellidos']);
        
        $this->info("Usuarios en la base de datos:");
        $this->newLine();
        
        foreach ($users as $user) {
            $this->line("  - {$user->correo} ({$user->nombres} {$user->apellidos})");
            $this->line("    ID: {$user->id}");
            $this->line("    Correo length: " . strlen($user->correo));
            $this->newLine();
        }
        
        return 0;
    }
}

