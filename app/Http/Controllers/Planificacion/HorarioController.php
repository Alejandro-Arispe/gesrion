<?php

namespace App\Http\Controllers\Planificacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Planificacion\Horario;
use App\Models\ConfiguracionAcademica\Grupo;
use App\Models\ConfiguracionAcademica\Aula;
use Exception;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    /**
     * Listar horarios con filtros
     */
    public function index(Request $request)
    {
        try {
            $query = Horario::with(['grupo.materia', 'grupo.docente', 'aula']);

            // Filtro por día
            if ($request->has('dia_semana')) {
                $query->where('dia_semana', $request->dia_semana);
            }

            // Filtro por aula
            if ($request->has('id_aula')) {
                $query->where('id_aula', $request->id_aula);
            }

            // Filtro por docente (a través del grupo)
            if ($request->has('id_docente')) {
                $query->whereHas('grupo', function($q) use ($request) {
                    $q->where('id_docente', $request->id_docente);
                });
            }

            // Filtro por grupo
            if ($request->has('id_grupo')) {
                $query->where('id_grupo', $request->id_grupo);
            }

            $horarios = $query->orderBy('dia_semana')
                             ->orderBy('hora_inicio')
                             ->get();

            return response()->json([
                'horarios' => $horarios
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener horarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear horario con validación de conflictos
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_grupo' => 'required|exists:grupo,id_grupo',
                'id_aula' => 'required|exists:aula,id_aula',
                'dia_semana' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'tipo_asignacion' => 'nullable|in:Manual,Automática'
            ]);

            // Validar conflictos
            $conflictos = $this->validarConflictosInterno($request->all());

            if (!empty($conflictos)) {
                return response()->json([
                    'message' => 'Existen conflictos de horario',
                    'conflictos' => $conflictos
                ], 400);
            }

            $horario = Horario::create($request->all());

            return response()->json([
                'message' => 'Horario creado exitosamente',
                'horario' => $horario->load(['grupo.materia', 'grupo.docente', 'aula'])
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al crear horario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar horario específico
     */
    public function show($id)
    {
        try {
            $horario = Horario::with(['grupo.materia', 'grupo.docente', 'aula'])->findOrFail($id);

            return response()->json([
                'horario' => $horario
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Horario no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar horario con validación
     */
    public function update(Request $request, $id)
    {
        try {
            $horario = Horario::findOrFail($id);

            $request->validate([
                'id_grupo' => 'required|exists:grupo,id_grupo',
                'id_aula' => 'required|exists:aula,id_aula',
                'dia_semana' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'tipo_asignacion' => 'nullable|in:Manual,Automática'
            ]);

            // Validar conflictos excluyendo el horario actual
            $conflictos = $this->validarConflictosInterno($request->all(), $id);

            if (!empty($conflictos)) {
                return response()->json([
                    'message' => 'Existen conflictos de horario',
                    'conflictos' => $conflictos
                ], 400);
            }

            $horario->update($request->all());

            return response()->json([
                'message' => 'Horario actualizado exitosamente',
                'horario' => $horario->load(['grupo.materia', 'grupo.docente', 'aula'])
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar horario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar horario
     */
    public function destroy($id)
    {
        try {
            $horario = Horario::findOrFail($id);
            $horario->delete();

            return response()->json([
                'message' => 'Horario eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar horario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar conflictos de horario (endpoint público)
     */
    public function validarConflictos(Request $request)
    {
        try {
            $request->validate([
                'id_grupo' => 'required|exists:grupo,id_grupo',
                'id_aula' => 'required|exists:aula,id_aula',
                'dia_semana' => 'required|string',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'id_horario_excluir' => 'nullable|exists:horario,id_horario'
            ]);

            $conflictos = $this->validarConflictosInterno(
                $request->all(), 
                $request->id_horario_excluir
            );

            return response()->json([
                'tiene_conflictos' => !empty($conflictos),
                'conflictos' => $conflictos
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al validar conflictos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método interno para validar conflictos
     */
    private function validarConflictosInterno($data, $idExcluir = null)
    {
        $conflictos = [];
        $grupo = Grupo::with('docente')->findOrFail($data['id_grupo']);

        // 1. CONFLICTO DE AULA (misma aula, mismo día y hora)
        $conflictoAula = Horario::where('id_aula', $data['id_aula'])
            ->where('dia_semana', $data['dia_semana'])
            ->where(function($q) use ($data) {
                $q->whereBetween('hora_inicio', [$data['hora_inicio'], $data['hora_fin']])
                  ->orWhereBetween('hora_fin', [$data['hora_inicio'], $data['hora_fin']])
                  ->orWhere(function($q2) use ($data) {
                      $q2->where('hora_inicio', '<=', $data['hora_inicio'])
                         ->where('hora_fin', '>=', $data['hora_fin']);
                  });
            })
            ->when($idExcluir, function($q) use ($idExcluir) {
                $q->where('id_horario', '!=', $idExcluir);
            })
            ->with(['grupo.materia'])
            ->first();

        if ($conflictoAula) {
            $conflictos[] = [
                'tipo' => 'aula',
                'mensaje' => 'El aula ya está ocupada en este horario',
                'detalle' => [
                    'aula' => $conflictoAula->aula->nro,
                    'materia' => $conflictoAula->grupo->materia->nombre,
                    'grupo' => $conflictoAula->grupo->nombre,
                    'horario' => $conflictoAula->hora_inicio . ' - ' . $conflictoAula->hora_fin
                ]
            ];
        }

        // 2. CONFLICTO DE DOCENTE (mismo docente, mismo día y hora)
        if ($grupo->id_docente) {
            $conflictoDocente = Horario::whereHas('grupo', function($q) use ($grupo) {
                    $q->where('id_docente', $grupo->id_docente);
                })
                ->where('dia_semana', $data['dia_semana'])
                ->where(function($q) use ($data) {
                    $q->whereBetween('hora_inicio', [$data['hora_inicio'], $data['hora_fin']])
                      ->orWhereBetween('hora_fin', [$data['hora_inicio'], $data['hora_fin']])
                      ->orWhere(function($q2) use ($data) {
                          $q2->where('hora_inicio', '<=', $data['hora_inicio'])
                             ->where('hora_fin', '>=', $data['hora_fin']);
                      });
                })
                ->when($idExcluir, function($q) use ($idExcluir) {
                    $q->where('id_horario', '!=', $idExcluir);
                })
                ->with(['grupo.materia', 'aula'])
                ->first();

            if ($conflictoDocente) {
                $conflictos[] = [
                    'tipo' => 'docente',
                    'mensaje' => 'El docente ya tiene clase asignada en este horario',
                    'detalle' => [
                        'docente' => $grupo->docente->nombre,
                        'materia' => $conflictoDocente->grupo->materia->nombre,
                        'aula' => $conflictoDocente->aula->nro,
                        'horario' => $conflictoDocente->hora_inicio . ' - ' . $conflictoDocente->hora_fin
                    ]
                ];
            }
        }

        // 3. CONFLICTO DE GRUPO (mismo grupo, mismo día y hora)
        $conflictoGrupo = Horario::where('id_grupo', $data['id_grupo'])
            ->where('dia_semana', $data['dia_semana'])
            ->where(function($q) use ($data) {
                $q->whereBetween('hora_inicio', [$data['hora_inicio'], $data['hora_fin']])
                  ->orWhereBetween('hora_fin', [$data['hora_inicio'], $data['hora_fin']])
                  ->orWhere(function($q2) use ($data) {
                      $q2->where('hora_inicio', '<=', $data['hora_inicio'])
                         ->where('hora_fin', '>=', $data['hora_fin']);
                  });
            })
            ->when($idExcluir, function($q) use ($idExcluir) {
                $q->where('id_horario', '!=', $idExcluir);
            })
            ->with(['aula'])
            ->first();

        if ($conflictoGrupo) {
            $conflictos[] = [
                'tipo' => 'grupo',
                'mensaje' => 'El grupo ya tiene clase asignada en este horario',
                'detalle' => [
                    'grupo' => $grupo->nombre,
                    'materia' => $grupo->materia->nombre,
                    'aula' => $conflictoGrupo->aula->nro,
                    'horario' => $conflictoGrupo->hora_inicio . ' - ' . $conflictoGrupo->hora_fin
                ]
            ];
        }

        return $conflictos;
    }

    /**
     * Asignación automática de horarios
     */
    public function asignarAutomatico(Request $request)
    {
        try {
            $request->validate([
                'id_gestion' => 'required|exists:gestion_academica,id_gestion'
            ]);

            DB::beginTransaction();

            $grupos = Grupo::where('id_gestion', $request->id_gestion)
                          ->with(['materia', 'docente'])
                          ->get();

            if ($grupos->isEmpty()) {
                return response()->json([
                    'message' => 'No hay grupos para asignar en esta gestión'
                ], 400);
            }

            $aulas = Aula::where('capacidad', '>', 0)->get();
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $bloques = [
                ['07:00', '08:30'],
                ['08:30', '10:00'],
                ['10:00', '11:30'],
                ['11:30', '13:00'],
                ['14:30', '16:00'],
                ['16:00', '17:30'],
                ['17:30', '19:00'],
                ['19:00', '20:30']
            ];

            $asignados = 0;
            $errores = [];

            foreach ($grupos as $grupo) {
                $horasAsignadas = 0;
                $horasNecesarias = ceil(($grupo->materia->carga_horaria ?? 4) / 1.5); // Bloques de 1.5 horas

                foreach ($dias as $dia) {
                    if ($horasAsignadas >= $horasNecesarias) break;

                    foreach ($bloques as $bloque) {
                        if ($horasAsignadas >= $horasNecesarias) break;

                        foreach ($aulas as $aula) {
                            $data = [
                                'id_grupo' => $grupo->id_grupo,
                                'id_aula' => $aula->id_aula,
                                'dia_semana' => $dia,
                                'hora_inicio' => $bloque[0],
                                'hora_fin' => $bloque[1]
                            ];

                            $conflictos = $this->validarConflictosInterno($data);

                            if (empty($conflictos)) {
                                Horario::create(array_merge($data, ['tipo_asignacion' => 'Automática']));
                                $asignados++;
                                $horasAsignadas++;
                                break; // Encontró aula libre, pasar al siguiente bloque
                            }
                        }
                    }
                }

                if ($horasAsignadas < $horasNecesarias) {
                    $errores[] = [
                        'grupo' => $grupo->nombre,
                        'materia' => $grupo->materia->nombre,
                        'horas_asignadas' => $horasAsignadas,
                        'horas_necesarias' => $horasNecesarias
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Asignación automática completada',
                'horarios_creados' => $asignados,
                'grupos_procesados' => $grupos->count(),
                'advertencias' => $errores
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error en la asignación automática',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}