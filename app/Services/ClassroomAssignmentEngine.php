<?php

namespace App\Services;

use App\Models\Planificacion\Horario;
use App\Models\ConfiguracionAcademica\Grupo;
use App\Models\ConfiguracionAcademica\Aula;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClassroomAssignmentEngine
{
    /**
     * Asignar aulas a docentes de forma inteligente:
     * 1. Distribuyendo según carga horaria
     * 2. Priorizando primeros pisos y laboratorios
     * 3. Validando ausencia de conflictos
     * 
     * @param int $idGestion - ID de la gestión académica
     * @return array - Resumen de asignaciones
     */
    public function asignarAulasInteligente($idGestion)
    {
        DB::beginTransaction();
        try {
            $resumen = [
                'exitosas' => 0,
                'conflictos' => [],
                'no_asignadas' => [],
                'detalles' => []
            ];

            $grupos = Grupo::where('id_gestion', $idGestion)
                ->with(['materia', 'docente', 'horarios'])
                ->get();

            if ($grupos->isEmpty()) {
                DB::rollBack();
                return ['error' => 'No hay grupos para esta gestión'];
            }

            // Agrupar por docente para procesar carga horaria total
            $gruposPorDocente = $grupos->groupBy('id_docente');

            foreach ($gruposPorDocente as $idDocente, $gruposDocente) {
                // Calcular horas totales necesarias por docente
                $totalHoras = $gruposDocente->sum(function ($grupo) {
                    return ceil(($grupo->materia->carga_horaria ?? 4) / 1.5); // Bloques de 1.5 horas
                });

                // Obtener horarios ya creados para este docente
                $horariosExistentes = Horario::whereHas('grupo', function ($q) use ($idDocente) {
                    $q->where('id_docente', $idDocente);
                })->get();

                foreach ($gruposDocente as $grupo) {
                    // Horas necesarias para este grupo
                    $horasNecesarias = ceil(($grupo->materia->carga_horaria ?? 4) / 1.5);
                    
                    // ¿Requiere laboratorio?
                    $requiereLaboratorio = $this->requiereLaboratorioDato($grupo->materia);

                    $horasAsignadas = 0;
                    $diasDisponibles = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
                    
                    // Intentar distribuir a lo largo de la semana
                    foreach ($diasDisponibles as $dia) {
                        if ($horasAsignadas >= $horasNecesarias) break;

                        // Intentar asignar bloque horario
                        $bloqueAsignado = $this->asignarBloqueOptimo(
                            $grupo,
                            $dia,
                            $requiereLaboratorio,
                            $horariosExistentes
                        );

                        if ($bloqueAsignado) {
                            $horasAsignadas++;
                            $resumen['exitosas']++;
                            
                            $resumen['detalles'][] = [
                                'docente' => $grupo->docente->nombre,
                                'materia' => $grupo->materia->nombre,
                                'grupo' => $grupo->nombre,
                                'aula' => $bloqueAsignado['aula']['nro'],
                                'dia' => $dia,
                                'horario' => "{$bloqueAsignado['hora_inicio']} - {$bloqueAsignado['hora_fin']}"
                            ];
                        }
                    }

                    // Si no se asignaron todas las horas, registrar advertencia
                    if ($horasAsignadas < $horasNecesarias) {
                        $resumen['no_asignadas'][] = [
                            'docente' => $grupo->docente->nombre,
                            'materia' => $grupo->materia->nombre,
                            'grupo' => $grupo->nombre,
                            'horas_asignadas' => $horasAsignadas,
                            'horas_necesarias' => $horasNecesarias,
                            'razon' => 'No hay aulas disponibles con los criterios especificados'
                        ];
                    }
                }
            }

            DB::commit();
            return $resumen;

        } catch (\Exception $e) {
            DB::rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Asignar un bloque horario óptimo para un grupo
     * Prioridades:
     * 1. Laboratorio si es necesario
     * 2. Primer piso
     * 3. Sin conflictos
     * 
     * @return array|false - Datos del horario asignado o false si no hay disponibilidad
     */
    private function asignarBloqueOptimo($grupo, $dia, $requiereLaboratorio, $horariosExistentes)
    {
        $bloques = $this->obtenerBloques();
        $docente = $grupo->docente;

        foreach ($bloques as $bloque) {
            // Obtener aulas disponibles, priorizadas
            $aulasDisponibles = $this->obtenerAulasPriorizadas($requiereLaboratorio);

            foreach ($aulasDisponibles as $aula) {
                // Validar conflictos
                $conflictos = $this->validarConflictosBloque(
                    $grupo->id_grupo,
                    $aula->id_aula,
                    $dia,
                    $bloque['inicio'],
                    $bloque['fin'],
                    $docente->id_docente
                );

                if (empty($conflictos)) {
                    // Crear el horario
                    $horario = Horario::create([
                        'id_grupo' => $grupo->id_grupo,
                        'id_aula' => $aula->id_aula,
                        'dia_semana' => $dia,
                        'hora_inicio' => $bloque['inicio'],
                        'hora_fin' => $bloque['fin'],
                        'tipo_asignacion' => 'Automática'
                    ]);

                    return [
                        'horario_id' => $horario->id_horario,
                        'aula' => $aula,
                        'hora_inicio' => $bloque['inicio'],
                        'hora_fin' => $bloque['fin']
                    ];
                }
            }
        }

        return false;
    }

    /**
     * Obtener aulas priorizadas:
     * 1. Si requiere laboratorio: solo laboratorios
     * 2. Primeros pisos antes que segundo/tercero
     * 3. Disponibles
     * 
     * @return Collection
     */
    private function obtenerAulasPriorizadas($requiereLaboratorio = false)
    {
        $query = Aula::where('disponible', true)
                    ->where('capacidad', '>', 0);

        if ($requiereLaboratorio) {
            $query->where('tipo_aula', 'Laboratorio');
        }

        // Ordenar por prioridad: Primer Piso > Segundo > Tercero/Otro
        $query->orderByRaw("
            CASE 
                WHEN piso = 'Primer Piso' THEN 1
                WHEN piso = 'Segundo Piso' THEN 2
                ELSE 3
            END
        ")
        ->orderBy('nro', 'ASC');

        return $query->get();
    }

    /**
     * Validar conflictos de bloque horario
     * Retorna array vacío si NO hay conflictos (OK para asignar)
     * Retorna array con conflictos si HAY problemas
     * 
     * @return array
     */
    private function validarConflictosBloque($idGrupo, $idAula, $dia, $horaInicio, $horaFin, $idDocente)
    {
        $conflictos = [];

        // 1. CONFLICTO DE AULA (misma aula ocupada)
        $conflictoAula = Horario::where('id_aula', $idAula)
            ->where('dia_semana', $dia)
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->whereBetween('hora_inicio', [$horaInicio, $horaFin])
                  ->orWhereBetween('hora_fin', [$horaInicio, $horaFin])
                  ->orWhere(function ($q2) use ($horaInicio, $horaFin) {
                      $q2->where('hora_inicio', '<=', $horaInicio)
                         ->where('hora_fin', '>=', $horaFin);
                  });
            })
            ->first();

        if ($conflictoAula) {
            $conflictos[] = "Aula $idAula ya está ocupada";
        }

        // 2. CONFLICTO DE DOCENTE (mismo docente en mismo horario)
        $conflictoDocente = Horario::whereHas('grupo', function ($q) use ($idDocente) {
                $q->where('id_docente', $idDocente);
            })
            ->where('dia_semana', $dia)
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->whereBetween('hora_inicio', [$horaInicio, $horaFin])
                  ->orWhereBetween('hora_fin', [$horaInicio, $horaFin])
                  ->orWhere(function ($q2) use ($horaInicio, $horaFin) {
                      $q2->where('hora_inicio', '<=', $horaInicio)
                         ->where('hora_fin', '>=', $horaFin);
                  });
            })
            ->first();

        if ($conflictoDocente) {
            $conflictos[] = "Docente ya tiene clase asignada";
        }

        // 3. CONFLICTO DE GRUPO (grupo ya tiene clase en este horario)
        $conflictoGrupo = Horario::where('id_grupo', $idGrupo)
            ->where('dia_semana', $dia)
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->whereBetween('hora_inicio', [$horaInicio, $horaFin])
                  ->orWhereBetween('hora_fin', [$horaInicio, $horaFin])
                  ->orWhere(function ($q2) use ($horaInicio, $horaFin) {
                      $q2->where('hora_inicio', '<=', $horaInicio)
                         ->where('hora_fin', '>=', $horaFin);
                  });
            })
            ->first();

        if ($conflictoGrupo) {
            $conflictos[] = "Grupo ya tiene clase asignada";
        }

        return $conflictos;
    }

    /**
     * Determinar si una materia requiere laboratorio
     * Consulta el campo requiere_laboratorio de la materia
     * 
     * @return bool
     */
    private function requiereLaboratorio($materia)
    {
        // Si es un objeto Materia, usar el campo directo
        if (is_object($materia)) {
            return $materia->requiere_laboratorio ?? false;
        }

        // Si es string (nombre), buscar en BD
        $mat = \App\Models\ConfiguracionAcademica\Materia::where('nombre', $materia)->first();
        return $mat?->requiere_laboratorio ?? false;
    }

    /**
     * Obtener bloques horarios disponibles
     * 
     * @return array
     */
    private function obtenerBloques()
    {
        return [
            ['inicio' => '07:00', 'fin' => '08:30'],
            ['inicio' => '08:30', 'fin' => '10:00'],
            ['inicio' => '10:00', 'fin' => '11:30'],
            ['inicio' => '11:30', 'fin' => '13:00'],
            ['inicio' => '14:30', 'fin' => '16:00'],
            ['inicio' => '16:00', 'fin' => '17:30'],
            ['inicio' => '17:30', 'fin' => '19:00'],
            ['inicio' => '19:00', 'fin' => '20:30']
        ];
    }

    /**
     * Obtener información detallada de carga horaria por docente
     * Útil para reportes
     * 
     * @return Collection
     */
    public function obtenerCargaHorariaDocentes($idGestion)
    {
        return Grupo::where('id_gestion', $idGestion)
            ->with(['docente', 'materia', 'horarios'])
            ->get()
            ->groupBy('id_docente')
            ->map(function ($grupos) {
                $totalBloques = $grupos->flatMap(function ($g) {
                    return $g->horarios;
                })->count();

                $totalHoras = $totalBloques * 1.5;

                return [
                    'docente' => $grupos->first()->docente->nombre,
                    'grupos_asignados' => $grupos->count(),
                    'bloques_asignados' => $totalBloques,
                    'horas_totales' => $totalHoras,
                    'materias' => $grupos->pluck('materia.nombre')->unique()->values()
                ];
            });
    }
}
