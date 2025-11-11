<?php

namespace App\Http\Controllers\Planificacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Planificacion\Horario;
use App\Models\ConfiguracionAcademica\Grupo;
use App\Models\ConfiguracionAcademica\Aula;
use App\Services\ClassroomAssignmentEngine;
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
     * Asignación automática de horarios (NUEVA - con algoritmo inteligente)
     * Distribuye aulas según:
     * 1. Carga horaria del docente
     * 2. Prioridad: Primer piso > Laboratorios para materias que requieran
     * 3. Sin conflictos de docente ni aula
     */
    public function asignarAutomatico(Request $request)
    {
        try {
            $request->validate([
                'id_gestion' => 'required|exists:gestion_academica,id_gestion'
            ]);

            $engine = new ClassroomAssignmentEngine();
            $resultado = $engine->asignarAulasInteligente($request->id_gestion);

            if (isset($resultado['error'])) {
                return response()->json($resultado, 400);
            }

            return response()->json([
                'message' => 'Asignación automática inteligente completada',
                'resumen' => $resultado
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error en la asignación automática',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información de carga horaria por docente
     */
    public function obtenerCargaHoraria(Request $request)
    {
        try {
            $request->validate([
                'id_gestion' => 'required|exists:gestion_academica,id_gestion'
            ]);

            $engine = new ClassroomAssignmentEngine();
            $cargaHoraria = $engine->obtenerCargaHorariaDocentes($request->id_gestion);

            return response()->json([
                'carga_horaria' => $cargaHoraria
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener carga horaria',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}