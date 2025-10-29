<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia', function (Blueprint $table) {
            $table->id('id_materia');
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->integer('carga_horaria')->nullable();
            $table->unsignedBigInteger('id_facultad')->nullable();
            
            $table->foreign('id_facultad')->references('id_facultad')->on('facultad')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materia');
    }
};