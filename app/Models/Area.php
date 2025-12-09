<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'activo',
    ];

    public function evaluadores()
    {
        return $this->belongsToMany(Evaluador::class, 'evaluador_area', 'area_id', 'evaluador_id')
            ->withPivot('nivel_id')
            ->withTimestamps();
    }

    /**
     * Mutator to normalize codigo: remove non-alphanumeric chars and store uppercase.
     */
    public function setCodigoAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['codigo'] = null;
            return;
        }

        // Remove any non-alphanumeric characters and uppercase the code
        $normalized = preg_replace('/[^A-Za-z0-9]/', '', (string) $value);
        $this->attributes['codigo'] = strtoupper($normalized);
    }
}
