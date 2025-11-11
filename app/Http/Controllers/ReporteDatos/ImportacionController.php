<?php

namespace App\Http\Controllers\ReporteDatos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\Docente;
use App\Models\ConfiguracionAcademica\Materia;
use App\Models\ReporteDatos\Importacion;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ImportacionController extends Controller
{
    public function index()
    {
        $importaciones = Importacion::orderBy('fecha_importacion', 'desc')->paginate(20);
        return view('reporte-datos.importacion.index', compact('importaciones'));
    }

    public function create()
    {
        return view('reporte-datos.importacion.create');
    }

    /**
     * Importar docentes desde Excel/CSV
     */
    public function importarDocentes(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|file|mimes:xlsx,xls,csv|max:2048'
            ]);

            $archivo = $request->file('archivo');
            $nombreArchivo = $archivo->getClientOriginalName();

            DB::beginTransaction();

            $data = Excel::toArray([], $archivo)[0];
            $importados = 0;
            $errores = [];

            // Saltar la primera fila (encabezados)
            array_shift($data);

            foreach ($data as $index => $fila) {
                try {
                    if (count($fila) < 4) continue; // Validar que tenga al menos 4 columnas

                    Docente::create([
                        'ci' => $fila[0],
                        'nombre' => $fila[1],
                        'correo' => $fila[2] ?? null,
                        'telefono' => $fila[3] ?? null,
                        'sexo' => $fila[4] ?? 'M',
                        'id_facultad' => $fila[5] ?? null,
                        'estado' => true
                    ]);
                    $importados++;
                } catch (Exception $e) {
                    $errores[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            // Registrar importación
            Importacion::create([
                'tipo' => 'Docentes',
                'archivo_nombre' => $nombreArchivo,
                'fecha_importacion' => Carbon::now(),
                'estado' => empty($errores) ? 'Completado' : 'Con errores'
            ]);

            DB::commit();

            $mensaje = "Importación completada. $importados docentes importados.";
            if (!empty($errores)) {
                $mensaje .= " Errores: " . implode(', ', $errores);
            }

            return redirect()->route('reporte-datos.importacion.index')
                ->with('success', $mensaje);
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error en la importación: ' . $e->getMessage());
        }
    }

    /**
     * Importar materias desde Excel/CSV
     */
    public function importarMaterias(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|file|mimes:xlsx,xls,csv|max:2048'
            ]);

            $archivo = $request->file('archivo');
            $nombreArchivo = $archivo->getClientOriginalName();

            DB::beginTransaction();

            $data = Excel::toArray([], $archivo)[0];
            $importados = 0;
            $errores = [];

            // Saltar encabezados
            array_shift($data);

            foreach ($data as $index => $fila) {
                try {
                    if (count($fila) < 2) continue;

                    Materia::create([
                        'codigo' => $fila[0],
                        'nombre' => $fila[1],
                        'carga_horaria' => $fila[2] ?? 4,
                        'id_facultad' => $fila[3] ?? null
                    ]);
                    $importados++;
                } catch (Exception $e) {
                    $errores[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            Importacion::create([
                'tipo' => 'Materias',
                'archivo_nombre' => $nombreArchivo,
                'fecha_importacion' => Carbon::now(),
                'estado' => empty($errores) ? 'Completado' : 'Con errores'
            ]);

            DB::commit();

            $mensaje = "Importación completada. $importados materias importadas.";
            if (!empty($errores)) {
                $mensaje .= " Errores: " . implode(', ', array_slice($errores, 0, 5));
            }

            return redirect()->route('reporte-datos.importacion.index')
                ->with('success', $mensaje);
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error en la importación: ' . $e->getMessage());
        }
    }
}