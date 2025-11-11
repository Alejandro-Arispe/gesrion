<?php

namespace App\Http\Controllers\ControlSeguimiento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ControlSeguimiento\Asistencia;
use App\Models\ConfiguracionAcademica\Docente;
use App\Models\Planificacion\Horario;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AsistenciaController extends Controller
{
    public function __construct()
    {
        // Proteger rutas según permisos
        $this->middleware('auth');
        $this->middleware('permiso:marcar_asistencia_qr')->only(['store', 'create']);
        $this->middleware('permiso:ver_mis_asistencias')->only(['index', 'show']);
    }

    /**
     * Listar asistencias con filtros
     */
    public function index(Request $request)
    {
        try {
            $query = Asistencia::with(['docente', 'horario.grupo.materia', 'horario.aula']);

            // Filtro por docente
            if ($request->has('id_docente')) {
                $query->where('id_docente', $request->id_docente);
            }

            // Filtro por fecha
            if ($request->has('fecha')) {
                $query->whereDate('fecha', $request->fecha);
            }

            // Filtro por estado
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            // Filtro por rango de fechas
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
            }

            $asistencias = $query->orderBy('fecha', 'desc')
                                ->orderBy('hora_marcado', 'desc')
                                ->paginate(20);

            $docentes = Docente::where('estado', true)->get();

            return view('control-seguimiento.asistencia.index', compact('asistencias', 'docentes'));
        } catch (Exception $e) {
            return back()->with('error', 'Error al obtener asistencias: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de registro
     */
    public function create(Request $request)
    {
        $docentes = Docente::where('estado', true)->get();
        $hoy = Carbon::now()->format('Y-m-d');
        
        // Obtener el día actual en diferentes formatos para mayor compatibilidad
        $diaSemanaES = ucfirst(Carbon::now()->locale('es')->dayName); // "Lunes"
        $diaSemanaEN = Carbon::now()->format('l'); // "Monday" 
        
        $horariosHoy = collect();
        
        // Si hay un docente seleccionado, filtrar por ese docente
        if ($request->has('id_docente') && $request->id_docente) {
            $horariosHoy = Horario::with(['grupo.materia', 'grupo.docente', 'aula'])
                ->whereHas('grupo', function ($query) use ($request) {
                    $query->where('id_docente', $request->id_docente);
                })
                ->where(function ($query) use ($diaSemanaES, $diaSemanaEN) {
                    $query->where('dia_semana', $diaSemanaES)
                        ->orWhere('dia_semana', $diaSemanaEN);
                })
                ->get();
        } else {
            // Si no hay docente seleccionado, mostrar todos los horarios del día
            $horariosHoy = Horario::with(['grupo.materia', 'grupo.docente', 'aula'])
                ->where(function ($query) use ($diaSemanaES, $diaSemanaEN) {
                    $query->where('dia_semana', $diaSemanaES)
                        ->orWhere('dia_semana', $diaSemanaEN);
                })
                ->get();
        }

        return view('control-seguimiento.asistencia.create', compact('docentes', 'hoy', 'horariosHoy'));
    }

    /**
     * Registrar asistencia manual
     * NOTA: Requiere validación de QR antes de crear asistencia
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_docente' => 'required|exists:docente,id_docente',
                'id_horario' => 'required|exists:horario,id_horario',
                'fecha' => 'required|date',
                'hora_marcado' => 'required|date_format:H:i',
                'estado' => 'required|in:Presente,Atrasado,Ausente,Fuera de aula',
                'latitud' => 'nullable|numeric|between:-90,90',
                'longitud' => 'nullable|numeric|between:-180,180',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'qr_aula_validada' => 'required' // QR OBLIGATORIO
            ]);

            // Verificar si ya existe registro para ese horario y fecha
            $existe = Asistencia::where('id_docente', $request->id_docente)
                ->where('id_horario', $request->id_horario)
                ->whereDate('fecha', $request->fecha)
                ->exists();

            if ($existe) {
                return back()->with('error', 'Ya existe un registro de asistencia para este horario.');
            }

            // Obtener horario y validar que el aula escanneada coincide
            $horario = Horario::with('aula')->findOrFail($request->id_horario);
            
            if ($horario->id_aula != $request->qr_aula_validada) {
                return back()->with('error', 
                    'El QR escaneado no corresponde al aula de este horario. ' .
                    'QR Leído: Aula ' . $request->qr_aula_validada . ', ' .
                    'Horario: Aula ' . $horario->id_aula
                )->withInput();
            }

            // Determinar estado automáticamente basado en la hora
            $horaMarcado = Carbon::parse($request->hora_marcado);
            $horaInicio = Carbon::parse($horario->hora_inicio);
            $margenAtraso = 10; // minutos

            $estado = $request->estado;
            if ($estado === 'Presente') {
                if ($horaMarcado->greaterThan($horaInicio->addMinutes($margenAtraso))) {
                    $estado = 'Atrasado';
                }
            }

            // Validar ubicación GPS si se proporciona y si el aula tiene GPS
            $dentroDeRango = true;
            $distancia = null;
            
            if ($request->filled('latitud') && $request->filled('longitud') && $horario->aula && $horario->aula->ubicacion_gps) {
                $dentroDeRango = $this->validarUbicacion(
                    $request->latitud,
                    $request->longitud,
                    $horario->aula->ubicacion_gps,
                    $distancia // Pasar por referencia para obtener la distancia
                );

                // Si el GPS está fuera del rango, cambiar estado a "Fuera de aula"
                if (!$dentroDeRango) {
                    $estado = 'Fuera de aula';
                    // Registrar advertencia
                    \Log::warning(
                        "Asistencia registrada fuera del aula. Docente: {$request->id_docente}, " .
                        "Aula: {$horario->id_aula}, Distancia: {$distancia}m, " .
                        "Ubicación GPS: {$request->latitud},{$request->longitud}"
                    );
                }
            }

            // Procesar foto si se proporciona
            $rutaFoto = null;
            if ($request->hasFile('foto')) {
                $fecha = date('Ymd');
                $nombreFoto = "asistencia_{$request->id_docente}_{$fecha}_" . time() . '.' . $request->foto->extension();
                $rutaFoto = $request->foto->storeAs('asistencias', $nombreFoto, 'public');
            }

            Asistencia::create([
                'id_docente' => $request->id_docente,
                'id_horario' => $request->id_horario,
                'fecha' => $request->fecha,
                'hora_marcado' => $request->hora_marcado,
                'estado' => $estado,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'foto' => $rutaFoto
            ]);

            return redirect()->route('control-seguimiento.asistencia.index')
                ->with('success', 'Asistencia registrada exitosamente' . 
                    ($distancia !== null ? " (Ubicación: {$distancia}m del aula)" : ''));
        } catch (Exception $e) {
            return back()->with('error', 'Error al registrar asistencia: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * API: Registrar asistencia con geolocalización
     */
    public function registrarConGPS(Request $request)
    {
        try {
            $request->validate([
                'id_docente' => 'required|exists:docente,id_docente',
                'id_horario' => 'required|exists:horario,id_horario',
                'latitud' => 'required|numeric|between:-90,90',
                'longitud' => 'required|numeric|between:-180,180'
            ]);

            $fecha = Carbon::now()->format('Y-m-d');
            $horaMarcado = Carbon::now()->format('H:i');

            // Verificar si ya registró
            $existe = Asistencia::where('id_docente', $request->id_docente)
                ->where('id_horario', $request->id_horario)
                ->whereDate('fecha', $fecha)
                ->exists();

            if ($existe) {
                return response()->json([
                    'message' => 'Ya registraste tu asistencia para este horario'
                ], 400);
            }

            // Obtener el horario
            $horario = Horario::with('aula')->findOrFail($request->id_horario);
            
            // Validar ubicación (si el aula tiene GPS)
            $dentroDeRango = true;
            if ($horario->aula && $horario->aula->ubicacion_gps) {
                $dentroDeRango = $this->validarUbicacion(
                    $request->latitud,
                    $request->longitud,
                    $horario->aula->ubicacion_gps
                );
            }

            // Determinar estado
            $horaActual = Carbon::now();
            $horaInicio = Carbon::parse($horario->hora_inicio);
            $margen = 10; // minutos

            $estado = 'Presente';
            if ($horaActual->greaterThan($horaInicio->addMinutes($margen))) {
                $estado = 'Atrasado';
            }
            if (!$dentroDeRango) {
                $estado = 'Fuera de aula';
            }

            $asistencia = Asistencia::create([
                'id_docente' => $request->id_docente,
                'id_horario' => $request->id_horario,
                'fecha' => $fecha,
                'hora_marcado' => $horaMarcado,
                'estado' => $estado,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud
            ]);

            return response()->json([
                'message' => 'Asistencia registrada exitosamente',
                'asistencia' => $asistencia,
                'estado' => $estado
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al registrar asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar ubicación GPS (radio de 50 metros)
     * @param float $lat1 - Latitud actual
     * @param float $lon1 - Longitud actual
     * @param string $gpsAula - Coordenadas del aula (formato: "lat,lon")
     * @param float &$distancia - Variable para retornar la distancia calculada
     * @return bool - True si está dentro del radio, False si está fuera
     */
    private function validarUbicacion($lat1, $lon1, $gpsAula, &$distancia = null)
    {
        // Extraer coordenadas del aula (formato: "lat,lon")
        $coords = explode(',', $gpsAula);
        if (count($coords) !== 2) {
            $distancia = 0;
            return true; // Si no tiene formato correcto, permitir
        }

        $lat2 = (float) trim($coords[0]);
        $lon2 = (float) trim($coords[1]);

        // Fórmula de Haversine para calcular distancia
        $radioTierra = 6371000; // en metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distancia = round($radioTierra * $c, 2); // Retornar distancia en metros

        return $distancia <= 50; // 50 metros de radio
    }

    /**
     * Mostrar estadísticas de asistencia
     */
    public function estadisticas(Request $request)
    {
        $idDocente = $request->id_docente;
        $mes = $request->mes ?? Carbon::now()->format('Y-m');

        $query = Asistencia::with(['docente', 'horario.grupo.materia']);

        if ($idDocente) {
            $query->where('id_docente', $idDocente);
        }

        // Usar fechas límite del mes en lugar de whereYear/whereMonth
        $fechaInicio = Carbon::parse($mes . '-01')->startOfMonth();
        $fechaFin = Carbon::parse($mes)->endOfMonth();
        
        $query->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')]);

        $asistencias = $query->get();

        $estadisticas = [
            'total' => $asistencias->count(),
            'presentes' => $asistencias->where('estado', 'Presente')->count(),
            'atrasados' => $asistencias->where('estado', 'Atrasado')->count(),
            'ausentes' => $asistencias->where('estado', 'Ausente')->count(),
            'fuera_aula' => $asistencias->where('estado', 'Fuera de aula')->count(),
        ];

        $docentes = Docente::where('estado', true)->get();

        return view('control-seguimiento.asistencia.estadisticas', compact('estadisticas', 'docentes', 'mes'));
    }

    /**
     * Eliminar asistencia
     */
    public function destroy($id)
    {
        try {
            $asistencia = Asistencia::findOrFail($id);
            $asistencia->delete();

            return redirect()->route('control-seguimiento.asistencia.index')
                ->with('success', 'Asistencia eliminada exitosamente');
        } catch (Exception $e) {
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Obtener horarios del docente para hoy (AJAX)
     */
    public function obtenerHorariosDocente(Request $request)
    {
        try {
            $request->validate([
                'id_docente' => 'required|exists:docente,id_docente'
            ]);

            $diaSemanaES = ucfirst(Carbon::now()->locale('es')->dayName);
            $diaSemanaEN = Carbon::now()->format('l');

            $horarios = Horario::with(['grupo.materia', 'grupo.docente', 'aula'])
                ->whereHas('grupo', function ($query) use ($request) {
                    $query->where('id_docente', $request->id_docente);
                })
                ->where(function ($query) use ($diaSemanaES, $diaSemanaEN) {
                    $query->where('dia_semana', $diaSemanaES)
                        ->orWhere('dia_semana', $diaSemanaEN);
                })
                ->orderBy('hora_inicio')
                ->get();

            return response()->json([
                'success' => true,
                'horarios' => $horarios,
                'html' => view('control-seguimiento.asistencia.horarios-options', compact('horarios'))->render()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * NUEVA: Obtener materias/grupos del docente (filtro inteligente)
     * Retorna SOLO las materias/grupos asignados a este docente
     */
    public function obtenerMateriasDocente(Request $request)
    {
        try {
            $request->validate([
                'id_docente' => 'required|exists:docente,id_docente'
            ]);

            $grupos = Grupo::with('materia')
                ->where('id_docente', $request->id_docente)
                ->orderBy('nombre')
                ->get()
                ->map(function($grupo) {
                    return [
                        'id_grupo' => $grupo->id_grupo,
                        'id_materia' => $grupo->id_materia,
                        'nombre_materia' => $grupo->materia->nombre,
                        'nombre_grupo' => $grupo->nombre,
                        'codigo_materia' => $grupo->materia->codigo,
                        'label' => "{$grupo->materia->nombre} - Grupo {$grupo->nombre}"
                    ];
                });

            return response()->json([
                'success' => true,
                'materias' => $grupos,
                'total' => $grupos->count()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}