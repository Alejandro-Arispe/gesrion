<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id('id_bitacora');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->timestamp('fecha_hora')->useCurrent();
            $table->string('accion', 100);
            $table->text('descripcion')->nullable();
            $table->string('ip_origen', 45)->nullable();
            
            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};