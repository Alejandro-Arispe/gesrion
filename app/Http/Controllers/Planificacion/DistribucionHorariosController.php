<?php

namespace App\Http\Controllers\Planificacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\Grupo;
use App\Services\DistribucionHorariosService;

class DistribucionHorariosController extends Controller
{
    protected $distribucionService;

    public function __construct(DistribucionHorariosService $distribucionService)
    {
        $this->distribucionService = distribucionService;
    }

    /**
     * Mostrar interfaz para generar distribución de horarios
     */
    public function mostrarFormulario(Request $request)
    {
        $grupos = Grupo::with(['materia', 'docente'])
                      ->where('id_gestion', $request->id_gestion)
                      ->whereDoesntHave('horarios')
                      ->get();

        $patrones = $this->distribucionService->obtenerPatronesDisponibles();

        return view('planificacion.distribucion-horarios', compact('grupos', 'patrones'));
    }

    /**
     * Generar distribución para un grupo
     */
    public function generar(Request $request)
    {
        try {
            $request->validate([
                'id_grupo' => 'required|exists:grupo,id_grupo',
                'patron' => 'nullable|string',
                'hora_inicio' => 'required|date_format:H:i',
                'dias_personalizados' => 'nullable|array',
                'duracion_personalizada' => 'nullable|numeric|min:1'
            ]);

            $grupo = Grupo::findOrFail($request->id_grupo);

            if (!empty($request->dias_personalizados)) {
                $resultado = $this->distribucionService->generarDistribucion(
                    $grupo,
                    'PERSONALIZADO',
                    $request->hora_inicio,
                    $request->dias_personalizados,
                    $request->duracion_personalizada
                );
            } else {
                $patron = $request->patron ?? 'LMV';
                $resultado = $this->distribucionService->generarDistribucion(
                    $grupo,
                    $patron,
                    $request->hora_inicio
                );
            }

            if (!$resultado['exito']) {
                return response()->json($resultado, 422);
            }

            return response()->json($resultado, 201);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener sugerencia de patrón
     */
    public function sugerirPatron(Request $request)
    {
        try {
            $request->validate([
                'carga_horaria' => 'required|numeric|min:1'
            ]);

            $patron = $this->distribucionService->sugerirPatron($request->carga_horaria);

            return response()->json([
                'patron_sugerido' => $patron,
                'patrones_disponibles' => $this->distribucionService->obtenerPatronesDisponibles()
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener patrones disponibles
     */
    public function obtenerPatrones()
    {
        $patrones = $this->distribucionService->obtenerPatronesDisponibles();
        return response()->json(['patrones' => $patrones]);
    }
}
