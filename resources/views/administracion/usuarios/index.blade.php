@extends('layouts.app')
@section('page-title', 'Gestión de Usuarios')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Lista de Usuarios</h5>
        <a href="{{ route('administracion.usuarios.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Usuario
        </a>
    </div>
    <div class="card-body">
        <!-- Buscador -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form action="{{ route('administracion.usuarios.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Buscar por username o correo..." 
                               value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->id_usuario }}</td>
                        <td>
                            <i class="bi bi-person-circle me-2"></i>
                            <strong>{{ $usuario->username }}</strong>
                        </td>
                        <td>{{ $usuario->correo ?? 'Sin correo' }}</td>
                        <td>
                            <span class="badge bg-info">
                                {{ $usuario->rol->nombre ?? 'Sin rol' }}
                            </span>
                        </td>
                        <td>
                            @if($usuario->activo)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Activo</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Inactivo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('administracion.usuarios.edit', $usuario->id_usuario) }}" 
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('administracion.usuarios.destroy', $usuario->id_usuario) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Está seguro de eliminar este usuario?')"
                                            title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No hay usuarios registrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $usuarios->links() }}
        </div>
    </div>
</div>
@endsection