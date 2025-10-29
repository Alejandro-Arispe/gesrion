@extends('layouts.app')
@section('page-title', 'Editar Usuario')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Editar Usuario: {{ $usuario->username }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('administracion.usuarios.update', $usuario->id_usuario) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label fw-medium">Username *</label>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username', $usuario->username) }}" 
                                   required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="correo" class="form-label fw-medium">Correo Electrónico</label>
                            <input type="email" 
                                   class="form-control @error('correo') is-invalid @enderror" 
                                   id="correo" 
                                   name="correo" 
                                   value="{{ old('correo', $usuario->correo) }}">
                            @error('correo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Nota:</strong> Deje los campos de contraseña vacíos si no desea cambiarla.
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-medium">Nueva Contraseña</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label fw-medium">Confirmar Contraseña</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="id_rol" class="form-label fw-medium">Rol *</label>
                            <select class="form-select @error('id_rol') is-invalid @enderror" 
                                    id="id_rol" 
                                    name="id_rol" 
                                    required>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol }}" 
                                            {{ old('id_rol', $usuario->id_rol) == $rol->id_rol ? 'selected' : '' }}>
                                        {{ $rol->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_rol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="activo" class="form-label fw-medium">Estado</label>
                            <select class="form-select" id="activo" name="activo">
                                <option value="1" {{ $usuario->activo ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ !$usuario->activo ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save me-1"></i> Actualizar
                        </button>
                        <a href="{{ route('administracion.usuarios.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
