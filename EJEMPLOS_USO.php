<?php

/**
 * EJEMPLOS DE USO - Sistema de Distribución Multi-día de Horarios
 * 
 * Este archivo contiene ejemplos prácticos de cómo usar el nuevo sistema
 */

// ==============================================================================
// EJEMPLO 1: CREAR MATERIA QUE REQUIERE LABORATORIO
// ==============================================================================

use App\Models\ConfiguracionAcademica\Materia;

// Opción A: Por artisan tinker
/*
$materia = Materia::create([
    'codigo' => 'LAB-PYTHON',
    'nombre' => 'Laboratorio de Python',
    'carga_horaria' => 4.5,
    'id_facultad' => 1,
    'requiere_laboratorio' => true  // ← NUEVO CAMPO
]);
*/

// Opción B: Desde formulario web en /configuracion-academica/materias
// 1. Ir a /configuracion-academica/materias
// 2. Clic "Nueva Materia"
// 3. Llenar formulario
// 4. ✓ Marcar "Esta materia requiere laboratorio"
// 5. Guardar

// ==============================================================================
// EJEMPLO 2: GENERAR DISTRIBUCIÓN CON PATRÓN LMV
// ==============================================================================

use App\Services\DistribucionHorariosService;
use App\Models\ConfiguracionAcademica\Grupo;

$distribucionService = new DistribucionHorariosService();

// Obtener grupo
$grupo = Grupo::find(5);  // Grupo Cálculo I - Sección A

// Generar distribución LMV (Lunes, Miércoles, Viernes)
$resultado = $distribucionService->generarDistribucion(
    grupo: $grupo,
    patron: 'LMV',           // Patrón predeterminado
    horaInicio: '08:00',     // Hora de inicio
);

// Resultado:
/*
[
    'exito' => true,
    'mensaje' => '3 horarios creados exitosamente',
    'horarios' => [
        ['id_horario' => 101, 'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:30'],
        ['id_horario' => 102, 'dia' => 'Miércoles', 'hora_inicio' => '08:00', 'hora_fin' => '09:30'],
        ['id_horario' => 103, 'dia' => 'Viernes', 'hora_inicio' => '08:00', 'hora_fin' => '09:30']
    ],
    'distribucion' => [
        'patron' => 'LMV',
        'dias' => ['Lunes', 'Miércoles', 'Viernes'],
        'duracion_horas_por_dia' => 1.5,
        'carga_total_horas' => 4.5
    ]
]
*/

// ==============================================================================
// EJEMPLO 3: GENERAR DISTRIBUCIÓN CON PATRÓN MJ
// ==============================================================================

$resultado = $distribucionService->generarDistribucion(
    grupo: $grupo,
    patron: 'MJ',               // Martes y Jueves
    horaInicio: '10:00',        // 10:00 AM
);

// Resultado: 2 horarios de 2:15h cada uno (Martes y Jueves 10:00-12:15)

// ==============================================================================
// EJEMPLO 4: GENERAR DISTRIBUCIÓN PERSONALIZADA
// ==============================================================================

$resultado = $distribucionService->generarDistribucion(
    grupo: $grupo,
    patron: 'PERSONALIZADO',
    horaInicio: '14:00',
    diasPersonalizados: ['Lunes', 'Miércoles'],        // Solo 2 días
    duracionPersonalizada: 2.0                          // 2 horas cada día
);

// Resultado: 2 horarios de 2h cada uno (Lunes y Miércoles 14:00-16:00)

// ==============================================================================
// EJEMPLO 5: OBTENER SUGERENCIA DE PATRÓN
// ==============================================================================

$patronSugerido = $distribucionService->sugerirPatron(4.5);
// Retorna: 'LMV' (Lunes, Miércoles, Viernes)

$patronSugerido = $distribucionService->sugerirPatron(3.0);
// Retorna: patrón que mejor encaje con 3 horas

// ==============================================================================
// EJEMPLO 6: OBTENER PATRONES DISPONIBLES
// ==============================================================================

$patrones = $distribucionService->obtenerPatronesDisponibles();

/*
[
    ['clave' => 'LMV', 'dias' => 'Lunes, Miércoles, Viernes', 'duracion_horas' => 1.5, ...],
    ['clave' => 'MJ', 'dias' => 'Martes, Jueves', 'duracion_horas' => 2.25, ...],
    ['clave' => 'L', 'dias' => 'Lunes', 'duracion_horas' => 4.5, ...],
    // ... más patrones
]
*/

// ==============================================================================
// EJEMPLO 7: USAR DESDE CONTROLADOR (RECOMENDADO)
// ==============================================================================

namespace App\Http\Controllers;

use App\Services\DistribucionHorariosService;
use App\Models\ConfiguracionAcademica\Grupo;
use Illuminate\Http\Request;

class MiControlador extends Controller
{
    protected $distribucionService;

    public function __construct(DistribucionHorariosService $distribucionService)
    {
        $this->distribucionService = $distribucionService;
    }

