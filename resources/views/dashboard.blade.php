@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white p-4">
                <h2 class="mb-2">
                    <i class="bi bi-sun me-2"></i>
                    Bienvenido, {{ Auth::user()->username }}
                </h2>
                <p class="mb-0 opacity-75">Panel de control del sistema de gestión de horarios</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-3 mb-4">
        <div class="card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-people fs-1 text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Docentes</h6>
                        <h2 class="mb-0">{{ $stats['docentes'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-book fs-1 text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Materias</h6>
                        <h2 class="mb-0">{{ $stats['materias'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-door-open fs-1 text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Aulas</h6>
                        <h2 class="mb-0">{{ $stats['aulas'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-calendar-event fs-1 text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Horarios</h6>
                        <h2 class="mb-0">{{ $stats['horarios'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('configuracion-academica.docentes.create') }}" 
                           class="btn btn-outline-primary w-100 p-3">
                            <i class="bi bi-plus-circle fs-4 d-block mb-2"></i>
                            <span>Nuevo Docente</span>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('configuracion-academica.materias.create') }}" 
                           class="btn btn-outline-success w-100 p-3">
                            <i class="bi bi-book-half fs-4 d-block mb-2"></i>
                            <span>Nueva Materia</span>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('planificacion.horarios.create') }}" 
                           class="btn btn-outline-info w-100 p-3">
                            <i class="bi bi-calendar-plus fs-4 d-block mb-2"></i>
                            <span>Asignar Horario</span>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('administracion.usuarios.create') }}" 
                           class="btn btn-outline-warning w-100 p-3">
                            <i class="bi bi-person-plus fs-4 d-block mb-2"></i>
                            <span>Nuevo Usuario</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gestión Actual -->
    @if(isset($gestionActual))
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Gestión Académica Actual</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Año:</strong> {{ $gestionActual->anio }}
                    </div>
                    <div class="col-md-3">
                        <strong>Semestre:</strong> {{ $gestionActual->semestre }}
                    </div>
                    <div class="col-md-3">
                        <strong>Inicio:</strong> {{ $gestionActual->fecha_inicio }}
                    </div>
                    <div class="col-md-3">
                        <strong>Fin:</strong> {{ $gestionActual->fecha_fin }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection