@extends('layouts.app')
@section('page-title', 'Crear Usuario')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Nuevo Usuario</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('administracion.usuarios.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label fw-medium">
                                <i class="bi bi-person me-1"></i>Username *
                            </label>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username') }}" 
                                   required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="correo" class="form-label fw-medium">
                                <i class="bi bi-envelope me-1"></i>Correo Electrónico
                            </label>
                            <input type="email" 
                                   class="form-control @error('correo') is-invalid @enderror" 
                                   id="correo" 
                                   name="correo" 
                                   value="{{ old('correo') }}">
                            @error('correo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-medium">
                                <i class="bi bi-lock me-1"></i>Contraseña *
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label fw-medium">
                                <i class="bi bi-lock-fill me-1"></i>Confirmar Contraseña *
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="id_rol" class="form-label fw-medium">
                                <i class="bi bi-shield-check me-1"></i>Rol *
                            </label>
                            <select class="form-select @error('id_rol') is-invalid @enderror" 
                                    id="id_rol" 
                                    name="id_rol" 
                                    required>
                                <option value="">-- Seleccione un rol --</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol }}" 
                                            {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>
                                        {{ $rol->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_rol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="activo" class="form-label fw-medium">
                                <i class="bi bi-toggle-on me-1"></i>Estado
                            </label>
                            <select class="form-select" id="activo" name="activo">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar Usuario
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
