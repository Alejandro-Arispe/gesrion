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
        Schema::table('aula', function (Blueprint $table) {
            // Agregar campos solo si no existen
            if (!Schema::hasColumn('aula', 'tipo_aula')) {
                $table->enum('tipo_aula', ['Laboratorio', 'Normal'])->default('Normal')->comment('Tipo de aula');
            }
            if (!Schema::hasColumn('aula', 'piso')) {
                $table->enum('piso', ['Primer Piso', 'Segundo Piso', 'Tercer Piso', 'Otro'])->default('Primer Piso')->comment('Piso donde está ubicada');
            }
            if (!Schema::hasColumn('aula', 'ubicacion_gps')) {
                $table->string('ubicacion_gps')->nullable()->comment('Coordenadas GPS: lat,lon');
            }
            if (!Schema::hasColumn('aula', 'capacidad')) {
                $table->integer('capacidad')->default(30)->comment('Capacidad de estudiantes');
            }
            if (!Schema::hasColumn('aula', 'disponible')) {
                $table->boolean('disponible')->default(true)->comment('Si el aula está disponible');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aula', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_aula',
                'piso',
                'ubicacion_gps',
                'capacidad',
                'disponible'
            ]);
        });
    }
};
