@extends('layouts.app')
@section('page-title', 'Gestión de Aulas')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-door-open me-2"></i>Lista de Aulas</h5>
        <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalAula">
            <i class="bi bi-plus-circle me-1"></i> Nueva Aula
        </button>
    </div>
    <div class="card-body">
        <div class="row">
            @forelse($aulas as $aula)
            <div class="col-md-4 mb-4">
                <div class="card border-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="mb-0">
                                    <i class="bi bi-door-closed text-info me-2"></i>
                                    Aula {{ $aula->nro }}
                                </h4>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-warning" 
                                        onclick="editarAula({{ json_encode($aula) }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('configuracion-academica.aulas.destroy', $aula->id_aula) }}" 
                                      method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Eliminar?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <i class="bi bi-building text-muted me-2"></i>
                            <strong>Piso:</strong> {{ $aula->piso ?? 'No especificado' }}
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-people text-muted me-2"></i>
                            <strong>Capacidad:</strong> 
                            <span class="badge bg-info">{{ $aula->capacidad ?? 'N/A' }} personas</span>
                        </div>
                        @if($aula->ubicacion_gps)
                        <div class="mb-2">
                            <i class="bi bi-geo-alt text-muted me-2"></i>
                            <small>{{ $aula->ubicacion_gps }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No hay aulas registradas</p>
                </div>
            </div>
            @endforelse
        </div>
        
        <div class="d-flex justify-content-center mt-3">
            {{ $aulas->links() }}
        </div>
    </div>
</div>

<!-- Modal Aula -->
<div class="modal fade" id="modalAula">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAula" method="POST" action="{{ route('configuracion-academica.aulas.store') }}">
                @csrf
                <input type="hidden" name="_method" id="methodAula" value="POST">
                <div class="modal-header bg-info text-white">
                    <h5 id="tituloAula">Nueva Aula</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Número Aula *</label>
                        <input type="text" class="form-control" name="nro" id="nro" 
                               placeholder="Ej: 101, A1, LAB-1..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Piso</label>
                        <input type="text" class="form-control" name="piso" id="piso" 
                               placeholder="Ej: PB, 1, 2...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Capacidad</label>
                        <input type="number" class="form-control" name="capacidad" id="capacidad" 
                               min="1" placeholder="Número de personas">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Ubicación GPS</label>
                        <input type="text" class="form-control" name="ubicacion_gps" id="ubicacion_gps" 
                               placeholder="Coordenadas o descripción">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Facultad</label>
                        <select class="form-select" name="id_facultad" id="aula_facultad">
                            <option value="">Sin asignar</option>
                            @foreach($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}">{{ $facultad->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white">
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
function editarAula(aula) {
    document.getElementById('formAula').action = '/configuracion-academica/aulas/' + aula.id_aula;
    document.getElementById('methodAula').value = 'PUT';
    document.getElementById('tituloAula').innerText = 'Editar Aula';
    document.getElementById('nro').value = aula.nro;
    document.getElementById('piso').value = aula.piso || '';
    document.getElementById('capacidad').value = aula.capacidad || '';
    document.getElementById('ubicacion_gps').value = aula.ubicacion_gps || '';
    document.getElementById('aula_facultad').value = aula.id_facultad || '';
    new bootstrap.Modal(document.getElementById('modalAula')).show();
}

document.getElementById('modalAula').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formAula').reset();
    document.getElementById('formAula').action = '{{ route("configuracion-academica.aulas.store") }}';
    document.getElementById('methodAula').value = 'POST';
    document.getElementById('tituloAula').innerText = 'Nueva Aula';
});
</script>
@endsection
