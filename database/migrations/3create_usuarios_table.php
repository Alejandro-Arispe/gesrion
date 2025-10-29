<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->string('correo', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('id_rol')->nullable();
            
            $table->foreign('id_rol')->references('id_rol')->on('rol')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};