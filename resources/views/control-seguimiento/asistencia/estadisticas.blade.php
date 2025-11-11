@extends('layouts.app')
@section('page-title', 'Estadísticas de Asistencia')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Estadísticas de Asistencia Docente</h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <div class="row g-3 mb-4 p-3 bg-light rounded">
            <div class="col-md-4">
                <label class="form-label fw-medium">Docente</label>
                <select name="id_docente" class="form-select" onchange="actualizarFiltros()">
                    <option value="">Todos los docentes</option>
                    @foreach($docentes as $docente)
                        <option value="{{ $docente->id_docente }}" 
                                {{ request('id_docente') == $docente->id_docente ? 'selected' : '' }}>
                            {{ $docente->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium">Mes</label>
                <input type="month" name="mes" class="form-control" id="mes" value="{{ $mes }}" onchange="actualizarFiltros()">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-medium">&nbsp;</label>
                <a href="{{ route('control-seguimiento.asistencia.estadisticas') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-clockwise me-1"></i> Limpiar
                </a>
            </div>
        </div>

        <!-- Estadísticas Principales en 2 Filas -->
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center bg-primary-subtle">
                        <i class="bi bi-calendar-check fs-2 text-primary mb-2 d-block"></i>
                        <h3 class="mb-0 text-primary fw-bold">{{ $estadisticas['total'] }}</h3>
                        <p class="mb-0 text-muted">Total de Registros</p>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($mes)->locale('es')->monthName }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center bg-success-subtle">
                        <i class="bi bi-check-circle fs-2 text-success mb-2 d-block"></i>
                        <h3 class="mb-0 text-success fw-bold">{{ $estadisticas['presentes'] }}</h3>
                        <p class="mb-0 text-muted">Presentes</p>
                        <small class="text-success">{{ $estadisticas['total'] > 0 ? round(($estadisticas['presentes'] / $estadisticas['total']) * 100) : 0 }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center bg-warning-subtle">
                        <i class="bi bi-clock-history fs-2 text-warning mb-2 d-block"></i>
                        <h3 class="mb-0 text-warning fw-bold">{{ $estadisticas['atrasados'] }}</h3>
                        <p class="mb-0 text-muted">Atrasados</p>
                        <small class="text-warning">{{ $estadisticas['total'] > 0 ? round(($estadisticas['atrasados'] / $estadisticas['total']) * 100) : 0 }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center bg-danger-subtle">
                        <i class="bi bi-x-circle fs-2 text-danger mb-2 d-block"></i>
                        <h3 class="mb-0 text-danger fw-bold">{{ $estadisticas['ausentes'] }}</h3>
                        <p class="mb-0 text-muted">Ausentes</p>
                        <small class="text-danger">{{ $estadisticas['total'] > 0 ? round(($estadisticas['ausentes'] / $estadisticas['total']) * 100) : 0 }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fila adicional para "Fuera de Aula" -->
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center bg-secondary-subtle">
                        <i class="bi bi-geo-alt fs-2 text-secondary mb-2 d-block"></i>
                        <h3 class="mb-0 text-secondary fw-bold">{{ $estadisticas['fuera_aula'] }}</h3>
                        <p class="mb-0 text-muted">Fuera de Aula</p>
                        <small class="text-secondary">{{ $estadisticas['total'] > 0 ? round(($estadisticas['fuera_aula'] / $estadisticas['total']) * 100) : 0 }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Gráficos -->
        <div class="row">
            <!-- Gráfico de Barras -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <h6 class="mb-0"><i class="bi bi-bar-chart-fill me-2"></i>Distribución por Estado</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chartBarras" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico Circular (Pie) -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <h6 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Proporción de Asistencia</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <div style="max-width: 300px; width: 100%;">
                            <canvas id="chartPie"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Información -->
        <div class="alert alert-info border-start border-4" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Resumen del período:</strong>
            <ul class="mb-0 mt-2">
                <li>Porcentaje de puntualidad: <strong>{{ $estadisticas['total'] > 0 ? round((($estadisticas['presentes'] + $estadisticas['atrasados']) / $estadisticas['total']) * 100) : 0 }}%</strong></li>
                <li>Docentes registrados: <strong>{{ count($docentes) }}</strong></li>
                <li>Período analizado: <strong>{{ \Carbon\Carbon::parse($mes)->locale('es')->monthName }} de {{ \Carbon\Carbon::parse($mes)->year }}</strong></li>
            </ul>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Colores coherentes
const colores = {
    presente: 'rgba(40, 167, 69, 0.8)',
    atrasado: 'rgba(255, 193, 7, 0.8)',
    ausente: 'rgba(220, 53, 69, 0.8)',
    fuera: 'rgba(108, 117, 125, 0.8)'
};

// Gráfico de Barras
const ctxBarras = document.getElementById('chartBarras');
new Chart(ctxBarras, {
    type: 'bar',
    data: {
        labels: ['Presentes', 'Atrasados', 'Ausentes', 'Fuera de Aula'],
        datasets: [{
            label: 'Cantidad',
            data: [
                {{ $estadisticas['presentes'] }},
                {{ $estadisticas['atrasados'] }},
                {{ $estadisticas['ausentes'] }},
                {{ $estadisticas['fuera_aula'] }}
            ],
            backgroundColor: [
                colores.presente,
                colores.atrasado,
                colores.ausente,
                colores.fuera
            ],
            borderColor: [
                'rgb(40, 167, 69)',
                'rgb(255, 193, 7)',
                'rgb(220, 53, 69)',
                'rgb(108, 117, 125)'
            ],
            borderWidth: 2,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Gráfico Circular (Pie)
const ctxPie = document.getElementById('chartPie');
new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: ['Presentes', 'Atrasados', 'Ausentes', 'Fuera de Aula'],
        datasets: [{
            data: [
                {{ $estadisticas['presentes'] }},
                {{ $estadisticas['atrasados'] }},
                {{ $estadisticas['ausentes'] }},
                {{ $estadisticas['fuera_aula'] }}
            ],
            backgroundColor: [
                colores.presente,
                colores.atrasado,
                colores.ausente,
                colores.fuera
            ],
            borderColor: [
                'rgb(40, 167, 69)',
                'rgb(255, 193, 7)',
                'rgb(220, 53, 69)',
                'rgb(108, 117, 125)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function actualizarFiltros() {
    const idDocente = document.querySelector('select[name="id_docente"]').value;
    const mes = document.getElementById('mes').value;
    
    const params = new URLSearchParams();
    if (idDocente) params.append('id_docente', idDocente);
    if (mes) params.append('mes', mes);
    
    window.location.href = '{{ route("control-seguimiento.asistencia.estadisticas") }}' + (params.toString() ? '?' + params.toString() : '');
}
</script>
@endsection