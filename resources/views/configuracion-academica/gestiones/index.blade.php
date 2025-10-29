@extends('layouts.app')
@section('page-title', 'Gestiones Académicas')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Gestiones Académicas</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGestion">
            <i class="bi bi-plus-circle me-1"></i> Nueva Gestión
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Año</th>
                        <th>Semestre</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gestiones as $gestion)
                    <tr>
                        <td><strong>{{ $gestion->anio }}</strong></td>
                        <td>
                            <span class="badge bg-primary">Semestre {{ $gestion->semestre }}</span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($gestion->fecha_inicio)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($gestion->fecha_fin)->format('d/m/Y') }}</td>
                        <td>
                            @if($gestion->estado)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Activa</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Inactiva</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" 
                                    onclick="editarGestion({{ json_encode($gestion) }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('configuracion-academica.gestiones.destroy', $gestion->id_gestion) }}" 
                                  method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Eliminar esta gestión?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No hay gestiones registradas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $gestiones->links() }}
    </div>
</div>

<!-- Modal Gestión -->
<div class="modal fade" id="modalGestion">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formGestion" method="POST" action="{{ route('configuracion-academica.gestiones.store') }}">
                @csrf
                <input type="hidden" name="_method" id="methodGestion" value="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="tituloGestion">Nueva Gestión Académica</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Año *</label>
                            <input type="number" class="form-control" name="anio" id="anio" 
                                   value="{{ date('Y') }}" min="2020" max="2030" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Semestre *</label>
                            <select class="form-select" name="semestre" id="semestre" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Fecha Inicio *</label>
                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Fecha Fin *</label>
                            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-medium">Estado</label>
                            <select class="form-select" name="estado" id="estado">
                                <option value="1" selected>Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editarGestion(gestion) {
    document.getElementById('formGestion').action = '/configuracion-academica/gestiones/' + gestion.id_gestion;
    document.getElementById('methodGestion').value = 'PUT';
    document.getElementById('tituloGestion').innerText = 'Editar Gestión';
    document.getElementById('anio').value = gestion.anio;
    document.getElementById('semestre').value = gestion.semestre;
    document.getElementById('fecha_inicio').value = gestion.fecha_inicio;
    document.getElementById('fecha_fin').value = gestion.fecha_fin;
    document.getElementById('estado').value = gestion.estado ? '1' : '0';
    new bootstrap.Modal(document.getElementById('modalGestion')).show();
}

// Resetear modal al cerrar
document.getElementById('modalGestion').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formGestion').reset();
    document.getElementById('formGestion').action = '{{ route("configuracion-academica.gestiones.store") }}';
    document.getElementById('methodGestion').value = 'POST';
    document.getElementById('tituloGestion').innerText = 'Nueva Gestión Académica';
});
</script>
@endsection
