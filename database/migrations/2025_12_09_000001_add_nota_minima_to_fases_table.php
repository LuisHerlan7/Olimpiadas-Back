<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fases', function (Blueprint $table) {
            if (!Schema::hasColumn('fases', 'nota_minima_suficiente')) {
                $table->decimal('nota_minima_suficiente', 5, 2)->default(70)->nullable()->after('mensaje');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fases', function (Blueprint $table) {
            if (Schema::hasColumn('fases', 'nota_minima_suficiente')) {
                $table->dropColumn('nota_minima_suficiente');
            }
        });
    }
};
