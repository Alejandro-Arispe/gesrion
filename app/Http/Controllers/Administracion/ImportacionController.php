<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\Docente;
use App\Models\ConfiguracionAcademica\Facultad;
use App\Models\ConfiguracionAcademica\Materia;
use App\Models\ConfiguracionAcademica\Grupo;
use App\Models\ConfiguracionAcademica\GestionAcademica;
use App\Models\Administracion\Usuario;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use DB;
use Exception;

class ImportacionController extends Controller
{
    /**
     * Mostrar formulario de importación
     */
    public function index()
    {
        return view('administracion.importacion.index');
    }

    /**
     * Vista previa del archivo CSV/XLSX
     */
    public function preview(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls',
                'tipo' => 'required|in:docentes,materias,usuarios,grupos'
            ]);

            $file = $request->file('file');
            $tipo = $request->tipo;

            // Leer el archivo
            if ($file->getClientOriginalExtension() === 'csv') {
                $datos = $this->leerCSV($file);
            } else {
                $datos = $this->leerExcel($file);
            }

            // Validar estructura según tipo
            $validacion = $this->validarEstructura($tipo, $datos);

            if (!$validacion['valido']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estructura inválida: ' . $validacion['error']
                ], 422);
            }

            // Preparar vista previa (máximo 10 filas)
            $preview = array_slice($datos, 0, 10);
            $totalRegistros = count($datos);

            return response()->json([
                'success' => true,
                'tipo' => $tipo,
                'total' => $totalRegistros,
                'preview' => $preview,
                'columnas' => $validacion['columnas'] ?? []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar archivo: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ejecutar la importación
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls',
                'tipo' => 'required|in:docentes,materias,usuarios,grupos'
            ]);

            $file = $request->file('file');
            $tipo = $request->tipo;

            // Leer el archivo
            if ($file->getClientOriginalExtension() === 'csv') {
                $datos = $this->leerCSV($file);
            } else {
                $datos = $this->leerExcel($file);
            }

            // Importar según tipo
            $resultado = match ($tipo) {
                'docentes' => $this->importarDocentes($datos),
                'materias' => $this->importarMaterias($datos),
                'usuarios' => $this->importarUsuarios($datos),
                'grupos' => $this->importarGrupos($datos),
                default => ['success' => false, 'message' => 'Tipo desconocido']
            };

            return response()->json($resultado);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en importación: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Leer archivo CSV
     */
    private function leerCSV($file)
    {
        $datos = [];
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle, 1000, ',');

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $fila = [];
            foreach ($header as $i => $columna) {
                $fila[trim(strtolower($columna))] = trim($row[$i] ?? '');
            }
            if (implode('', $fila) !== '') { // Ignorar filas vacías
                $datos[] = $fila;
            }
        }
        fclose($handle);

        return $datos;
    }

    /**
     * Leer archivo XLSX
     */
    private function leerExcel($file)
    {
        $datos = [];
        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();

        $header = [];
        $primerFila = true;

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $fila = [];

            foreach ($cellIterator as $cell) {
                $valor = $cell->getValue();
                if ($primerFila) {
                    $header[] = trim(strtolower($valor ?? ''));
                } else {
                    $fila[] = $valor ?? '';
                }
            }

            if ($primerFila) {
                $primerFila = false;
            } else {
                // Asociar valores con headers
                $filaAsociativa = [];
                foreach ($header as $i => $columna) {
                    $filaAsociativa[$columna] = $fila[$i] ?? '';
                }
                if (implode('', $fila) !== '') {
                    $datos[] = $filaAsociativa;
                }
            }
        }

        return $datos;
    }

    /**
     * Validar estructura del archivo
     */
    private function validarEstructura($tipo, $datos)
    {
        if (empty($datos)) {
            return ['valido' => false, 'error' => 'Archivo vacío'];
        }

        $columnasPorTipo = [
            'docentes' => ['nombre', 'email', 'telefono', 'id_facultad'],
            'materias' => ['nombre', 'codigo', 'creditos', 'id_gestion'],
            'usuarios' => ['username', 'password', 'email', 'id_rol'],
            'grupos' => ['nombre', 'id_materia', 'id_gestion', 'id_docente']
        ];

        $columnasRequeridas = $columnasPorTipo[$tipo] ?? [];
        $columnasArchivo = array_keys($datos[0]);

        foreach ($columnasRequeridas as $col) {
            if (!in_array($col, $columnasArchivo)) {
                return ['valido' => false, 'error' => "Columna requerida ausente: $col"];
            }
        }

        return ['valido' => true, 'columnas' => $columnasArchivo];
    }

    /**
     * Importar docentes
     */
    private function importarDocentes($datos)
    {
        $creados = 0;
        $errores = [];
        $fila = 1;

        DB::beginTransaction();
        try {
            foreach ($datos as $dato) {
                $fila++;
                try {
                    // Validar datos
                    if (empty($dato['nombre']) || empty($dato['email'])) {
                        $errores[] = "Fila $fila: Nombre o email vacío";
                        continue;
                    }

                    // Verificar facultad
                    if (!empty($dato['id_facultad'])) {
                        $facultad = Facultad::find($dato['id_facultad']);
                        if (!$facultad) {
                            $errores[] = "Fila $fila: Facultad no encontrada (ID: {$dato['id_facultad']})";
                            continue;
                        }
                    }

                    // Verificar email único
                    if (Docente::where('email', $dato['email'])->exists()) {
                        $errores[] = "Fila $fila: Email ya registrado ({$dato['email']})";
                        continue;
                    }

                    // Crear docente
                    Docente::create([
                        'nombre' => $dato['nombre'],
                        'email' => $dato['email'],
                        'telefono' => $dato['telefono'] ?? null,
                        'id_facultad' => $dato['id_facultad'] ?? null,
                        'estado' => true
                    ]);

                    $creados++;
                } catch (Exception $e) {
                    $errores[] = "Fila $fila: {$e->getMessage()}";
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Importación completada: $creados docentes creados",
                'creados' => $creados,
                'errores' => $errores,
                'total_errores' => count($errores)
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error en transacción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Importar materias
     */
    private function importarMaterias($datos)
    {
        $creadas = 0;
        $errores = [];
        $fila = 1;

        DB::beginTransaction();
        try {
            foreach ($datos as $dato) {
                $fila++;
                try {
                    // Validar datos
                    if (empty($dato['nombre']) || empty($dato['codigo'])) {
                        $errores[] = "Fila $fila: Nombre o código vacío";
                        continue;
                    }

                    // Verificar gestión
                    if (!empty($dato['id_gestion'])) {
                        $gestion = GestionAcademica::find($dato['id_gestion']);
                        if (!$gestion) {
                            $errores[] = "Fila $fila: Gestión no encontrada (ID: {$dato['id_gestion']})";
                            continue;
                        }
                    }

                    // Verificar código único
                    if (Materia::where('codigo', $dato['codigo'])->exists()) {
                        $errores[] = "Fila $fila: Código duplicado ({$dato['codigo']})";
                        continue;
                    }

                    // Crear materia
                    Materia::create([
                        'nombre' => $dato['nombre'],
                        'codigo' => $dato['codigo'],
                        'creditos' => (int)($dato['creditos'] ?? 4),
                        'id_gestion' => $dato['id_gestion'] ?? null,
                        'estado' => true
                    ]);

                    $creadas++;
                } catch (Exception $e) {
                    $errores[] = "Fila $fila: {$e->getMessage()}";
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Importación completada: $creadas materias creadas",
                'creadas' => $creadas,
                'errores' => $errores,
                'total_errores' => count($errores)
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error en transacción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Importar usuarios
     */
    private function importarUsuarios($datos)
    {
        $creados = 0;
        $errores = [];
        $fila = 1;

        DB::beginTransaction();
        try {
            foreach ($datos as $dato) {
                $fila++;
                try {
                    // Validar datos
                    if (empty($dato['username']) || empty($dato['password']) || empty($dato['email'])) {
                        $errores[] = "Fila $fila: Username, password o email vacío";
                        continue;
                    }

                    // Verificar username único
                    if (Usuario::where('username', $dato['username'])->exists()) {
                        $errores[] = "Fila $fila: Username ya registrado ({$dato['username']})";
                        continue;
                    }

                    // Verificar email único
                    if (Usuario::where('email', $dato['email'])->exists()) {
                        $errores[] = "Fila $fila: Email ya registrado ({$dato['email']})";
                        continue;
                    }

                    // Crear usuario
                    Usuario::create([
                        'username' => $dato['username'],
                        'email' => $dato['email'],
                        'password' => bcrypt($dato['password']),
                        'id_rol' => $dato['id_rol'] ?? 3,
                        'estado' => true
                    ]);

                    $creados++;
                } catch (Exception $e) {
                    $errores[] = "Fila $fila: {$e->getMessage()}";
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Importación completada: $creados usuarios creados",
                'creados' => $creados,
                'errores' => $errores,
                'total_errores' => count($errores)
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error en transacción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Importar grupos
     */
    private function importarGrupos($datos)
    {
        $creados = 0;
        $errores = [];
        $fila = 1;

        DB::beginTransaction();
        try {
            foreach ($datos as $dato) {
                $fila++;
                try {
                    // Validar datos
                    if (empty($dato['nombre'])) {
                        $errores[] = "Fila $fila: Nombre del grupo vacío";
                        continue;
                    }

                    // Verificar materia
                    if (!empty($dato['id_materia'])) {
                        $materia = Materia::find($dato['id_materia']);
                        if (!$materia) {
                            $errores[] = "Fila $fila: Materia no encontrada (ID: {$dato['id_materia']})";
                            continue;
                        }
                    }

                    // Verificar gestión
                    if (!empty($dato['id_gestion'])) {
                        $gestion = GestionAcademica::find($dato['id_gestion']);
                        if (!$gestion) {
                            $errores[] = "Fila $fila: Gestión no encontrada (ID: {$dato['id_gestion']})";
                            continue;
                        }
                    }

                    // Crear grupo
                    Grupo::create([
                        'nombre' => $dato['nombre'],
                        'id_materia' => $dato['id_materia'] ?? null,
                        'id_gestion' => $dato['id_gestion'] ?? null,
                        'id_docente' => $dato['id_docente'] ?? null,
                        'estado' => true
                    ]);

                    $creados++;
                } catch (Exception $e) {
                    $errores[] = "Fila $fila: {$e->getMessage()}";
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Importación completada: $creados grupos creados",
                'creados' => $creados,
                'errores' => $errores,
                'total_errores' => count($errores)
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error en transacción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Descargar plantilla CSV
     */
    public function descargarPlantilla($tipo)
    {
        $plantillas = [
            'docentes' => [
                'headers' => ['nombre', 'email', 'telefono', 'id_facultad'],
                'ejemplo' => [
                    ['Juan Pérez', 'juan@example.com', '70123456', '1'],
                    ['María García', 'maria@example.com', '70234567', '2']
                ]
            ],
            'materias' => [
                'headers' => ['nombre', 'codigo', 'creditos', 'id_gestion'],
                'ejemplo' => [
                    ['Matemáticas I', 'MAT101', '4', '1'],
                    ['Física II', 'FIS102', '3', '1']
                ]
            ],
            'usuarios' => [
                'headers' => ['username', 'password', 'email', 'id_rol'],
                'ejemplo' => [
                    ['usuario1', 'password123', 'usuario1@example.com', '3'],
                    ['usuario2', 'password456', 'usuario2@example.com', '3']
                ]
            ],
            'grupos' => [
                'headers' => ['nombre', 'id_materia', 'id_gestion', 'id_docente'],
                'ejemplo' => [
                    ['Grupo A', '1', '1', '1'],
                    ['Grupo B', '1', '1', '2']
                ]
            ]
        ];

        if (!isset($plantillas[$tipo])) {
            abort(404, 'Plantilla no encontrada');
        }

        $plantilla = $plantillas[$tipo];
        $filename = "plantilla_importacion_$tipo.csv";

        $output = fopen('php://output', 'w');
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");

        fputcsv($output, $plantilla['headers']);
        foreach ($plantilla['ejemplo'] as $fila) {
            fputcsv($output, $fila);
        }
        fclose($output);
        exit;
    }
}
