@extends('layouts.app')
@section('page-title', 'Generador de Reportes')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3">
                <i class="bi bi-file-earmark-pdf me-2"></i>Generador de Reportes
            </h1>
        </div>
    </div>

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Reporte de Horarios -->
        <div class="col-md-6 mb-4">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar2-week me-2"></i>Reporte de Horarios</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Genera un PDF con los horarios de docentes según gestión académica.</p>
                    
                    <form action="{{ route('reporte-datos.reportes.horarios-pdf') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="horarios_gestion" class="form-label">Gestión Académica</label>
                            <select class="form-select" id="horarios_gestion" name="id_gestion">
                                <option value="">-- Todas las gestiones --</option>
                                @foreach($gestiones as $gestion)
                                    <option value="{{ $gestion->id_gestion }}">
                                        {{ $gestion->anio }} - Semestre {{ $gestion->semestre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="horarios_docente" class="form-label">Docente</label>
                            <select class="form-select" id="horarios_docente" name="id_docente">
                                <option value="">-- Todos los docentes --</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id_docente }}">{{ $docente->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-download me-1"></i> Descargar PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reporte de Asistencia -->
        <div class="col-md-6 mb-4">
            <div class="card border-success shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Reporte de Asistencia</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Genera un PDF con los registros de asistencia docente.</p>
                    
                    <form action="{{ route('reporte-datos.reportes.asistencia-pdf') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="asistencia_docente" class="form-label">Docente</label>
                            <select class="form-select" id="asistencia_docente" name="id_docente">
                                <option value="">-- Seleccione un docente --</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id_docente }}">{{ $docente->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="asistencia_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="asistencia_inicio" name="fecha_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="asistencia_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="asistencia_fin" name="fecha_fin">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-download me-1"></i> Descargar PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reporte de Carga Horaria -->
        <div class="col-md-6 mb-4">
            <div class="card border-info shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Reporte de Carga Horaria</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Genera un reporte con la carga horaria total de docentes en PDF o Excel.</p>
                    
                    <form action="{{ route('reporte-datos.reportes.carga-horaria') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="carga_gestion" class="form-label">Gestión Académica</label>
                            <select class="form-select" id="carga_gestion" name="id_gestion">
                                <option value="">-- Todas las gestiones --</option>
                                @foreach($gestiones as $gestion)
                                    <option value="{{ $gestion->id_gestion }}">
                                        {{ $gestion->anio }} - Semestre {{ $gestion->semestre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="carga_formato" class="form-label">Formato</label>
                            <select class="form-select" id="carga_formato" name="formato">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel (.xlsx)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-info text-white w-100">
                            <i class="bi bi-download me-1"></i> Descargar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reporte de Asistencia por Período -->
        <div class="col-md-6 mb-4">
            <div class="card border-warning shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Asistencia por Período</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Estadísticas detalladas de asistencia de un docente en un período específico.</p>
                    
                    <form action="{{ route('reporte-datos.reportes.asistencia-periodo') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="periodo_docente" class="form-label">Docente *</label>
                            <select class="form-select" id="periodo_docente" name="id_docente" required>
                                <option value="">-- Seleccione un docente --</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id_docente }}">{{ $docente->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="periodo_inicio" class="form-label">Fecha Inicio *</label>
                                <input type="date" class="form-control" id="periodo_inicio" name="fecha_inicio" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="periodo_fin" class="form-label">Fecha Fin *</label>
                                <input type="date" class="form-control" id="periodo_fin" name="fecha_fin" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-download me-1"></i> Descargar PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
