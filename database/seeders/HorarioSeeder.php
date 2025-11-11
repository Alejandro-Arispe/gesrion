<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Planificacion\Grupo;
use App\Models\Administration\Aula;

class HorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener grupos y aulas
        $grupos = Grupo::all();
        $aulas = Aula::all();

        if ($grupos->isEmpty() || $aulas->isEmpty()) {
            $this->command->warn('No hay grupos o aulas disponibles para crear horarios.');
            return;
        }

        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $horas = [
            ['hora_inicio' => '08:00', 'hora_fin' => '09:30'],
            ['hora_inicio' => '09:45', 'hora_fin' => '11:15'],
            ['hora_inicio' => '11:30', 'hora_fin' => '13:00'],
            ['hora_inicio' => '14:00', 'hora_fin' => '15:30'],
            ['hora_inicio' => '15:45', 'hora_fin' => '17:15'],
        ];

        $indiceGrupo = 0;
        $indiceAula = 0;

        // Crear horarios para cada grupo
        foreach ($grupos as $grupo) {
            foreach ($diasSemana as $dia) {
                // Asignar aula de forma rotativa
                $aula = $aulas[$indiceAula % $aulas->count()];

                // Asignar horas de forma alternada
                $hora = $horas[$indiceGrupo % count($horas)];

                DB::table('horario')->insertOrIgnore([
                    'id_grupo' => $grupo->id_grupo,
                    'id_aula' => $aula->id_aula,
                    'dia_semana' => $dia,
                    'hora_inicio' => $hora['hora_inicio'],
                    'hora_fin' => $hora['hora_fin'],
                    'tipo_asignacion' => 'Manual',
                ]);

                $indiceAula++;
            }
            $indiceGrupo++;
        }

        $this->command->info('Horarios creados exitosamente.');
    }
}
