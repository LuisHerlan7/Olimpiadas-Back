<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fase extends Model
{
    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'activa',
        'cancelada',
        'mensaje',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'activa' => 'boolean',
        'cancelada' => 'boolean',
    ];

    /**
     * Verificar si la fase está activa según las fechas
     */
    public function estaActiva(): bool
    {
        if ($this->cancelada) {
            return false;
        }

        if (!$this->activa) {
            return false;
        }

        $now = now();
        
        if ($this->fecha_inicio && $now->lt($this->fecha_inicio)) {
            return false;
        }

        if ($this->fecha_fin && $now->gt($this->fecha_fin)) {
            return false;
        }

        return true;
    }
}

