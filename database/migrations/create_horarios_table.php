<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario', function (Blueprint $table) {
            $table->id('id_horario');
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedBigInteger('id_aula')->nullable();
            $table->string('dia_semana', 15);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('tipo_asignacion', 15)->default('Manual');
            
            $table->foreign('id_grupo')->references('id_grupo')->on('grupo')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_aula')->references('id_aula')->on('aula')
                  ->onDelete('set null')->onUpdate('cascade');
            
            $table->unique(['id_grupo', 'id_aula', 'dia_semana', 'hora_inicio', 'hora_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario');
    }
};