<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create a case-insensitive unique index for SQLite (COLLATE NOCASE)
        // This will prevent 'MAT' and 'mat' from coexisting.
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS areas_codigo_nocase_unique ON areas (codigo COLLATE NOCASE);");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS areas_codigo_nocase_unique;");
    }
};
