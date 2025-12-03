<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacoras';

    protected $fillable = [
        'actor_email',
        'actor_tipo',
        'mensaje',
        'created_at',
    ];

    public $timestamps = false; // solo usamos created_at manualmente

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Registrar un evento en la bitácora
     */
    public static function registrar(string $actorEmail, string $actorTipo, string $mensaje): void
    {
        try {
            // Sanitizar y validar datos antes de guardar
            $email = trim($actorEmail);
            $tipo = strtoupper(trim($actorTipo));
            $msg = trim($mensaje);
            
            // Validar que los datos no estén vacíos
            if (empty($email) || empty($tipo) || empty($msg)) {
                \Log::warning("Intento de registrar bitácora con datos vacíos", [
                    'email' => $email,
                    'tipo' => $tipo,
                    'mensaje' => $msg,
                ]);
                return;
            }
            
            // Limitar longitud del mensaje para prevenir problemas
            $msg = mb_substr($msg, 0, 500);
            
            self::create([
                'actor_email' => $email,
                'actor_tipo' => $tipo,
                'mensaje' => $msg,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // No bloquear la operación principal si falla el registro de bitácora
            \Log::warning("Error al registrar bitácora: " . $e->getMessage(), [
                'email' => $actorEmail ?? 'N/A',
                'tipo' => $actorTipo ?? 'N/A',
            ]);
        }
    }
}

