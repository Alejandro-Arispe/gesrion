<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('horario', function (Blueprint $table) {
            $table->json('distribucion_dias')->nullable()->after('tipo_asignacion');
            // distribucion_dias contiene: {"dias": ["Lunes", "Miércoles", "Viernes"], "duracion_minutos": 90, "patron": "LMV"}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horario', function (Blueprint $table) {
            $table->dropColumn('distribucion_dias');
        });
    }
};
