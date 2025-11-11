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
        Schema::create('qr_aula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_aula')
                  ->constrained('aula', 'id_aula')
                  ->onDelete('cascade');
            $table->text('codigo_qr'); // Contenido del QR (JSON con id_aula + token)
            $table->string('token')->unique(); // Token único para validar integridad
            $table->timestamp('generado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            
            // Índices
            $table->index('id_aula');
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_aula');
    }
};
