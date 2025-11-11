@extends('layouts.app')
@section('page-title', 'Gestión de Materias')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-book me-2"></i>Lista de Materias</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMateria">
            <i class="bi bi-plus-circle me-1"></i> Nueva Materia
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Carga Horaria</th>
                        <th>Facultad</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materias as $materia)
                    <tr>
                        <td><code class="bg-light px-2 py-1">{{ $materia->codigo }}</code></td>
                        <td>
                            <strong>{{ $materia->nombre }}</strong>
                            @if($materia->requiere_laboratorio)
                                <span class="badge bg-danger ms-2"><i class="bi bi-beaker"></i> Requiere Laboratorio</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $materia->carga_horaria }} horas</span>
                        </td>
                        <td>{{ $materia->facultad->nombre ?? 'Sin asignar' }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" 
                                    onclick="editarMateria({{ json_encode($materia) }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('configuracion-academica.materias.destroy', $materia->id_materia) }}" 
                                  method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Eliminar esta materia?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No hay materias registradas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $materias->links() }}
    </div>
</div>

<!-- Modal Materia -->
<div class="modal fade" id="modalMateria">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formMateria" method="POST" action="{{ route('configuracion-academica.materias.store') }}">
                @csrf
                <input type="hidden" name="_method" id="methodMateria" value="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 id="tituloMateria">Nueva Materia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Código *</label>
                        <input type="text" class="form-control" name="codigo" id="codigo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Carga Horaria (horas)</label>
                        <input type="number" class="form-control" name="carga_horaria" id="carga_horaria" min="1" step="0.5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Facultad</label>
                        <select class="form-select" name="id_facultad" id="id_facultad">
                            <option value="">Sin asignar</option>
                            @foreach($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}">{{ $facultad->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="requiere_laboratorio" id="requiere_laboratorio" value="1">
                            <label class="form-check-label fw-medium" for="requiere_laboratorio">
                                <i class="bi bi-beaker"></i> Esta materia requiere laboratorio
                            </label>
                            <small class="d-block text-muted mt-1">Si se marca, se asignará un aula de laboratorio (máximo 1 vez/semana)</small>
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
function editarMateria(materia) {
    document.getElementById('formMateria').action = '/configuracion-academica/materias/' + materia.id_materia;
    document.getElementById('methodMateria').value = 'PUT';
    document.getElementById('tituloMateria').innerText = 'Editar Materia';
    document.getElementById('codigo').value = materia.codigo;
    document.getElementById('nombre').value = materia.nombre;
    document.getElementById('carga_horaria').value = materia.carga_horaria;
    document.getElementById('id_facultad').value = materia.id_facultad || '';
    document.getElementById('requiere_laboratorio').checked = materia.requiere_laboratorio;
    new bootstrap.Modal(document.getElementById('modalMateria')).show();
}

document.getElementById('modalMateria').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formMateria').reset();
    document.getElementById('formMateria').action = '{{ route("configuracion-academica.materias.store") }}';
    document.getElementById('methodMateria').value = 'POST';
    document.getElementById('tituloMateria').innerText = 'Nueva Materia';
});
</script>
@endsection
