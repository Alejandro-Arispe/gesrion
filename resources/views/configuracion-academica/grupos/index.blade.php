@extends('layouts.app')
@section('page-title', 'Gestión de Grupos')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Lista de Grupos</h5>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalGrupo">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Grupo
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Grupo</th>
                        <th>Materia</th>
                        <th>Docente</th>
                        <th>Gestión</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grupos as $grupo)
                    <tr>
                        <td>
                            <span class="badge bg-primary fs-6">{{ $grupo->nombre }}</span>
                        </td>
                        <td>
                            <strong>{{ $grupo->materia->nombre }}</strong><br>
                            <small class="text-muted">{{ $grupo->materia->codigo }}</small>
                        </td>
                        <td>
                            @if($grupo->docente)
                                <i class="bi bi-person-circle me-1"></i>{{ $grupo->docente->nombre }}
                            @else
                                <span class="text-muted">Sin asignar</span>
                            @endif
                        </td>
                        <td>
                            @if($grupo->gestion)
                                {{ $grupo->gestion->anio }}-{{ $grupo->gestion->semestre }}
                            @else
                                <span class="text-muted">Sin gestión</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" 
                                    onclick="editarGrupo({{ json_encode($grupo) }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('configuracion-academica.grupos.destroy', $grupo->id_grupo) }}" 
                                  method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Eliminar este grupo?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No hay grupos registrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $grupos->links() }}
    </div>
</div>

<!-- Modal Grupo -->
<div class="modal fade" id="modalGrupo">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formGrupo" method="POST" action="{{ route('configuracion-academica.grupos.store') }}">
                @csrf
                <input type="hidden" name="_method" id="methodGrupo" value="POST">
                <div class="modal-header bg-success text-white">
                    <h5 id="tituloGrupo">Nuevo Grupo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nombre Grupo *</label>
                        <input type="text" class="form-control" name="nombre" id="grupo_nombre" 
                               placeholder="Ej: A, B, 1, 2..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Materia *</label>
                        <select class="form-select" name="id_materia" id="grupo_materia" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($materias as $materia)
                                <option value="{{ $materia->id_materia }}">
                                    {{ $materia->codigo }} - {{ $materia->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Docente</label>
                        <select class="form-select" name="id_docente" id="grupo_docente">
                            <option value="">Sin asignar</option>
                            @foreach($docentes as $docente)
                                <option value="{{ $docente->id_docente }}">{{ $docente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Gestión *</label>
                        <select class="form-select" name="id_gestion" id="grupo_gestion" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($gestiones as $gestion)
                                <option value="{{ $gestion->id_gestion }}">
                                    {{ $gestion->anio }}-{{ $gestion->semestre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
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
function editarGrupo(grupo) {
    document.getElementById('formGrupo').action = '/configuracion-academica/grupos/' + grupo.id_grupo;
    document.getElementById('methodGrupo').value = 'PUT';
    document.getElementById('tituloGrupo').innerText = 'Editar Grupo';
    document.getElementById('grupo_nombre').value = grupo.nombre;
    document.getElementById('grupo_materia').value = grupo.id_materia;
    document.getElementById('grupo_docente').value = grupo.id_docente || '';
    document.getElementById('grupo_gestion').value = grupo.id_gestion || '';
    new bootstrap.Modal(document.getElementById('modalGrupo')).show();
}

document.getElementById('modalGrupo').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formGrupo').reset();
    document.getElementById('formGrupo').action = '{{ route("configuracion-academica.grupos.store") }}';
    document.getElementById('methodGrupo').value = 'POST';
    document.getElementById('tituloGrupo').innerText = 'Nuevo Grupo';
});
</script>
@endsection