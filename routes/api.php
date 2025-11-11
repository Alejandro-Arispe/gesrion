<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\administracion\AuthController;
use App\Http\Controllers\administracion\UserController;
use App\Http\Controllers\administracion\RolController;
use App\Http\Controllers\ConfiguracionAcademica\FacultadController;
use App\Http\Controllers\ConfiguracionAcademica\GestionController;
use App\Http\Controllers\ConfiguracionAcademica\DocenteController;
use App\Http\Controllers\ConfiguracionAcademica\MateriaController;
use App\Http\Controllers\ConfiguracionAcademica\GrupoController;
use App\Http\Controllers\ConfiguracionAcademica\AulaController;
use App\Http\Controllers\Planificacion\HorarioController;

// ============================================
// RUTAS PÚBLICAS (Sin autenticación)
// ============================================
Route::post('/login', [AuthController::class, 'login']);

// ============================================
// RUTAS PROTEGIDAS (Requieren autenticación)
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- AUTENTICACIÓN ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/cambiar-password', [AuthController::class, 'cambiarPassword']);
    // Asistencia con GPS
    Route::post('asistencia/registrar-gps', [\App\Http\Controllers\ControlSeguimiento\AsistenciaController::class, 'registrarConGPS']);
    Route::get('asistencia/mis-horarios-hoy', [\App\Http\Controllers\ControlSeguimiento\AsistenciaController::class, 'misHorariosHoy']);

    // ============================================
    // PAQUETE: ADMINISTRACIÓN
    // ============================================
    
    // --- USUARIOS (CU02) ---
    Route::middleware('permission:gestionar_usuarios')->group(function () {
        Route::apiResource('usuarios', UserController::class);
    });

    // --- ROLES (CU02) ---
    Route::middleware('permission:gestionar_roles')->group(function () {
        Route::apiResource('roles', RolController::class);
        Route::post('roles/{id}/permisos', [RolController::class, 'asignarPermisos']);
    });

    // ============================================
    // PAQUETE: CONFIGURACIÓN ACADÉMICA
    // ============================================
    
    // --- FACULTADES ---
    Route::middleware('permission:gestionar_facultades')->group(function () {
        Route::apiResource('facultades', FacultadController::class);
    });

    // --- GESTIONES ACADÉMICAS (CU03) ---
    Route::middleware('permission:gestionar_gestiones')->group(function () {
        Route::apiResource('gestiones', GestionController::class)->except(['destroy']);
        Route::post('gestiones/{id}/activar', [GestionController::class, 'activar']);
    });

    // --- DOCENTES (CU04) ---
    Route::middleware('permission:gestionar_docentes')->group(function () {
        Route::apiResource('docentes', DocenteController::class);
    });

    // --- MATERIAS (CU05) ---
    Route::middleware('permission:gestionar_materias')->group(function () {
        Route::apiResource('materias', MateriaController::class);
    });

    // --- GRUPOS (CU05) ---
    Route::middleware('permission:gestionar_grupos')->group(function () {
        Route::apiResource('grupos', GrupoController::class);
    });

    // --- AULAS (CU06) ---
    Route::middleware('permission:gestionar_aulas')->group(function () {
        Route::apiResource('aulas', AulaController::class);
    });

    // ============================================
    // PAQUETE: PLANIFICACIÓN ACADÉMICA
    // ============================================
    
    // --- HORARIOS (CU07) ---
    Route::middleware('permission:gestionar_horarios')->group(function () {
        Route::apiResource('horarios', HorarioController::class);
        Route::post('horarios/validar-conflictos', [HorarioController::class, 'validarConflictos']);
        Route::post('horarios/asignar-automatico', [HorarioController::class, 'asignarAutomatico']);
        Route::get('horarios/carga-horaria', [HorarioController::class, 'obtenerCargaHoraria']);
    });

    // --- QR DE AULAS (CU07) ---
    Route::middleware('permission:gestionar_horarios')->group(function () {
        Route::get('qr-aula/listar', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'listar']);
        Route::post('qr-aula/generar/{idAula}', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'generar']);
        Route::post('qr-aula/regenerar/{idAula}', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'regenerar']);
        Route::post('qr-aula/generar-todos', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'generarTodos']);
        Route::get('qr-aula/{idAula}', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'obtener']);
        Route::get('qr-aula/{idAula}/mostrar', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'mostrar']);
        Route::get('qr-aula/{idAula}/descargar', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'descargar']);
    });

    // --- VALIDAR QR LEÍDO (Endpoint público para asistencia) ---
    Route::post('qr-aula/validar', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'validar']);

    // ============================================
    // RUTAS DE CONSULTA (Sin restricción de permisos)
    // ============================================
    Route::get('facultades-publicas', [FacultadController::class, 'index']);
    Route::get('horarios-consulta', [HorarioController::class, 'index']);
});