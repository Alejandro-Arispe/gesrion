<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aula', function (Blueprint $table) {
            $table->id('id_aula');
            $table->string('nro', 10);
            $table->string('piso', 10)->nullable();
            $table->integer('capacidad')->nullable();
            $table->string('ubicacion_gps', 100)->nullable();
            $table->unsignedBigInteger('id_facultad')->nullable();
            
            $table->foreign('id_facultad')->references('id_facultad')->on('facultad')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aula');
    }
};