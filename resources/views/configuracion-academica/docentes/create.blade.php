@extends('layouts.app')
@section('page-title', 'Crear Docente')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Nuevo Docente</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('configuracion-academica.docentes.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-card-text me-1"></i>CI *
                            </label>
                            <input type="text" class="form-control @error('ci') is-invalid @enderror" 
                                   name="ci" value="{{ old('ci') }}" required>
                            @error('ci')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-person me-1"></i>Nombre Completo *
                            </label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                   name="nombre" value="{{ old('nombre') }}" required>
                            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-gender-ambiguous me-1"></i>Sexo *
                            </label>
                            <select class="form-select @error('sexo') is-invalid @enderror" name="sexo" required>
                                <option value="">Seleccione</option>
                                <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                            @error('sexo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-phone me-1"></i>Teléfono
                            </label>
                            <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                   name="telefono" value="{{ old('telefono') }}">
                            @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-envelope me-1"></i>Correo
                            </label>
                            <input type="email" class="form-control @error('correo') is-invalid @enderror" 
                                   name="correo" value="{{ old('correo') }}">
                            @error('correo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-building me-1"></i>Facultad
                            </label>
                            <select class="form-select" name="id_facultad">
                                <option value="">Sin asignar</option>
                                @foreach($facultades as $facultad)
                                    <option value="{{ $facultad->id_facultad }}">{{ $facultad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-toggle-on me-1"></i>Estado
                            </label>
                            <select class="form-select" name="estado">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar
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