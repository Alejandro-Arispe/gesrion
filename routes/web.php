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
        
        // Roles (si necesitas gestionar roles)
        Route::resource('roles', RolController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        
        // Bitácora
        Route::get('bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
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
            $materias = \App\Models\ConfiguracionAcademica\Materia::with('gestion')->paginate(10);
            $facultades = \App\Models\ConfiguracionAcademica\Facultad::all();
            return view('configuracion-academica.materias.index', compact('materias','facultades'));
        })->name('materias.index');

        Route::get('materias/create', function() {
            $gestiones = \App\Models\ConfiguracionAcademica\GestionAcademica::all();
            return view('configuracion-academica.materias.create', compact('gestiones'));
        })->name('materias.create');

        Route::post('materias', function(\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'nombre' => 'required',
                'codigo' => 'required|unique:materia',
                'id_gestion' => 'required|exists:gestion_academica,id_gestion'
            ]);

            \App\Models\ConfiguracionAcademica\Materia::create($data);
            return redirect()->route('configuracion-academica.materias.index')
                             ->with('success', 'Materia creada exitosamente');
        })->name('materias.store');

        Route::get('materias/{id}/edit', function($id) {
            $materia = \App\Models\ConfiguracionAcademica\Materia::findOrFail($id);
            $gestiones = \App\Models\ConfiguracionAcademica\GestionAcademica::all();
            return view('configuracion-academica.materias.edit', compact('materia','gestiones'));
        })->name('materias.edit');

        Route::put('materias/{id}', function(\Illuminate\Http\Request $request, $id) {
            $data = $request->validate([
                'nombre' => 'required',
                'codigo' => 'required',
                'id_gestion' => 'required|exists:gestion_academica,id_gestion'
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
            return view('planificacion.horarios.index', compact('horarios'));
        })->name('horarios.index');

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
    });
});

// ============================================
// EJEMPLO DE MIDDLEWARE PERSONALIZADO (OPCIONAL)
// Si necesitas restringir por roles
// ============================================
/*
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('administracion/usuarios', UserController::class);
});

Route::middleware(['auth', 'role:coordinador'])->group(function () {
    Route::resource('configuracion-academica/gestiones', GestionController::class);
});
*/