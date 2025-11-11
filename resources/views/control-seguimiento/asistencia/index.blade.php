@extends('layouts.app')
@section('page-title', 'Registro de Asistencia')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Registro de Asistencia Docente</h5>
        <div class="btn-group" role="group">
            <a href="{{ route('control-seguimiento.asistencia.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Registrar Asistencia
            </a>
            <a href="{{ route('control-seguimiento.asistencia.estadisticas') }}" class="btn btn-info text-white">
                <i class="bi bi-bar-chart me-1"></i> Estadísticas
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Alertas -->
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Errores:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filtros -->
        <form method="GET" class="row g-3 mb-4" id="filtrosForm">
            <div class="col-md-3">
                <label class="form-label fw-medium">Docente</label>
                <select name="id_docente" id="filter_docente" class="form-select">
                    <option value="">Todos los docentes</option>
                    @foreach($docentes as $docente)
                        <option value="{{ $docente->id_docente }}" {{ request('id_docente') == $docente->id_docente ? 'selected' : '' }}>
                            {{ $docente->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium">Materia (Filtrado por Docente)</label>
                <select name="id_materia" id="filter_materia" class="form-select">
                    <option value="">-- Seleccionar docente primero --</option>
                </select>
                <small class="text-muted d-block mt-1">Se activa al seleccionar un docente</small>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-medium">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-medium">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="Presente" {{ request('estado') == 'Presente' ? 'selected' : '' }}>
                        <i class="bi bi-check-circle text-success"></i> Presente
                    </option>
                    <option value="Atrasado" {{ request('estado') == 'Atrasado' ? 'selected' : '' }}>
                        <i class="bi bi-clock text-warning"></i> Atrasado
                    </option>
                    <option value="Ausente" {{ request('estado') == 'Ausente' ? 'selected' : '' }}>
                        <i class="bi bi-x-circle text-danger"></i> Ausente
                    </option>
                    <option value="Fuera de aula" {{ request('estado') == 'Fuera de aula' ? 'selected' : '' }}>
                        <i class="bi bi-geo-alt text-secondary"></i> Fuera de aula
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-medium">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Buscar
                </button>
            </div>
            @if(request()->anyFilled(['id_docente', 'fecha', 'estado', 'id_materia']))
                <div class="col-md-2">
                    <label class="form-label fw-medium">&nbsp;</label>
                    <a href="{{ route('control-seguimiento.asistencia.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i> Limpiar
                    </a>
                </div>
            @endif
        </form>

        <!-- Tabla Responsiva -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th style="width: 100px;">Fecha</th>
                        <th style="width: 80px;">Hora</th>
                        <th>Docente</th>
                        <th>Materia</th>
                        <th style="width: 80px;">Grupo</th>
                        <th style="width: 80px;">Aula</th>
                        <th style="width: 120px;">Estado</th>
                        <th style="width: 60px;" class="text-center">GPS</th>
                        <th style="width: 60px;" class="text-center">Foto</th>
                        <th style="width: 80px;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asistencias as $asistencia)
                    <tr>
                        <td>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            <i class="bi bi-clock text-info"></i>
                            <small>{{ substr($asistencia->hora_marcado, 0, 5) }}</small>
                        </td>
                        <td>
                            <strong>{{ $asistencia->docente->nombre }}</strong>
                        </td>
                        <td>{{ $asistencia->horario->grupo->materia->nombre ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-info text-dark">{{ $asistencia->horario->grupo->nombre ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $asistencia->horario->aula->nro ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @php
                                $statusConfig = [
                                    'Presente' => ['badge' => 'bg-success', 'icon' => 'check-circle'],
                                    'Atrasado' => ['badge' => 'bg-warning', 'icon' => 'clock'],
                                    'Ausente' => ['badge' => 'bg-danger', 'icon' => 'x-circle'],
                                    'Fuera de aula' => ['badge' => 'bg-secondary', 'icon' => 'geo-alt'],
                                ];
                                $config = $statusConfig[$asistencia->estado] ?? ['badge' => 'bg-secondary', 'icon' => 'question-circle'];
                            @endphp
                            <span class="badge {{ $config['badge'] }}">
                                <i class="bi bi-{{ $config['icon'] }} me-1"></i>{{ $asistencia->estado }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($asistencia->latitud && $asistencia->longitud)
                                <a href="https://www.google.com/maps?q={{ $asistencia->latitud }},{{ $asistencia->longitud }}" 
                                   target="_blank" class="btn btn-sm btn-outline-primary" title="Ver en Maps">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </a>
                            @else
                                <small class="text-muted" title="Sin GPS">—</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($asistencia->foto)
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        data-bs-toggle="modal" data-bs-target="#fotoModal{{ $asistencia->id_asistencia }}" 
                                        title="Ver foto">
                                    <i class="bi bi-image"></i>
                                </button>
                            @else
                                <small class="text-muted" title="Sin foto">—</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <form action="{{ route('control-seguimiento.asistencia.destroy', $asistencia->id_asistencia) }}" 
                                  method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Eliminar este registro de asistencia?')"
                                        title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal para mostrar foto -->
                    @if($asistencia->foto)
                    <div class="modal fade" id="fotoModal{{ $asistencia->id_asistencia }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Foto - {{ $asistencia->docente->nombre }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img src="{{ asset('storage/' . $asistencia->foto) }}" alt="Foto" style="max-width: 100%; max-height: 500px; border-radius: 0.25rem;">
                                    <div class="mt-3 small text-muted">
                                        <i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <p class="text-muted mb-0">No hay registros de asistencia que coincidan con los filtros</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                Mostrando <strong>{{ $asistencias->count() }}</strong> de <strong>{{ $asistencias->total() }}</strong> registros
            </div>
            {{ $asistencias->links() }}
        </div>
    </div>
</div>

<script>
// Filtro inteligente de materias por docente
document.addEventListener('DOMContentLoaded', function() {
    const filterDocente = document.getElementById('filter_docente');
    const filterMateria = document.getElementById('filter_materia');

    // Inicializar si ya hay un docente seleccionado
    if (filterDocente.value) {
        cargarMateriasDocente(filterDocente.value);
    }

    // Cargar materias cuando cambia el docente
    filterDocente.addEventListener('change', function() {
        if (this.value) {
            cargarMateriasDocente(this.value);
        } else {
            // Limpiar materia
            filterMateria.innerHTML = '<option value="">-- Seleccionar docente primero --</option>';
            filterMateria.disabled = true;
        }
    });

    function cargarMateriasDocente(idDocente) {
        // Mostrar cargando
        filterMateria.innerHTML = '<option value="" disabled>Cargando...</option>';
        filterMateria.disabled = true;

        fetch('{{ route("control-seguimiento.asistencia.materias-docente") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id_docente: idDocente
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.materias && data.materias.length > 0) {
                // Construir opciones
                let html = '<option value="">Todas las materias</option>';
                data.materias.forEach(function(materia) {
                    html += `<option value="${materia.id_materia}">${materia.label}</option>`;
                });
                filterMateria.innerHTML = html;
                filterMateria.disabled = false;
            } else {
                filterMateria.innerHTML = '<option value="">No hay materias asignadas</option>';
                filterMateria.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            filterMateria.innerHTML = '<option value="">Error al cargar materias</option>';
            filterMateria.disabled = true;
        });
    }
});
</script>
@endsection