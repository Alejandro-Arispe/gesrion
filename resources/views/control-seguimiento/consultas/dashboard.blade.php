@extends('layouts.app')
@section('page-title', 'Consultar Horarios y Asistencia')

@section('content')
<div class="row">
    <!-- Encabezado -->
    <div class="col-12 mb-4">
        <div class="card bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="bi bi-calendar2-week me-2"></i>Consultar Horarios y Asistencia
                        </h4>
                        <p class="card-text mt-2 mb-0">Visualiza tu horario semanal y estadísticas de asistencia</p>
                    </div>
                    <i class="bi bi-calendar-check display-6 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light border-0">
                <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros de Búsqueda</h6>
            </div>
            <div class="card-body">
                <form method="GET" id="filterForm" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Seleccionar Docente</label>
                        <select name="id_docente" class="form-select" id="idDocente" onchange="aplicarFiltros()">
                            <option value="">-- Seleccione un docente --</option>
                            @foreach($docentes as $docente)
                                <option value="{{ $docente->id_docente }}" 
                                        {{ request('id_docente') == $docente->id_docente ? 'selected' : '' }}>
                                    {{ $docente->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">O Seleccionar Grupo</label>
                        <select name="id_grupo" class="form-select" id="idGrupo" onchange="aplicarFiltros()">
                            <option value="">-- Seleccione un grupo --</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id_grupo }}"
                                        {{ request('id_grupo') == $grupo->id_grupo ? 'selected' : '' }}>
                                    {{ $grupo->materia->nombre }} - Grupo {{ $grupo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>
                        <a href="{{ route('control-seguimiento.consultas.dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sección Estadísticas (Solo si se selecciona docente) -->
    @if(request()->has('id_docente') && $estadisticas)
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-2">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body bg-primary-subtle">
                        <i class="bi bi-calendar-check fs-2 text-primary mb-2 d-block"></i>
                        <h3 class="mb-0 text-primary">{{ $estadisticas['total'] }}</h3>
                        <small class="text-muted">Registros (30d)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body bg-success-subtle">
                        <i class="bi bi-check-circle fs-2 text-success mb-2 d-block"></i>
                        <h3 class="mb-0 text-success">{{ $estadisticas['presentes'] }}</h3>
                        <small class="text-muted">Presentes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body bg-warning-subtle">
                        <i class="bi bi-clock fs-2 text-warning mb-2 d-block"></i>
                        <h3 class="mb-0 text-warning">{{ $estadisticas['atrasados'] }}</h3>
                        <small class="text-muted">Atrasados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body bg-danger-subtle">
                        <i class="bi bi-x-circle fs-2 text-danger mb-2 d-block"></i>
                        <h3 class="mb-0 text-danger">{{ $estadisticas['ausentes'] }}</h3>
                        <small class="text-muted">Ausentes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body bg-secondary-subtle">
                        <i class="bi bi-geo-alt fs-2 text-secondary mb-2 d-block"></i>
                        <h3 class="mb-0 text-secondary">{{ $estadisticas['fuera_aula'] }}</h3>
                        <small class="text-muted">Fuera Aula</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body bg-info-subtle">
                        <i class="bi bi-percent fs-2 text-info mb-2 d-block"></i>
                        <h3 class="mb-0 text-info">{{ $estadisticas['porcentaje_puntualidad'] }}%</h3>
                        <small class="text-muted">Puntualidad</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tabs para Diferentes Vistas -->
    @if(request()->has('id_docente') || request()->has('id_grupo'))
    <div class="col-12 mb-4">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-horarios" data-bs-toggle="tab" data-bs-target="#pane-horarios" type="button" role="tab">
                    <i class="bi bi-calendar2-event me-2"></i>Horarios
                </button>
            </li>
            @if(request()->has('id_docente'))
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-asistencia" data-bs-toggle="tab" data-bs-target="#pane-asistencia" type="button" role="tab">
                    <i class="bi bi-clipboard-check me-2"></i>Asistencia Reciente
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-hoy" data-bs-toggle="tab" data-bs-target="#pane-hoy" type="button" role="tab">
                    <i class="bi bi-calendar-day me-2"></i>Horarios de Hoy
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-semanal" data-bs-toggle="tab" data-bs-target="#pane-semanal" type="button" role="tab">
                    <i class="bi bi-calendar-week me-2"></i>Resumen Semanal
                </button>
            </li>
            @endif
        </ul>

        <div class="tab-content border border-top-0 p-4">
            <!-- Tab: Horarios -->
            <div class="tab-pane fade show active" id="pane-horarios" role="tabpanel">
                @if($horarios->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Día de Semana</th>
                                    <th>Hora</th>
                                    <th>Materia</th>
                                    <th>Grupo</th>
                                    <th>Aula</th>
                                    <th>Docente</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($horarios as $horario)
                                <tr>
                                    <td>
                                        <strong>{{ ucfirst($horario->dia_semana) }}</strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-clock-fill me-1 text-info"></i>
                                        {{ $horario->hora_inicio }} - {{ $horario->hora_fin }}
                                    </td>
                                    <td>{{ $horario->grupo->materia->nombre }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $horario->grupo->nombre }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $horario->aula->nro }}</span>
                                    </td>
                                    <td>{{ $horario->grupo->docente->nombre ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Activo
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>No hay horarios para mostrar
                    </div>
                @endif
            </div>

            <!-- Tab: Asistencia Reciente -->
            @if(request()->has('id_docente'))
            <div class="tab-pane fade" id="pane-asistencia" role="tabpanel">
                @if($asistenciasPorDia->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora Marcado</th>
                                    <th>Materia</th>
                                    <th>Grupo</th>
                                    <th>Aula</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asistenciasPorDia as $fecha => $asistencias)
                                    @foreach($asistencias as $asistencia)
                                    <tr>
                                        <td>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <i class="bi bi-clock me-1"></i>
                                            {{ substr($asistencia->hora_marcado, 0, 5) }}
                                        </td>
                                        <td>{{ $asistencia->horario->grupo->materia->nombre }}</td>
                                        <td>
                                            <span class="badge bg-info text-dark">{{ $asistencia->horario->grupo->nombre }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $asistencia->horario->aula->nro }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $config = [
                                                    'Presente' => ['badge' => 'bg-success', 'icon' => 'check-circle'],
                                                    'Atrasado' => ['badge' => 'bg-warning', 'icon' => 'clock'],
                                                    'Ausente' => ['badge' => 'bg-danger', 'icon' => 'x-circle'],
                                                    'Fuera de aula' => ['badge' => 'bg-secondary', 'icon' => 'geo-alt'],
                                                ];
                                                $cfg = $config[$asistencia->estado] ?? ['badge' => 'bg-secondary', 'icon' => 'question'];
                                            @endphp
                                            <span class="badge {{ $cfg['badge'] }}">
                                                <i class="bi bi-{{ $cfg['icon'] }} me-1"></i>{{ $asistencia->estado }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Sin registros de asistencia
                    </div>
                @endif
            </div>

            <!-- Tab: Horarios de Hoy (Carga via AJAX) -->
            <div class="tab-pane fade" id="pane-hoy" role="tabpanel">
                <div id="horariosHoy-content" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>

            <!-- Tab: Resumen Semanal (Carga via AJAX) -->
            <div class="tab-pane fade" id="pane-semanal" role="tabpanel">
                <div id="resumenSemanal-content" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
    <!-- Mensaje de bienvenida sin filtros -->
    <div class="col-12">
        <div class="alert alert-primary border-start border-4" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Bienvenido al módulo de consultas</strong>
            <p class="mb-0 mt-2">Selecciona un docente o grupo arriba para visualizar sus horarios y estadísticas de asistencia.</p>
        </div>
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
function aplicarFiltros() {
    document.getElementById('filterForm').submit();
}

// Cargar horarios de hoy cuando se activa el tab
document.getElementById('tab-hoy')?.addEventListener('shown.bs.tab', function () {
    const idDocente = document.getElementById('idDocente').value;
    if (idDocente) {
        cargarHorariosHoy(idDocente);
    }
});

// Cargar resumen semanal cuando se activa el tab
document.getElementById('tab-semanal')?.addEventListener('shown.bs.tab', function () {
    const idDocente = document.getElementById('idDocente').value;
    if (idDocente) {
        cargarResumenSemanal(idDocente);
    }
});

function cargarHorariosHoy(idDocente) {
    const container = document.getElementById('horariosHoy-content');
    
    fetch('{{ route("control-seguimiento.consultas.horarios-hoy") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ id_docente: idDocente })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarHorariosHoy(data.horarios, data.fecha, data.dia);
        }
    })
    .catch(error => console.error('Error:', error));
}

function mostrarHorariosHoy(horarios, fecha, dia) {
    const container = document.getElementById('horariosHoy-content');
    
    if (horarios.length === 0) {
        container.innerHTML = '<div class="alert alert-info">No hay horarios para hoy (' + dia + ')</div>';
        return;
    }

    let html = '<div class="alert alert-info mb-3">Horarios para hoy: <strong>' + fecha + ' (' + dia + ')</strong></div>';
    html += '<div class="table-responsive"><table class="table table-hover">';
    html += '<thead class="table-light"><tr><th>Hora</th><th>Materia</th><th>Grupo</th><th>Aula</th><th>Estado</th></tr></thead><tbody>';

    horarios.forEach(function(horario) {
        const estado = horario.registrado 
            ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Registrado</span>'
            : '<span class="badge bg-warning"><i class="bi bi-clock me-1"></i>Pendiente</span>';
        
        html += '<tr>';
        html += '<td>' + horario.hora_inicio + ' - ' + horario.hora_fin + '</td>';
        html += '<td>' + horario.grupo.materia.nombre + '</td>';
        html += '<td><span class="badge bg-info text-dark">' + horario.grupo.nombre + '</span></td>';
        html += '<td><span class="badge bg-light text-dark">' + horario.aula.nro + '</span></td>';
        html += '<td>' + estado + '</td>';
        html += '</tr>';
    });

    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function cargarResumenSemanal(idDocente) {
    const container = document.getElementById('resumenSemanal-content');
    
    fetch('{{ route("control-seguimiento.consultas.resumen-semanal") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ id_docente: idDocente })
    })
    .then(response => response.text())
    .then(html => {
        container.innerHTML = html;
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endsection
