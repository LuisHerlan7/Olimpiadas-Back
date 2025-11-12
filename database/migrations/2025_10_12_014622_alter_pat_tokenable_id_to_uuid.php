<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: usar ALTER COLUMN
            DB::statement('DROP INDEX IF EXISTS personal_access_tokens_tokenable_type_tokenable_id_index');
            DB::statement('ALTER TABLE personal_access_tokens
                ALTER COLUMN tokenable_id TYPE uuid USING (tokenable_id::text::uuid)');
            DB::statement('CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index
                ON personal_access_tokens (tokenable_type, tokenable_id)');
        } elseif ($driver === 'sqlite') {
            // SQLite: la tabla ya se crea con UUID desde la migración anterior
            // Solo verificamos que el índice exista, si no, lo creamos
            $indexExists = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name='personal_access_tokens_tokenable_type_tokenable_id_index'");
            if (empty($indexExists)) {
                DB::statement('CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_type_tokenable_id_index
                    ON personal_access_tokens (tokenable_type, tokenable_id)');
            }
        }
        // Para otros drivers (mysql, etc.), no hacemos nada ya que la tabla ya tiene UUID
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS personal_access_tokens_tokenable_type_tokenable_id_index');
            DB::statement('ALTER TABLE personal_access_tokens
                ALTER COLUMN tokenable_id TYPE bigint USING NULL');
            DB::statement('CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index
                ON personal_access_tokens (tokenable_type, tokenable_id)');
        }
        // Para SQLite y otros, no revertimos ya que la tabla ya tiene UUID desde el inicio
    }
};
