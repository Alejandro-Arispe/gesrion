<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->string('nombre', 10);
            $table->unsignedBigInteger('id_materia');
            $table->unsignedBigInteger('id_docente')->nullable();
            $table->unsignedBigInteger('id_gestion')->nullable();
            
            $table->foreign('id_materia')->references('id_materia')->on('materia')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_docente')->references('id_docente')->on('docente')
                  ->onDelete('set null')->onUpdate('cascade');
            $table->foreign('id_gestion')->references('id_gestion')->on('gestion_academica')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo');
    }
};