<?php
// database/migrations/2025_12_03_120000_create_bitacoras_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bitacoras', function (Blueprint $t) {
            $t->id();
            $t->string('actor_email', 255); // correo del usuario (evaluador@ohsansi.bo, admin@ohsansi.bo, etc.)
            $t->string('actor_tipo', 50);    // 'ADMIN', 'EVALUADOR', 'RESPONSABLE'
            $t->text('mensaje');             // "se conectó", "subió notas de Juan Pérez", "cerró sesión"
            $t->timestamp('created_at')->useCurrent();
            $t->index('created_at');
            $t->index('actor_email');
        });
    }
    public function down(): void { Schema::dropIfExists('bitacoras'); }
};

