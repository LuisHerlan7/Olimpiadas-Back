<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscritos', function (Blueprint $table) {
            if (!Schema::hasColumn('inscritos', 'area_id')) {
                $table->foreignId('area_id')->nullable()->after('unidad')->constrained('areas')->nullOnDelete();
            }
            if (!Schema::hasColumn('inscritos', 'nivel_id')) {
                $table->foreignId('nivel_id')->nullable()->after('area_id')->constrained('niveles')->nullOnDelete();
            }
        });

        // Normaliza textos
        // Mapea por nombre (lower) → id
        // Nota: usa SQL puro para evitar dependencias de modelos en la migración.

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: sintaxis con FROM
            DB::statement("
                UPDATE inscritos i
                SET area_id = a.id
                FROM areas a
                WHERE i.area_id IS NULL
                  AND i.area IS NOT NULL
                  AND LOWER(TRIM(i.area)) = LOWER(TRIM(a.nombre))
            ");

            DB::statement("
                UPDATE inscritos i
                SET nivel_id = n.id
                FROM niveles n
                WHERE i.nivel_id IS NULL
                  AND i.nivel IS NOT NULL
                  AND LOWER(TRIM(i.nivel)) = LOWER(TRIM(n.nombre))
            ");
        } else {
            // SQLite/MySQL: sintaxis con JOIN
            DB::statement("
                UPDATE inscritos
                SET area_id = (
                    SELECT a.id
                    FROM areas a
                    WHERE LOWER(TRIM(inscritos.area)) = LOWER(TRIM(a.nombre))
                    LIMIT 1
                )
                WHERE area_id IS NULL
                  AND area IS NOT NULL
            ");

            DB::statement("
                UPDATE inscritos
                SET nivel_id = (
                    SELECT n.id
                    FROM niveles n
                    WHERE LOWER(TRIM(inscritos.nivel)) = LOWER(TRIM(n.nombre))
                    LIMIT 1
                )
                WHERE nivel_id IS NULL
                  AND nivel IS NOT NULL
            ");
        }

        // (Opcional) Si quieres hacerlos obligatorios una vez mapeados:
        // Schema::table('inscritos', function (Blueprint $table) {
        //     $table->foreignId('area_id')->nullable(false)->change();
        //     $table->foreignId('nivel_id')->nullable(false)->change();
        // });
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: no soporta DROP COLUMN directamente, omitir
            // Si necesitas revertir, tendrías que recrear la tabla
        } else {
            // PostgreSQL/MySQL: eliminar columnas normalmente
            Schema::table('inscritos', function (Blueprint $table) {
                if (Schema::hasColumn('inscritos', 'nivel_id')) {
                    try {
                        $table->dropConstrainedForeignId('nivel_id');
                    } catch (\Exception $e) {
                        // Ignorar si falla
                    }
                }
                if (Schema::hasColumn('inscritos', 'area_id')) {
                    try {
                        $table->dropConstrainedForeignId('area_id');
                    } catch (\Exception $e) {
                        // Ignorar si falla
                    }
                }
            });
        }
    }
};
