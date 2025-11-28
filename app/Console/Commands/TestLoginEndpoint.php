<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestLoginEndpoint extends Command
{
    protected $signature = 'test:login-endpoint';
    protected $description = 'Prueba el endpoint de login directamente';

    public function handle()
    {
        $this->info("=== TEST DEL ENDPOINT DE LOGIN ===");
        $this->newLine();
        
        // Obtener la URL base del backend
        $url = env('APP_URL', 'http://localhost');
        if (str_contains($url, 'localhost')) {
            $url = 'https://olimpiadas-back-production-6956.up.railway.app';
        }
        
        $endpoint = $url . '/api/auth/login';
        
        $this->info("Endpoint: {$endpoint}");
        $this->newLine();
        
        $data = [
            'correo' => 'admin@ohsansi.bo',
            'password' => 'admin123',
            'device' => 'web'
        ];
        
        $this->info("Datos enviados:");
        $this->line(json_encode($data, JSON_PRETTY_PRINT));
        $this->newLine();
        
        try {
            $response = Http::post($endpoint, $data);
            
            $this->info("Status: " . $response->status());
            $this->info("Response:");
            $this->line($response->body());
            
            if ($response->successful()) {
                $this->info("✅✅✅ LOGIN EXITOSO ✅✅✅");
            } else {
                $this->error("❌ LOGIN FALLÓ");
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
        
        return 0;
    }
}

