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
            self::create([
                'actor_email' => $actorEmail,
                'actor_tipo' => $actorTipo,
                'mensaje' => $mensaje,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // No bloquear la operación principal si falla el registro de bitácora
            \Log::warning("Error al registrar bitácora: " . $e->getMessage());
        }
    }
}

