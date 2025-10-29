@extends('layouts.app')
@section('page-title', 'Gestión de Docentes')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Lista de Docentes</h5>
        <a href="{{ route('configuracion-academica.docentes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Docente
        </a>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form action="{{ route('configuracion-academica.docentes.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Buscar por CI o nombre..." 
                           value="{{ request('search') }}">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>CI</th>
                        <th>Nombre</th>
                        <th>Sexo</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docentes as $docente)
                    <tr>
                        <td><code>{{ $docente->ci }}</code></td>
                        <td>
                            <i class="bi bi-person-circle me-2 text-primary"></i>
                            <strong>{{ $docente->nombre }}</strong>
                        </td>
                        <td>
                            @if($docente->sexo == 'M')
                                <i class="bi bi-gender-male text-primary"></i> Masculino
                            @else
                                <i class="bi bi-gender-female text-danger"></i> Femenino
                            @endif
                        </td>
                        <td>
                            @if($docente->telefono)
                                <small><i class="bi bi-phone"></i> {{ $docente->telefono }}</small><br>
                            @endif
                            @if($docente->correo)
                                <small><i class="bi bi-envelope"></i> {{ $docente->correo }}</small>
                            @endif
                        </td>
                        <td>
                            @if($docente->estado)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('configuracion-academica.docentes.edit', $docente->id_docente) }}" 
                               class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('configuracion-academica.docentes.destroy', $docente->id_docente) }}" 
                                  method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Eliminar este docente?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No hay docentes registrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $docentes->links() }}
    </div>
</div>
@endsection
