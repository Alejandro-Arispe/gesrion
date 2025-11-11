<?php

namespace App\Http\Controllers\ControlSeguimiento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\Docente;
use App\Models\ConfiguracionAcademica\Grupo;
use App\Models\ConfiguracionAcademica\GestionAcademica;
use App\Models\Planificacion\Horario;
use App\Models\ControlSeguimiento\Asistencia;
use Carbon\Carbon;
use Exception;

class ConsultaHorarioController extends Controller
{
    /**
     * Dashboard principal de consultas
     */
    public function dashboard(Request $request)
    {
        try {
            $docentes = Docente::where('estado', true)->get();
            $grupos = Grupo::with('materia', 'gestion')->get();
            $gestiones = GestionAcademica::orderBy('anio', 'desc')->get();
            
            // Obtener horarios filtrados si se proporcionan criterios
            $horarios = collect();
            $asistenciasPorDia = collect();
            
            if ($request->has('id_docente')) {
                $horarios = Horario::with(['grupo.materia', 'grupo.docente', 'aula'])
                    ->whereHas('grupo', function ($query) use ($request) {
                        $query->where('id_docente', $request->id_docente);
                    })
                    ->orderBy('dia_semana')
                    ->orderBy('hora_inicio')
                    ->get();
                    
                // Obtener asistencia del docente en el período
                $asistencias = Asistencia::where('id_docente', $request->id_docente)
                    ->with(['horario', 'docente'])
                    ->orderBy('fecha', 'desc')
                    ->get();
                    
                // Agrupar por día de asistencia
                $asistenciasPorDia = $asistencias->groupBy(function ($item) {
                    return Carbon::parse($item->fecha)->format('Y-m-d');
                });
            }
            
            if ($request->has('id_grupo')) {
                $horarios = Horario::with(['grupo.materia', 'grupo.docente', 'aula'])
                    ->where('id_grupo', $request->id_grupo)
                    ->orderBy('dia_semana')
                    ->orderBy('hora_inicio')
                    ->get();
            }
            
            // Estadísticas del docente seleccionado (últimos 30 días)
            $estadisticas = $this->calcularEstadisticas($request->id_docente);
            
            return view('control-seguimiento.consultas.dashboard', compact(
                'docentes', 'grupos', 'gestiones', 'horarios', 
                'asistenciasPorDia', 'estadisticas'
            ));
        } catch (Exception $e) {
            return back()->with('error', 'Error al cargar dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Horarios del docente (API/AJAX)
     */
    public function horariosDocente(Request $request)
    {
        try {
            $request->validate([
                'id_docente' => 'required|exists:docente,id_docente'
            ]);

            $horarios = Horario::with(['grupo.materia', 'grupo.docente', 'aula'])
                ->whereHas('grupo', function ($query) use ($request) {
                    $query->where('id_docente', $request->id_docente);
                })
                ->orderBy('dia_semana')
                ->orderBy('hora_inicio')
                ->get();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'horarios' => $horarios
                ]);
            }

            return view('control-seguimiento.consultas.horarios-list', compact('horarios'));
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Horarios del grupo (API/AJAX)
     */
    public function horariosGrupo(Request $request)
    {
        try {
            $request->validate([
                'id_grupo' => 'required|exists:grupo,id_grupo'
            ]);

            $horarios = Horario::with(['grupo.materia', 'grupo.docente', 'aula'])
                ->where('id_grupo', $request->id_grupo)
                ->orderBy('dia_semana')
                ->orderBy('hora_inicio')
                ->get();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'horarios' => $horarios
                ]);
            }

            return view('control-seguimiento.consultas.horarios-list', compact('horarios'));
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Asistencia del docente en rango de fechas
     */
    public function asistenciaDocente(Request $request)
    {
        try {
            $request->validate([
                'id_docente' => 'required|exists:docente,id_docente',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio'
            ]);

            $query = Asistencia::with(['horario.grupo.materia', 'docente', 'horario.aula'])
                ->where('id_docente', $request->id_docente);

            if ($request->has('fecha_inicio') && $request->fecha_inicio) {
                $query->whereDate('fecha', '>=', $request->fecha_inicio);
            }

            if ($request->has('fecha_fin') && $request->fecha_fin) {
                $query->whereDate('fecha', '<=', $request->fecha_fin);
            }

            $asistencias = $query->orderBy('fecha', 'desc')
                ->orderBy('hora_marcado', 'desc')
                ->paginate(20);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'asistencias' => $asistencias
                ]);
            }

