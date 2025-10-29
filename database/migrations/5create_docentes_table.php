<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente', function (Blueprint $table) {
            $table->id('id_docente');
            $table->string('ci', 20)->unique();
            $table->string('nombre', 100);
            $table->char('sexo', 1)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 100)->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('id_facultad')->nullable();
            
            $table->foreign('id_facultad')->references('id_facultad')->on('facultad')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente');
    }
};