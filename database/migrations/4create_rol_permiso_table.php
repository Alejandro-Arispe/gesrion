<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_permiso');
            
            $table->foreign('id_rol')->references('id_rol')->on('rol')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_permiso')->references('id_permiso')->on('permiso')
                  ->onDelete('cascade')->onUpdate('cascade');
            
            $table->primary(['id_rol', 'id_permiso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_permiso');
    }
};