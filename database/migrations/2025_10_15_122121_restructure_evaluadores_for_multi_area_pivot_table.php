<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones (UP).
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // 1. Eliminar las claves foráneas y columnas directas de la tabla evaluadores (solo si existen)
        if ($driver === 'sqlite') {
            // SQLite: verificar si las columnas existen antes de intentar eliminarlas
            $columns = DB::select("PRAGMA table_info(evaluadores)");
            $columnNames = array_column($columns, 'name');
            
            if (in_array('area_id', $columnNames) || in_array('nivel_id', $columnNames)) {
                // SQLite no soporta DROP COLUMN directamente, necesitamos recrear la tabla
                // Pero como la tabla ya se creó sin estas columnas en la migración anterior,
                // simplemente las omitimos
            }
        } else {
            // PostgreSQL/MySQL: intentar eliminar las columnas si existen
            Schema::table('evaluadores', function (Blueprint $table) use ($driver) {
                try {
                    $table->dropConstrainedForeignId('area_id');
                } catch (\Exception $e) {
                    // La columna no existe, continuar
                }
                try {
                    $table->dropConstrainedForeignId('nivel_id');
                } catch (\Exception $e) {
                    // La columna no existe, continuar
                }
            });
        }

        // 2. Crear la tabla pivote para la relación muchos-a-muchos
        // Esta tabla manejará la asociación de un Evaluador con una o más Áreas
        if (!Schema::hasTable('evaluador_area')) {
            Schema::create('evaluador_area', function (Blueprint $table) {
                // Claves foráneas compuestas que forman la clave primaria
                $table->foreignId('evaluador_id')->constrained('evaluadores')->onDelete('cascade');
                $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
                
                // Columna extra (pivot data) para el nivel, **DEFINIDA COMO NULLABLE**
                // Esto resuelve el error 500 si se intenta guardar sin un nivel.
                $table->foreignId('nivel_id')->nullable()->constrained('niveles')->onDelete('set null');

                // Establecer la clave primaria compuesta (evaluador_id, area_id)
                $table->primary(['evaluador_id', 'area_id']);
                
                $table->timestamps();
            });
        }
    }

    /**
     * Revierte las migraciones (DOWN).
     */
    public function down(): void
    {
        // 1. Eliminar la tabla pivote
        Schema::dropIfExists('evaluador_area');

        // 2. Revertir la tabla evaluadores (Opcional, solo si necesitas reversibilidad completa)
        // En una aplicación real, probablemente dejarías la tabla evaluadores como está en DOWN.
        Schema::table('evaluadores', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->constrained('areas');
            $table->foreignId('nivel_id')->nullable()->constrained('niveles');
        });
    }
};
