<?php
use Illuminate\Support\Facades\Route;

// Controladores de Administración
use App\Http\Controllers\Administracion\AuthController;
use App\Http\Controllers\Administracion\UserController;
use App\Http\Controllers\Administracion\RolController;
use App\Http\Controllers\Administracion\BitacoraController;

// Controladores de Configuración Académica
use App\Http\Controllers\ConfiguracionAcademica\GestionController;
use App\Http\Controllers\ConfiguracionAcademica\DocenteController;
use App\Http\Controllers\ConfiguracionAcademica\MateriaController;
use App\Http\Controllers\ConfiguracionAcademica\GrupoController;
use App\Http\Controllers\ConfiguracionAcademica\AulaController;

// Controladores de Planificación
use App\Http\Controllers\Planificacion\HorarioController;

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación
|--------------------------------------------------------------------------
*/
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren Autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function() {
        $stats = [
            'docentes' => \App\Models\ConfiguracionAcademica\Docente::where('estado', true)->count(),
            'materias' => \App\Models\ConfiguracionAcademica\Materia::count(),
            'aulas' => \App\Models\ConfiguracionAcademica\Aula::count(),
            'horarios' => \App\Models\Planificacion\Horario::count()
        ];
        
        $gestionActual = \App\Models\ConfiguracionAcademica\GestionAcademica::where('estado', true)->first();
        
        return view('dashboard', compact('stats', 'gestionActual'));
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | PAQUETE: ADMINISTRACIÓN
    |--------------------------------------------------------------------------
    */
    Route::prefix('administracion')->name('administracion.')->group(function () {
        
        // Usuarios
        Route::resource('usuarios', UserController::class);
        
        // Usuarios Docentes (generador masivo)
        Route::prefix('usuarios-docentes')->name('usuarios-docentes.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Administracion\GeneradorUsuariosDocentesController::class, 'index'])
                ->name('index');
            Route::post('generar-masivo', [\App\Http\Controllers\Administracion\GeneradorUsuariosDocentesController::class, 'generarMasivo'])
                ->name('generar-masivo');
            Route::get('descargar-credenciales-pdf', [\App\Http\Controllers\Administracion\GeneradorUsuariosDocentesController::class, 'descargarCredencialesPDF'])
                ->name('descargar-credenciales-pdf');
            Route::post('{id_docente}/regenerar-password', [\App\Http\Controllers\Administracion\GeneradorUsuariosDocentesController::class, 'regenerarPassword'])
                ->name('regenerar-password');
            Route::post('{id_docente}/desactivar', [\App\Http\Controllers\Administracion\GeneradorUsuariosDocentesController::class, 'desactivarUsuario'])
                ->name('desactivar');
        });
        
        // Roles (si necesitas gestionar roles)
        Route::resource('roles', RolController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        
        // Bitácora
        Route::get('bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
        
        // Importación (CU11)
        Route::prefix('importacion')->name('importacion.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Administracion\ImportacionController::class, 'index'])
                ->name('index');
            Route::post('preview', [\App\Http\Controllers\Administracion\ImportacionController::class, 'preview'])
                ->name('preview');
            Route::post('import', [\App\Http\Controllers\Administracion\ImportacionController::class, 'import'])
                ->name('import');
            Route::get('descargar-plantilla', [\App\Http\Controllers\Administracion\ImportacionController::class, 'descargarPlantilla'])
                ->name('descargar-plantilla');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | PAQUETE: CONFIGURACIÓN ACADÉMICA
    |--------------------------------------------------------------------------
    */
    Route::prefix('configuracion-academica')->name('configuracion-academica.')->group(function () {
        // Gestiones Académicas (rutas web mínimas)
        Route::get('gestiones', function() {
            $gestiones = \App\Models\ConfiguracionAcademica\GestionAcademica::orderBy('anio','desc')->paginate(10);
            return view('configuracion-academica.gestiones.index', compact('gestiones'));
        })->name('gestiones.index');

        Route::get('gestiones/create', function() {
            return view('configuracion-academica.gestiones.create');
        })->name('gestiones.create');

        Route::post('gestiones', function(\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'anio' => 'required|integer|min:2020|max:2100',
                'semestre' => 'required|in:1,2',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after:fecha_inicio',
                'estado' => 'boolean'
            ]);

            \App\Models\ConfiguracionAcademica\GestionAcademica::create($data);
            return redirect()->route('configuracion-academica.gestiones.index')
                             ->with('success', 'Gestión creada exitosamente');
        })->name('gestiones.store');

        Route::get('gestiones/{id}/edit', function($id) {
            $gestion = \App\Models\ConfiguracionAcademica\GestionAcademica::findOrFail($id);
            return view('configuracion-academica.gestiones.edit', compact('gestion'));
        })->name('gestiones.edit');

        Route::put('gestiones/{id}', function(\Illuminate\Http\Request $request, $id) {
            $data = $request->validate([
                'anio' => 'required|integer|min:2020|max:2100',
                'semestre' => 'required|in:1,2',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after:fecha_inicio',
                'estado' => 'boolean'
            ]);

            $gestion = \App\Models\ConfiguracionAcademica\GestionAcademica::findOrFail($id);
            $gestion->update($data);
            return redirect()->route('configuracion-academica.gestiones.index')
                             ->with('success', 'Gestión actualizada exitosamente');
        })->name('gestiones.update');

        Route::delete('gestiones/{id}', function($id) {
            \App\Models\ConfiguracionAcademica\GestionAcademica::destroy($id);
            return redirect()->route('configuracion-academica.gestiones.index')
                             ->with('success', 'Gestión eliminada');
        })->name('gestiones.destroy');

        // Docentes (rutas web mínimas)
        Route::get('docentes', function() {
            $docentes = \App\Models\ConfiguracionAcademica\Docente::with('facultad')->orderBy('nombre')->paginate(10);
            return view('configuracion-academica.docentes.index', compact('docentes'));
        })->name('docentes.index');

        Route::get('docentes/create', function() {
            $facultades = \App\Models\ConfiguracionAcademica\Facultad::all();
            return view('configuracion-academica.docentes.create', compact('facultades'));
        })->name('docentes.create');

        Route::post('docentes', function(\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'ci' => 'required|string|max:20|unique:docente,ci',
                'nombre' => 'required|string|max:100',
                'correo' => 'required|email|unique:docente,correo',
                'telefono' => 'nullable|string|max:20',
                'sexo' => 'nullable|in:M,F',
                'id_facultad' => 'required|exists:facultad,id_facultad',
                'estado' => 'boolean'
            ]);

            \App\Models\ConfiguracionAcademica\Docente::create($data);
            return redirect()->route('configuracion-academica.docentes.index')
                             ->with('success', 'Docente creado exitosamente');
        })->name('docentes.store');

        Route::get('docentes/{id}/edit', function($id) {
            $docente = \App\Models\ConfiguracionAcademica\Docente::findOrFail($id);
            $facultades = \App\Models\ConfiguracionAcademica\Facultad::all();
            return view('configuracion-academica.docentes.edit', compact('docente','facultades'));
        })->name('docentes.edit');

        Route::put('docentes/{id}', function(\Illuminate\Http\Request $request, $id) {
            $data = $request->validate([
                'ci' => 'required|string|max:20|unique:docente,ci,' . $id . ',id_docente',
                'nombre' => 'required|string|max:100',
                'correo' => 'required|email|unique:docente,correo,' . $id . ',id_docente',
                'telefono' => 'nullable|string|max:20',
                'sexo' => 'nullable|in:M,F',
                'id_facultad' => 'required|exists:facultad,id_facultad',
                'estado' => 'boolean'
            ]);

            $docente = \App\Models\ConfiguracionAcademica\Docente::findOrFail($id);
            $docente->update($data);
            return redirect()->route('configuracion-academica.docentes.index')
                             ->with('success', 'Docente actualizado exitosamente');
        })->name('docentes.update');

        Route::delete('docentes/{id}', function($id) {
            \App\Models\ConfiguracionAcademica\Docente::destroy($id);
            return redirect()->route('configuracion-academica.docentes.index')
                             ->with('success', 'Docente eliminado');
        })->name('docentes.destroy');

        // Materias (rutas web mínimas)
        Route::get('materias', function() {
            $materias = \App\Models\ConfiguracionAcademica\Materia::with('facultad')->paginate(10);
            $facultades = \App\Models\ConfiguracionAcademica\Facultad::all();
            return view('configuracion-academica.materias.index', compact('materias','facultades'));
        })->name('materias.index');

        Route::get('materias/create', function() {
            $facultades = \App\Models\ConfiguracionAcademica\Facultad::all();
            return view('configuracion-academica.materias.create', compact('facultades'));
        })->name('materias.create');

        Route::post('materias', function(\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'nombre' => 'required',
                'codigo' => 'required|unique:materia',
                'carga_horaria' => 'nullable|numeric|min:1',
                'id_facultad' => 'nullable|exists:facultad,id_facultad'
            ]);

            \App\Models\ConfiguracionAcademica\Materia::create($data);
            return redirect()->route('configuracion-academica.materias.index')
                             ->with('success', 'Materia creada exitosamente');
        })->name('materias.store');

        Route::get('materias/{id}/edit', function($id) {
            $materia = \App\Models\ConfiguracionAcademica\Materia::findOrFail($id);
            $facultades = \App\Models\ConfiguracionAcademica\Facultad::all();
            return view('configuracion-academica.materias.edit', compact('materia','facultades'));
        })->name('materias.edit');

        Route::put('materias/{id}', function(\Illuminate\Http\Request $request, $id) {
            $data = $request->validate([
                'nombre' => 'required',
                'codigo' => 'required',
                'carga_horaria' => 'nullable|numeric|min:1',
                'id_facultad' => 'nullable|exists:facultad,id_facultad'
            ]);

            $materia = \App\Models\ConfiguracionAcademica\Materia::findOrFail($id);
            $materia->update($data);
            return redirect()->route('configuracion-academica.materias.index')
                             ->with('success', 'Materia actualizada exitosamente');
        })->name('materias.update');

        Route::delete('materias/{id}', function($id) {
            \App\Models\ConfiguracionAcademica\Materia::destroy($id);
            return redirect()->route('configuracion-academica.materias.index')
                             ->with('success', 'Materia eliminada');
        })->name('materias.destroy');

        // Grupos (rutas web mínimas)
        Route::get('grupos', function() {
            $grupos = \App\Models\ConfiguracionAcademica\Grupo::with(['materia','docente','gestion'])->paginate(10);
            $materias = \App\Models\ConfiguracionAcademica\Materia::all();
            $docentes = \App\Models\ConfiguracionAcademica\Docente::orderBy('nombre')->get();
            $gestiones = \App\Models\ConfiguracionAcademica\GestionAcademica::orderBy('anio','desc')->get();
            return view('configuracion-academica.grupos.index', compact('grupos','materias','docentes','gestiones'));
        })->name('grupos.index');

        Route::get('grupos/create', function() {
            $materias = \App\Models\ConfiguracionAcademica\Materia::all();
            return view('configuracion-academica.grupos.create', compact('materias'));
        })->name('grupos.create');

        Route::post('grupos', function(\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'nombre' => 'required',
                'id_materia' => 'required|exists:materia,id_materia'
            ]);

            \App\Models\ConfiguracionAcademica\Grupo::create($data);
            return redirect()->route('configuracion-academica.grupos.index')
                             ->with('success', 'Grupo creado exitosamente');
        })->name('grupos.store');

        Route::get('grupos/{id}/edit', function($id) {
            $grupo = \App\Models\ConfiguracionAcademica\Grupo::findOrFail($id);
            $materias = \App\Models\ConfiguracionAcademica\Materia::all();
            return view('configuracion-academica.grupos.edit', compact('grupo','materias'));
        })->name('grupos.edit');

        Route::put('grupos/{id}', function(\Illuminate\Http\Request $request, $id) {
            $data = $request->validate([
                'nombre' => 'required',
                'id_materia' => 'required|exists:materia,id_materia'
            ]);

            $grupo = \App\Models\ConfiguracionAcademica\Grupo::findOrFail($id);
            $grupo->update($data);
            return redirect()->route('configuracion-academica.grupos.index')
                             ->with('success', 'Grupo actualizado exitosamente');
        })->name('grupos.update');

        Route::delete('grupos/{id}', function($id) {
            \App\Models\ConfiguracionAcademica\Grupo::destroy($id);
            return redirect()->route('configuracion-academica.grupos.index')
                             ->with('success', 'Grupo eliminado');
        })->name('grupos.destroy');

        // Aulas (rutas web mínimas)
        Route::get('aulas', function() {
            $aulas = \App\Models\ConfiguracionAcademica\Aula::paginate(10);
            $facultades = \App\Models\ConfiguracionAcademica\Facultad::all();
            return view('configuracion-academica.aulas.index', compact('aulas','facultades'));
        })->name('aulas.index');

        Route::get('aulas/create', function() {
            return view('configuracion-academica.aulas.create');
        })->name('aulas.create');

        Route::post('aulas', function(\Illuminate\Http\Request $request) {
            $data = $request->validate(['codigo' => 'required|unique:aula']);
            \App\Models\ConfiguracionAcademica\Aula::create($data);
            return redirect()->route('configuracion-academica.aulas.index')
                             ->with('success', 'Aula creada exitosamente');
        })->name('aulas.store');

        Route::get('aulas/{id}/edit', function($id) {
            $aula = \App\Models\ConfiguracionAcademica\Aula::findOrFail($id);
            return view('configuracion-academica.aulas.edit', compact('aula'));
        })->name('aulas.edit');

        Route::put('aulas/{id}', function(\Illuminate\Http\Request $request, $id) {
            $data = $request->validate(['codigo' => 'required']);
            $aula = \App\Models\ConfiguracionAcademica\Aula::findOrFail($id);
            $aula->update($data);
            return redirect()->route('configuracion-academica.aulas.index')
                             ->with('success', 'Aula actualizada exitosamente');
        })->name('aulas.update');

        Route::delete('aulas/{id}', function($id) {
            \App\Models\ConfiguracionAcademica\Aula::destroy($id);
            return redirect()->route('configuracion-academica.aulas.index')
                             ->with('success', 'Aula eliminada');
        })->name('aulas.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | PAQUETE: PLANIFICACIÓN
    |--------------------------------------------------------------------------
    */
    Route::prefix('planificacion')->name('planificacion.')->group(function () {
        // Horarios (rutas web mínimas)
        Route::get('horarios', function() {
            $horarios = \App\Models\Planificacion\Horario::with(['grupo.materia','grupo.docente','aula'])->orderBy('dia_semana')->paginate(15);
            $gestiones = \App\Models\ConfiguracionAcademica\GestionAcademica::all();
            return view('planificacion.horarios.index', compact('horarios', 'gestiones'));
        })->name('horarios.index');

        // Distribución de horarios multi-día
        Route::get('distribucion', function(Request $request) {
            $grupos = \App\Models\ConfiguracionAcademica\Grupo::with(['materia', 'docente'])
                                                              ->where('id_gestion', $request->id_gestion ?? \App\Models\ConfiguracionAcademica\GestionAcademica::where('estado', true)->first()->id_gestion ?? 0)
                                                              ->whereDoesntHave('horarios')
                                                              ->get();
            $patrones = [];
            return view('planificacion.distribucion-horarios', compact('grupos', 'patrones'));
        })->name('distribucion.formulario');

        Route::post('distribucion/generar', [\App\Http\Controllers\Planificacion\DistribucionHorariosController::class, 'generar'])
            ->name('distribucion.generar');

        Route::get('distribucion/patrones', [\App\Http\Controllers\Planificacion\DistribucionHorariosController::class, 'obtenerPatrones'])
            ->name('distribucion.patrones');

        Route::get('horarios/create', function() {
            $grupos = \App\Models\ConfiguracionAcademica\Grupo::with('materia')->get();
            $aulas = \App\Models\ConfiguracionAcademica\Aula::all();
            return view('planificacion.horarios.create', compact('grupos','aulas'));
        })->name('horarios.create');

        Route::post('horarios', function(\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'id_grupo' => 'required|exists:grupo,id_grupo',
                'id_aula' => 'required|exists:aula,id_aula',
                'dia_semana' => 'required|string',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'tipo_asignacion' => 'nullable|in:Manual,Automática'
            ]);

            \App\Models\Planificacion\Horario::create($data);
            return redirect()->route('planificacion.horarios.index')
                             ->with('success', 'Horario creado exitosamente');
        })->name('horarios.store');

        Route::get('horarios/{id}/edit', function($id) {
            $horario = \App\Models\Planificacion\Horario::findOrFail($id);
            $grupos = \App\Models\ConfiguracionAcademica\Grupo::with('materia')->get();
            $aulas = \App\Models\ConfiguracionAcademica\Aula::all();
            return view('planificacion.horarios.create', compact('horario','grupos','aulas'));
        })->name('horarios.edit');

        Route::put('horarios/{id}', function(\Illuminate\Http\Request $request, $id) {
            $data = $request->validate([
                'id_grupo' => 'required|exists:grupo,id_grupo',
                'id_aula' => 'required|exists:aula,id_aula',
                'dia_semana' => 'required|string',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'tipo_asignacion' => 'nullable|in:Manual,Automática'
            ]);

            $horario = \App\Models\Planificacion\Horario::findOrFail($id);
            $horario->update($data);
            return redirect()->route('planificacion.horarios.index')
                             ->with('success', 'Horario actualizado exitosamente');
        })->name('horarios.update');

        Route::delete('horarios/{id}', function($id) {
            \App\Models\Planificacion\Horario::destroy($id);
            return redirect()->route('planificacion.horarios.index')
                             ->with('success', 'Horario eliminado');
        })->name('horarios.destroy');

        // Rutas adicionales para horarios si necesitas
        Route::get('horarios/grupo/{id}', function($id) {
            $horarios = \App\Models\Planificacion\Horario::where('id_grupo', $id)->with(['grupo.materia','aula'])->get();
            return response()->json(['horarios' => $horarios]);
        })->name('horarios.grupo');

        Route::get('horarios/docente/{id}', function($id) {
            $horarios = \App\Models\Planificacion\Horario::whereHas('grupo', function($q) use ($id) { $q->where('id_docente', $id); })->with(['grupo.materia','aula'])->get();
            return response()->json(['horarios' => $horarios]);
        })->name('horarios.docente');

        // ============================================
        // QR DE AULAS
        // ============================================
        Route::get('generador-qr', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'index'])->name('qr.index');
        Route::post('qr/validar', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'validar'])->name('qr.validar');
        Route::get('qr/{idAula}/mostrar', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'mostrar'])->name('qr.mostrar');
        Route::post('qr/{idAula}/generar', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'generar'])->name('qr.generar');
        Route::post('qr/{idAula}/regenerar', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'regenerar'])->name('qr.regenerar');
        Route::get('qr/listar', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'listar'])->name('qr.listar');
        Route::post('qr/generar-todos', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'generarTodos'])->name('qr.generar-todos');
        Route::post('qr/descargar-zip', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'descargarZip'])->name('qr.descargar-zip');
        Route::post('qr/descargar-zip-todos', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'descargarZipTodos'])->name('qr.descargar-zip-todos');
        Route::post('qr/regenerar-multiples', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'regenerarMultiples'])->name('qr.regenerar-multiples');
        Route::post('qr/regenerar-todos', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'regenerarTodos'])->name('qr.regenerar-todos');
        Route::post('qr/descargar-pdf', [\App\Http\Controllers\Planificacion\QrAulaController::class, 'descargarPdfImprimible'])->name('qr.descargar-pdf');
    });
    Route::middleware('auth')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | PAQUETE: CONTROL Y SEGUIMIENTO DOCENTE
    |--------------------------------------------------------------------------
    */
    Route::prefix('control-seguimiento')->name('control-seguimiento.')->group(function () {
        
        // Dashboard
        Route::get('/', function () {
            return view('control-seguimiento.index');
        })->name('index');
        
        // Asistencia
        Route::resource('asistencia', \App\Http\Controllers\ControlSeguimiento\AsistenciaController::class);
        Route::get('asistencia-estadisticas', [\App\Http\Controllers\ControlSeguimiento\AsistenciaController::class, 'estadisticas'])
            ->name('asistencia.estadisticas');
        Route::post('asistencia/horarios-docente', [\App\Http\Controllers\ControlSeguimiento\AsistenciaController::class, 'obtenerHorariosDocente'])
            ->name('asistencia.horarios-docente');
        Route::post('asistencia/materias-docente', [\App\Http\Controllers\ControlSeguimiento\AsistenciaController::class, 'obtenerMateriasDocente'])
            ->name('asistencia.materias-docente');
        
        // Consultas de Horarios y Asistencia (CU09)
        Route::prefix('consultas')->name('consultas.')->group(function () {
            Route::get('dashboard', [\App\Http\Controllers\ControlSeguimiento\ConsultaHorarioController::class, 'dashboard'])
                ->name('dashboard');
            Route::post('horarios-docente', [\App\Http\Controllers\ControlSeguimiento\ConsultaHorarioController::class, 'horariosDocente'])
                ->name('horarios-docente');
            Route::post('horarios-grupo', [\App\Http\Controllers\ControlSeguimiento\ConsultaHorarioController::class, 'horariosGrupo'])
                ->name('horarios-grupo');
            Route::post('asistencia-docente', [\App\Http\Controllers\ControlSeguimiento\ConsultaHorarioController::class, 'asistenciaDocente'])
                ->name('asistencia-docente');
            Route::post('horarios-hoy', [\App\Http\Controllers\ControlSeguimiento\ConsultaHorarioController::class, 'misHorariosHoy'])
                ->name('horarios-hoy');
            Route::get('calendario', [\App\Http\Controllers\ControlSeguimiento\ConsultaHorarioController::class, 'calendarioAsistencia'])
                ->name('calendario');
            Route::post('resumen-semanal', [\App\Http\Controllers\ControlSeguimiento\ConsultaHorarioController::class, 'resumenSemanal'])
                ->name('resumen-semanal');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | PAQUETE: REPORTES Y DATOS
    |--------------------------------------------------------------------------
    */
    Route::prefix('reporte-datos')->name('reporte-datos.')->group(function () {
        
        // Reportes
        Route::get('reportes', [\App\Http\Controllers\ReporteDatos\ReporteController::class, 'index'])
            ->name('reportes.index');
        Route::post('reportes/horarios-pdf', [\App\Http\Controllers\ReporteDatos\ReporteController::class, 'horariosPDF'])
            ->name('reportes.horarios-pdf');
        Route::post('reportes/asistencia-pdf', [\App\Http\Controllers\ReporteDatos\ReporteController::class, 'asistenciaPDF'])
            ->name('reportes.asistencia-pdf');
        Route::post('reportes/asistencia-periodo', [\App\Http\Controllers\ReporteDatos\ReporteController::class, 'asistenciaPorPeriodo'])
            ->name('reportes.asistencia-periodo');
        Route::post('reportes/asistencia-asignacion', [\App\Http\Controllers\ReporteDatos\ReporteController::class, 'asistenciaPorAsignacion'])
            ->name('reportes.asistencia-asignacion');
        Route::post('reportes/carga-horaria', [\App\Http\Controllers\ReporteDatos\ReporteController::class, 'cargaHoraria'])
            ->name('reportes.carga-horaria');
        
        // Importación
        Route::resource('importacion', \App\Http\Controllers\ReporteDatos\ImportacionController::class)->only(['index', 'create']);
        Route::post('importacion/docentes', [\App\Http\Controllers\ReporteDatos\ImportacionController::class, 'importarDocentes'])
            ->name('importacion.docentes');
        Route::post('importacion/materias', [\App\Http\Controllers\ReporteDatos\ImportacionController::class, 'importarMaterias'])
            ->name('importacion.materias');
    });
});



// ============================================
// EJEMPLO DE MIDDLEWARE PERSONALIZADO (OPCIONAL)
// para restringir por roles
// ============================================
/*
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('administracion/usuarios', UserController::class);
});

Route::middleware(['auth', 'role:coordinador'])->group(function () {
    Route::resource('configuracion-academica/gestiones', GestionController::class);
});
*/
});