    public function generarDistribucion(Request $request)
    {
        $grupo = Grupo::findOrFail($request->id_grupo);

        $resultado = $this->distribucionService->generarDistribucion(
            grupo: $grupo,
            patron: $request->patron ?? 'LMV',
            horaInicio: $request->hora_inicio ?? '08:00',
            diasPersonalizados: $request->dias_personalizados ?? [],
            duracionPersonalizada: $request->duracion_personalizada ?? null
        );

        return response()->json($resultado);
    }
}

// ==============================================================================
// EJEMPLO 8: VALIDACIONES Y MANEJO DE ERRORES
// ==============================================================================

$resultado = $distribucionService->generarDistribucion($grupo, 'LMV', '08:00');

if (!$resultado['exito']) {
    // Algo salió mal
    $error = $resultado['mensaje'];
    // Posibles errores:
    // - "La materia no tiene carga horaria definida"
    // - "Conflicto de horario: Docente ya tiene clase..."
    // - "La carga horaria configurada (3h) no coincide..."
    
    \Log::error("Error en distribución: {$error}");
    return response()->json($resultado, 422);
}

// Éxito
$horarios = $resultado['horarios'];
$aviso = $resultado['aviso'] ?? null;  // Puede haber advertencia

// ==============================================================================
// EJEMPLO 9: VERIFICAR QUE MATERIA REQUIERE LABORATORIO
// ==============================================================================

$materia = Materia::find(5);

if ($materia->requiere_laboratorio) {
    // Asignar aula de laboratorio (máx 1 vez/semana)
    $aulasLab = Aula::where('tipo_aula', 'Laboratorio')->get();
} else {
    // Asignar aula normal
    $aulasNormales = Aula::where('tipo_aula', 'Aula Normal')->get();
}

// ==============================================================================
// EJEMPLO 10: CONSULTAR DISTRIBUCIÓN GUARDADA
// ==============================================================================

$horario = \App\Models\Planificacion\Horario::find(101);

// Consultar patrón usado (guardado en JSON)
$distribucion = $horario->distribucion_dias;

/*
{
    "patron": "LMV",
    "dias": ["Lunes", "Miércoles", "Viernes"],
    "duracion_minutos": 90
}
*/

// Uso en vistas:
// @if($horario->distribucion_dias['patron'] === 'LMV')
//    <span class="badge bg-primary">LMV</span>
// @endif

// ==============================================================================
// EJEMPLO 11: FLUJO COMPLETO EN INTERFAZ WEB
// ==============================================================================

/*
1. Usuario accede a /planificacion/distribucion
2. Selecciona grupo "Cálculo I - Sección A"
3. Elige patrón "LMV (Lunes, Miércoles, Viernes)"
4. Ingresa hora inicio "08:00"
5. Clic "Generar Distribución"

En backend:
- DistribucionHorariosController::generar()
- Valida datos (grupo, patrón, hora)
- Llama DistribucionHorariosService::generarDistribucion()
- Valida conflictos
- Crea 3 registros en tabla horario
- Retorna JSON con resultado

En frontend:
- JavaScript recibe respuesta
- Muestra resultado en pantalla
- Usuario ve horarios generados
*/

// ==============================================================================
// EJEMPLO 12: CARGA HORARIA Y PATRONES
// ==============================================================================

/*
Materia con 4.5 horas (RECOMENDADO):
  ✓ Patrón LMV: 1:30h × 3 = 4:30h (coincide exacto)
  ✓ Patrón MJ: 2:15h × 2 = 4:30h (coincide exacto)

Materia con 3 horas:
  → Personalizar: 1h × 3 = 3h
  → O: 1:30h × 2 = 3h

Materia con 6 horas:
  → Personalizar: 2h × 3 = 6h
  → O: 3h × 2 = 6h

Materia con 2 horas:
  → Personalizar: 1h × 2 = 2h
  → O: 2h × 1 = 2h
*/

// ==============================================================================
// BONUS: OBTENER ESTADÍSTICAS DE DISTRIBUCIÓN
// ==============================================================================

use App\Models\Planificacion\Horario;

$estadisticas = Horario::where('tipo_asignacion', 'Automática')
    ->whereNotNull('distribucion_dias')
    ->get()
    ->groupBy('distribucion_dias->patron')
    ->map(function ($horarios, $patron) {
        return [
            'patron' => $patron,
            'cantidad' => $horarios->count(),
            'grupos' => $horarios->pluck('id_grupo')->unique()->count()
        ];
    });

/*
{
    'LMV': ['patron' => 'LMV', 'cantidad' => 45, 'grupos' => 15],
    'MJ': ['patron' => 'MJ', 'cantidad' => 38, 'grupos' => 19],
    'L': ['patron' => 'L', 'cantidad' => 10, 'grupos' => 10],
    ...
}
*/

// ==============================================================================
// FIN DE EJEMPLOS
// ==============================================================================

?>
