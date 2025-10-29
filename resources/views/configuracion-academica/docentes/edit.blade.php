@extends('layouts.app')
@section('page-title', 'Editar Docente')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Editar: {{ $docente->nombre }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('configuracion-academica.docentes.update', $docente->id_docente) }}" method="POST">
                    @csrf @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">CI *</label>
                            <input type="text" class="form-control @error('ci') is-invalid @enderror" 
                                   name="ci" value="{{ old('ci', $docente->ci) }}" required>
                            @error('ci')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-medium">Nombre Completo *</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                   name="nombre" value="{{ old('nombre', $docente->nombre) }}" required>
                            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Sexo *</label>
                            <select class="form-select" name="sexo" required>
                                <option value="M" {{ old('sexo', $docente->sexo) == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo', $docente->sexo) == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" 
                                   value="{{ old('telefono', $docente->telefono) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Correo</label>
                            <input type="email" class="form-control" name="correo" 
                                   value="{{ old('correo', $docente->correo) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Facultad</label>
                            <select class="form-select" name="id_facultad">
                                <option value="">Sin asignar</option>
                                @foreach($facultades as $facultad)
                                    <option value="{{ $facultad->id_facultad }}" 
                                        {{ old('id_facultad', $docente->id_facultad) == $facultad->id_facultad ? 'selected' : '' }}>
                                        {{ $facultad->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="1" {{ $docente->estado ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ !$docente->estado ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save me-1"></i> Actualizar
                        </button>
                        <a href="{{ route('configuracion-academica.docentes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection