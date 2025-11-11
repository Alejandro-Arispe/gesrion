<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeneradorUsuariosDocentesService;
use App\Models\ConfiguracionAcademica\Docente;
use App\Models\Administracion\Usuario;

class GeneradorUsuariosDocentesController extends Controller
{
    protected $generador;

    public function __construct(GeneradorUsuariosDocentesService $generador)
    {
        $this->generador = $generador;
    }

    /**
     * Mostrar interfaz de gestión de usuarios docentes
     */
    public function index()
    {
        $docentes = Docente::where('estado', true)
                          ->with('usuario')
                          ->orderBy('nombre')
                          ->paginate(20);

        $estadisticas = [
            'total_docentes' => Docente::where('estado', true)->count(),
            'usuarios_creados' => Usuario::whereHas('docente')
                                        ->where('activo', true)
                                        ->count(),
            'usuarios_pendientes' => Docente::where('estado', true)
                                           ->whereDoesntHave('usuario')
                                           ->count()
        ];

        return view('administracion.usuarios-docentes', compact('docentes', 'estadisticas'));
    }

    /**
     * Generar usuarios para todos los docentes sin usuario
     */
    public function generarMasivo()
    {
        try {
            $resultado = $this->generador->generarUsuariosDocentes();

            if (isset($resultado['exito']) && !$resultado['exito']) {
                return response()->json($resultado, 422);
            }

            return response()->json([
                'exito' => true,
                'mensaje' => "Se crearon {$resultado['creados']} usuarios, se omitieron {$resultado['omitidos']}",
                'resumen' => $resultado
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar PDF con credenciales de docentes
     */
    public function descargarCredencialesPDF()
    {
        try {
            $credenciales = $this->generador->obtenerCredencialesDocentes();

            if ($credenciales->isEmpty()) {
                return redirect()->back()->with('error', 'No hay usuarios de docentes para descargar');
            }

            $pdf = \PDF::loadView('administracion.credenciales-docentes-pdf', [
                'credenciales' => $credenciales,
                'fecha_generacion' => now()->format('d/m/Y H:i')
            ])->setPaper('a4', 'portrait');

            return $pdf->download('credenciales_docentes_' . date('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Regenerar contraseña para un docente
     */
    public function regenerarPassword($idDocente)
    {
        try {
            $resultado = $this->generador->regenerarPassword($idDocente);

            if (!$resultado['exito']) {
                return response()->json($resultado, 422);
            }

            return response()->json($resultado);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar usuario de un docente
     */
    public function desactivarUsuario($idDocente)
    {
        try {
            $resultado = $this->generador->desactivarUsuario($idDocente);

            if (!$resultado['exito']) {
                return response()->json($resultado, 422);
            }

            return response()->json($resultado);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
