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
        // Verificar si la tabla asistencia existe y agregar la columna foto
        if (Schema::hasTable('asistencia')) {
            Schema::table('asistencia', function (Blueprint $table) {
                if (!Schema::hasColumn('asistencia', 'foto')) {
                    $table->string('foto')->nullable()->after('longitud')->comment('Ruta de la foto de asistencia');
                }
            });
        } else {
            // Si la tabla no existe, crearla completa
            Schema::create('asistencia', function (Blueprint $table) {
                $table->id('id_asistencia');
                $table->unsignedBigInteger('id_docente');
                $table->unsignedBigInteger('id_horario');
                $table->date('fecha');
                $table->time('hora_marcado');
                $table->enum('estado', ['Presente', 'Atrasado', 'Ausente', 'Fuera de aula'])->default('Presente');
                $table->decimal('latitud', 10, 6)->nullable();
                $table->decimal('longitud', 10, 6)->nullable();
                $table->string('foto')->nullable();
                $table->timestamps();

                $table->foreign('id_docente')->references('id_docente')->on('docente')->onDelete('cascade');
                $table->foreign('id_horario')->references('id_horario')->on('horario')->onDelete('cascade');
                
                $table->index(['id_docente', 'fecha']);
                $table->unique(['id_docente', 'id_horario', 'fecha']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencia', function (Blueprint $table) {
            if (Schema::hasColumn('asistencia', 'foto')) {
                $table->dropColumn('foto');
            }
        });
    }
};
