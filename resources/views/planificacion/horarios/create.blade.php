@extends('layouts.app')
@section('page-title', 'Asignar Horario')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Asignar Nuevo Horario</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('planificacion.horarios.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-collection me-1"></i>Grupo *
                            </label>
                            <select class="form-select @error('id_grupo') is-invalid @enderror" 
                                    name="id_grupo" required>
                                <option value="">-- Seleccione un grupo --</option>
                                @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id_grupo }}">
                                        {{ $grupo->nombre }} - {{ $grupo->materia->nombre }}
                                        ({{ $grupo->gestion->anio }}-{{ $grupo->gestion->semestre }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_grupo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-door-open me-1"></i>Aula *
                            </label>
                            <select class="form-select @error('id_aula') is-invalid @enderror" 
                                    name="id_aula" required>
                                <option value="">-- Seleccione un aula --</option>
                                @foreach($aulas as $aula)
                                    <option value="{{ $aula->id_aula }}">
                                        Aula {{ $aula->nro }} (Cap: {{ $aula->capacidad ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_aula')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-calendar-day me-1"></i>Día de la Semana *
                            </label>
                            <select class="form-select @error('dia_semana') is-invalid @enderror" 
                                    name="dia_semana" required>
                                <option value="">Seleccione</option>
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miércoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                                <option value="Sábado">Sábado</option>
                            </select>
                            @error('dia_semana')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-clock me-1"></i>Hora Inicio *
                            </label>
                            <input type="time" class="form-control @error('hora_inicio') is-invalid @enderror" 
                                   name="hora_inicio" required>
                            @error('hora_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-clock-fill me-1"></i>Hora Fin *
                            </label>
                            <input type="time" class="form-control @error('hora_fin') is-invalid @enderror" 
                                   name="hora_fin" required>
                            @error('hora_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-gear me-1"></i>Tipo de Asignación
                            </label>
                            <select class="form-select" name="tipo_asignacion">
                                <option value="Manual" selected>Manual</option>
                                <option value="Automática">Automática</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Nota:</strong> El sistema validará que no haya cruces de horarios en la misma aula.
                    </div>

                    <hr>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Asignar Horario
                        </button>
                        <a href="{{ route('planificacion.horarios.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection