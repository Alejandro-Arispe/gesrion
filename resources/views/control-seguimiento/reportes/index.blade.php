@extends('layouts.app')
@section('page-title', 'Generar Reportes')

@section('content')
<div class="row">
    <!-- Reporte de Horarios -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-file-pdf me-2"></i>Reporte de Horarios (PDF)</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reporte-datos.reportes.horarios-pdf') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Docente</label>
                        <select name="id_docente" class="form-select">
                            <option value="">Todos los docentes</option>
                            @foreach($docentes as $docente)
                                <option value="{{ $docente->id_docente }}">{{ $docente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gestión</label>
                        <select name="id_gestion" class="form-select">
                            <option value="">Todas las gestiones</option>
                            @foreach($gestiones as $gestion)
                                <option value="{{ $gestion->id_gestion }}">
                                    {{ $gestion->anio }}-{{ $gestion->semestre }}
                                </option>
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
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-file-excel me-2"></i>Reporte de Asistencia (Excel)</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reporte-datos.reportes.asistencia-excel') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Docente</label>
                        <select name="id_docente" class="form-select">
                            <option value="">Todos los docentes</option>
                            @foreach($docentes as $docente)
                                <option value="{{ $docente->id_docente }}">{{ $docente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-download me-1"></i> Descargar Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Reporte de Carga Horaria -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-file-pdf me-2"></i>Carga Horaria Docente (PDF)</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reporte-datos.reportes.carga-horaria') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Gestión</label>
                        <select name="id_gestion" class="form-select">
                            <option value="">Todas las gestiones</option>
                            @foreach($gestiones as $gestion)
                                <option value="{{ $gestion->id_gestion }}">
                                    {{ $gestion->anio }}-{{ $gestion->semestre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-info text-white w-100 mt-5">
                        <i class="bi bi-download me-1"></i> Descargar PDF
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection