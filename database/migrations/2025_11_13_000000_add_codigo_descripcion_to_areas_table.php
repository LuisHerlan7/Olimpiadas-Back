<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('areas', function (Blueprint $table) {
            if (!Schema::hasColumn('areas', 'codigo')) {
                $table->string('codigo')->nullable()->unique()->after('nombre');
            }
            if (!Schema::hasColumn('areas', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('codigo');
            }
        });
    }

    public function down(): void {
        Schema::table('areas', function (Blueprint $table) {
            if (Schema::hasColumn('areas', 'codigo')) {
                $table->dropColumn('codigo');
            }
            if (Schema::hasColumn('areas', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
        });
    }
};
