@extends('layouts.app')
@section('page-title', 'Control de Seguimiento')

@section('content')
<div class="row">
    <!-- Bienvenida -->
    <div class="col-12 mb-4">
        <div class="card bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body">
                <h4 class="card-title mb-0">
                    <i class="bi bi-clipboard-check me-2"></i>Módulo de Control de Seguimiento
                </h4>
                <p class="card-text mt-2 mb-0">Gestiona la asistencia docente, consulta horarios y análisis de datos</p>
            </div>
        </div>
    </div>

    <!-- Opciones Principales -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle fs-1 text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title">Registrar Asistencia</h5>
                        <p class="card-text text-muted">
                            Registra tu asistencia con validación de ubicación GPS, captura de foto y detección automática de atrasos.
                        </p>
                        <a href="{{ route('control-seguimiento.asistencia.create') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Nuevo Registro
                        </a>
                        <a href="{{ route('control-seguimiento.asistencia.index') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-list me-1"></i>Ver Todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="bi bi-bar-chart-fill fs-1 text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title">Estadísticas</h5>
                        <p class="card-text text-muted">
                            Visualiza gráficos y estadísticas de asistencia por período, docente o estado. Análisis detallado de puntualidad.
                        </p>
                        <a href="{{ route('control-seguimiento.asistencia.estadisticas') }}" class="btn btn-info btn-sm text-white">
                            <i class="bi bi-bar-chart me-1"></i>Ver Estadísticas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center shadow-sm border-0">
                    <div class="card-body">
                        <div class="display-6 text-success mb-2">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h6 class="card-title">Validación GPS</h6>
                        <p class="card-text small text-muted">
                            Verifica que el docente se encuentre en el aula (radio: 50 metros)
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center shadow-sm border-0">
                    <div class="card-body">
                        <div class="display-6 text-primary mb-2">
                            <i class="bi bi-camera-fill"></i>
                        </div>
                        <h6 class="card-title">Captura de Foto</h6>
                        <p class="card-text small text-muted">
                            Registra una foto opcional para mayor control y auditoría
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center shadow-sm border-0">
                    <div class="card-body">
                        <div class="display-6 text-warning mb-2">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <h6 class="card-title">Detección Automática</h6>
                        <p class="card-text small text-muted">
                            Sistema automático de detección de atrasos (>10 minutos)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requisitos del Sistema -->
    <div class="col-12">
        <div class="alert alert-info border-start border-4" role="alert">
            <strong><i class="bi bi-info-circle me-2"></i>Requisitos para usar el módulo:</strong>
            <ul class="mb-0 mt-2">
                <li><strong>GPS/Ubicación:</strong> Navegador web con soporte de Geolocalización HTML5</li>
                <li><strong>Cámara:</strong> Para captura de fotos (opcional pero recomendado)</li>
                <li><strong>Navegador moderno:</strong> Chrome, Firefox, Safari o Edge (últimas versiones)</li>
                <li><strong>Conexión a internet:</strong> Para sincronización de datos</li>
            </ul>
        </div>
    </div>
</div>
@endsection