            return view('control-seguimiento.consultas.asistencia-list', compact('asistencias'));
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Horarios de hoy para un docente
     */
    public function misHorariosHoy(Request $request)
    {
        try {
            $request->validate([
                'id_docente' => 'required|exists:docente,id_docente'
            ]);

            $hoy = Carbon::now();
            $diaSemana = $hoy->locale('es')->dayName;

            $horarios = Horario::with(['grupo.materia', 'grupo.docente', 'aula'])
                ->whereHas('grupo', function ($query) use ($request) {
                    $query->where('id_docente', $request->id_docente);
                })
                ->where('dia_semana', ucfirst($diaSemana))
                ->orderBy('hora_inicio')
                ->get();

            // Enriquecer con información de asistencia
            $horarios = $horarios->map(function ($horario) use ($hoy, $request) {
                $asistencia = Asistencia::where('id_docente', $request->id_docente)
                    ->where('id_horario', $horario->id_horario)
                    ->whereDate('fecha', $hoy->toDateString())
                    ->first();

                $horario->asistencia = $asistencia;
                $horario->registrado = $asistencia ? true : false;
                return $horario;
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'horarios' => $horarios,
                    'fecha' => $hoy->format('Y-m-d'),
                    'dia' => $diaSemana
                ]);
            }

            return view('control-seguimiento.consultas.horarios-hoy', compact('horarios', 'hoy', 'diaSemana'));
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Calendario de asistencia
     */
    public function calendarioAsistencia(Request $request)
    {
        try {
            $request->validate([
                'id_docente' => 'required|exists:docente,id_docente',
                'mes' => 'nullable|date_format:Y-m'
            ]);

            $mes = $request->mes ? Carbon::parse($request->mes) : Carbon::now();
            
            $asistencias = Asistencia::where('id_docente', $request->id_docente)
                ->whereYear('fecha', $mes->year)
                ->whereMonth('fecha', $mes->month)
                ->get();

            // Agrupar por fecha
            $calendarioData = $asistencias->groupBy(function ($item) {
                return $item->fecha->format('Y-m-d');
            })->map(function ($grupo) {
                return [
                    'total' => $grupo->count(),
                    'presentes' => $grupo->where('estado', 'Presente')->count(),
                    'atrasados' => $grupo->where('estado', 'Atrasado')->count(),
                    'ausentes' => $grupo->where('estado', 'Ausente')->count(),
                    'fuera_aula' => $grupo->where('estado', 'Fuera de aula')->count()
                ];
            });

            return view('control-seguimiento.consultas.calendario', compact('calendarioData', 'mes', 'asistencias'));
        } catch (Exception $e) {
            return back()->with('error', 'Error al generar calendario: ' . $e->getMessage());
        }
    }

    /**
     * Calcular estadísticas de asistencia (últimos 30 días)
     */
    private function calcularEstadisticas($idDocente = null)
    {
        if (!$idDocente) {
            return null;
        }

        $hace30Dias = Carbon::now()->subDays(30);

        $asistencias = Asistencia::where('id_docente', $idDocente)
            ->where('fecha', '>=', $hace30Dias)
            ->get();

        $estadisticas = [
            'total' => $asistencias->count(),
            'presentes' => $asistencias->where('estado', 'Presente')->count(),
            'atrasados' => $asistencias->where('estado', 'Atrasado')->count(),
            'ausentes' => $asistencias->where('estado', 'Ausente')->count(),
            'fuera_aula' => $asistencias->where('estado', 'Fuera de aula')->count(),
            'porcentaje_puntualidad' => $asistencias->count() > 0 
                ? round((($asistencias->where('estado', 'Presente')->count() + 
                         $asistencias->where('estado', 'Atrasado')->count()) / 
                        $asistencias->count()) * 100, 2)
                : 0
        ];

        return $estadisticas;
    }

    /**
     * Resumen semanal
     */
    public function resumenSemanal(Request $request)
    {
        try {
            $request->validate([
                'id_docente' => 'required|exists:docente,id_docente'
            ]);

            $inicioSemana = Carbon::now()->startOfWeek();
            $finSemana = Carbon::now()->endOfWeek();

            $asistencias = Asistencia::with(['horario.grupo.materia'])
                ->where('id_docente', $request->id_docente)
                ->whereBetween('fecha', [$inicioSemana, $finSemana])
                ->get();

            $resumenPorDia = [];
            for ($i = 0; $i < 7; $i++) {
                $dia = $inicioSemana->copy()->addDays($i);
                $asistenciasDelDia = $asistencias->filter(function ($a) use ($dia) {
                    return $a->fecha->toDateString() == $dia->toDateString();
                });

                $resumenPorDia[$dia->format('l')] = [
                    'fecha' => $dia->format('Y-m-d'),
                    'total' => $asistenciasDelDia->count(),
                    'presentes' => $asistenciasDelDia->where('estado', 'Presente')->count(),
                    'atrasados' => $asistenciasDelDia->where('estado', 'Atrasado')->count(),
                    'ausentes' => $asistenciasDelDia->where('estado', 'Ausente')->count()
                ];
            }

            return view('control-seguimiento.consultas.resumen-semanal', compact('resumenPorDia', 'inicioSemana', 'finSemana'));
        } catch (Exception $e) {
            return back()->with('error', 'Error al generar resumen: ' . $e->getMessage());
        }
    }
}
