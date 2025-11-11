<?php

namespace App\Http\Controllers\Planificacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Planificacion\QrAula;
use App\Models\ConfiguracionAcademica\Aula;
use App\Services\QrGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Exception;

class QrAulaController extends Controller
{
    protected $qrService;

    public function __construct(QrGeneratorService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Mostrar interfaz de administración de QR
     */
    public function index()
    {
        return view('planificacion.generador-qr');
    }

    /**
     * Generar QR para un aula específica
     */
    public function generar(Request $request, $idAula)
    {
        try {
            $aula = Aula::findOrFail($idAula);

            $qr = $this->qrService->generarQrAula($idAula);

            return response()->json([
                'message' => 'QR generado exitosamente',
                'qr' => [
                    'id' => $qr->id,
                    'id_aula' => $qr->id_aula,
                    'nro_aula' => $aula->nro,
                    'token' => $qr->token,
                    'codigo_qr' => $qr->codigo_qr,
                    'generado_en' => $qr->generado_en
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al generar QR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener QR de un aula
     */
    public function obtener($idAula)
    {
        try {
            $aula = Aula::findOrFail($idAula);
            $qr = QrAula::where('id_aula', $idAula)->first();

            if (!$qr) {
                return response()->json([
                    'message' => 'QR no encontrado para esta aula',
                    'existe' => false
                ], 404);
            }

            return response()->json([
                'qr' => [
                    'id' => $qr->id,
                    'id_aula' => $qr->id_aula,
                    'nro_aula' => $aula->nro,
                    'codigo_qr' => $qr->codigo_qr,
                    'generado_en' => $qr->generado_en
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener QR',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Descargar QR como imagen PNG
     */
    public function descargar($idAula)
    {
        try {
            $aula = Aula::findOrFail($idAula);
            $qr = QrAula::where('id_aula', $idAula)->firstOrFail();

            // El código_qr contiene SVG, lo convertimos a PNG si es necesario
            // Por ahora retornamos el SVG directo
            return response($qr->codigo_qr)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', "attachment; filename=qr_aula_{$aula->nro}.svg");

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al descargar QR',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mostrar QR como imagen en el navegador
     */
    public function mostrar($idAula)
    {
        try {
            $qr = QrAula::where('id_aula', $idAula)->firstOrFail();

            return response($qr->codigo_qr)
                ->header('Content-Type', 'image/svg+xml');

        } catch (Exception $e) {
            return response()->json([
                'message' => 'QR no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Validar QR leído desde escaneo
     */
    public function validar(Request $request)
    {
        try {
            $request->validate([
                'codigo_qr_leido' => 'required|string'
            ]);

            $resultado = $this->qrService->validarQrLeido($request->codigo_qr_leido);

            if ($resultado === false) {
                return response()->json([
                    'valido' => false,
                    'message' => 'QR no válido o no registrado'
                ], 400);
            }

            return response()->json([
                'valido' => true,
                'aula' => $resultado
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al validar QR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerar QR para un aula
     */
    public function regenerar($idAula)
    {
        try {
            $aula = Aula::findOrFail($idAula);

            $qr = $this->qrService->regenerarQrAula($idAula);

            return response()->json([
                'message' => 'QR regenerado exitosamente',
                'qr' => [
                    'id' => $qr->id,
                    'id_aula' => $qr->id_aula,
                    'nro_aula' => $aula->nro,
                    'token' => $qr->token,
                    'codigo_qr' => $qr->codigo_qr,
                    'generado_en' => $qr->generado_en
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al regenerar QR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar QR para todas las aulas disponibles
     */
    public function generarTodos(Request $request)
    {
        try {
            $resumen = $this->qrService->generarQrTodasAulas();

            return response()->json([
                'message' => 'Generación de QR completada',
                'resumen' => $resumen
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al generar QR masivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas las aulas con sus QR
     */
    public function listar()
    {
        try {
            $aulas = Aula::with('horarios')
                        ->where('disponible', true)
                        ->get()
                        ->map(function ($aula) {
                            $qr = QrAula::where('id_aula', $aula->id_aula)->first();
                            return [
                                'id_aula' => $aula->id_aula,
                                'nro' => $aula->nro,
                                'piso' => $aula->piso,
                                'tipo' => $aula->tipo_aula,
                                'capacidad' => $aula->capacidad,
                                'ubicacion_gps' => $aula->ubicacion_gps,
                                'qr_generado' => $qr ? true : false,
                                'qr_id' => $qr?->id,
                                'qr_token' => $qr?->token
                            ];
                        });

            return response()->json([
                'aulas' => $aulas
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al listar aulas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar múltiples QRs como ZIP
     */
    public function descargarZip(Request $request)
    {
        try {
            $aulasIds = explode(',', $request->input('aulas'));
            $aulasIds = array_filter(array_map('intval', $aulasIds));

            if (empty($aulasIds)) {
                return response()->json(['message' => 'No aulas seleccionadas'], 400);
            }

            $qrs = QrAula::whereIn('id_aula', $aulasIds)->with('aula')->get();

            if ($qrs->isEmpty()) {
                return response()->json(['message' => 'No QRs encontrados'], 404);
            }

            $zipPath = storage_path('app/temp/qr_' . time() . '.zip');
            $zip = new ZipArchive();
            $zip->open($zipPath, ZipArchive::CREATE);

            foreach ($qrs as $qr) {
                $nombreArchivo = "QR_Aula_{$qr->aula->nro}.svg";
                $zip->addFromString($nombreArchivo, $qr->codigo_qr);
            }

            $zip->close();

            return response()->download($zipPath, 'qr_aulas.zip')->deleteFileAfterSend(true);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al descargar QRs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar todos los QRs como ZIP
     */
    public function descargarZipTodos()
    {
        try {
            $qrs = QrAula::with('aula')->get();

            if ($qrs->isEmpty()) {
                return response()->json(['message' => 'No QRs encontrados'], 404);
            }

            $zipPath = storage_path('app/temp/qr_todos_' . time() . '.zip');
            $zip = new ZipArchive();
            $zip->open($zipPath, ZipArchive::CREATE);

            foreach ($qrs as $qr) {
                $nombreArchivo = "QR_Aula_{$qr->aula->nro}.svg";
                $zip->addFromString($nombreArchivo, $qr->codigo_qr);
            }

            $zip->close();

            return response()->download($zipPath, 'qr_todas_aulas.zip')->deleteFileAfterSend(true);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al descargar QRs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerar múltiples QRs
     */
    public function regenerarMultiples(Request $request)
    {
        try {
            $aulasIds = explode(',', $request->input('aulas'));
            $aulasIds = array_filter(array_map('intval', $aulasIds));

            if (empty($aulasIds)) {
                return response()->json(['message' => 'No aulas seleccionadas'], 400);
            }

            DB::beginTransaction();

            $regenerados = 0;
            foreach ($aulasIds as $idAula) {
                try {
                    $this->qrService->regenerarQrAula($idAula);
                    $regenerados++;
                } catch (Exception $e) {
                    // Continuar con otros
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'QR regenerados exitosamente',
                'regenerados' => $regenerados
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al regenerar QRs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerar todos los QRs
     */
    public function regenerarTodos()
    {
        try {
            $aulas = Aula::where('disponible', true)->pluck('id_aula');

            DB::beginTransaction();

            $regenerados = 0;
            foreach ($aulas as $idAula) {
                try {
                    $this->qrService->regenerarQrAula($idAula);
                    $regenerados++;
                } catch (Exception $e) {
                    // Continuar
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Todos los QR han sido regenerados',
                'regenerados' => $regenerados
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al regenerar QRs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar PDF de todos los QRs imprimibles
     */
    public function descargarPdfImprimible(Request $request)
    {
        try {
            $formato = $request->input('formato', '2x3');
            
            $qrs = QrAula::with('aula')
                        ->whereHas('aula', function ($query) {
                            $query->where('disponible', true);
                        })
                        ->get();

            if ($qrs->isEmpty()) {
                return redirect()->back()->with('error', 'No hay QRs disponibles');
            }

            // Generar HTML para PDF
            $html = view('planificacion.qr-pdf-template', [
                'qrs' => $qrs,
                'formato' => $formato
            ])->render();

            // Generar PDF usando DomPDF
            $pdf = \PDF::loadHTML($html)
                        ->setPaper('a4', 'portrait')
                        ->setOption('isPhpEnabled', true)
                        ->setOption('isRemoteEnabled', true);

            return $pdf->download('qr_aulas_' . date('Y-m-d') . '.pdf');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }
}
