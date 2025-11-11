@extends('layouts.app')
@section('page-title', 'Asignación de Horarios')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Horarios Asignados</h5>
        <a href="{{ route('planificacion.horarios.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Asignar Horario
        </a>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-4">
                <select class="form-select" id="filtroGestion" onchange="filtrarHorarios()">
                    <option value="">Todas las gestiones</option>
                    @foreach($gestiones as $g)
                        <option value="{{ $g->id_gestion }}">{{ $g->anio }}-{{ $g->semestre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Día</th>
                        <th>Horario</th>
                        <th>Grupo</th>
                        <th>Materia</th>
                        <th>Docente</th>
                        <th>Aula</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($horarios as $horario)
                    <tr>
                        <td>
                            <strong>{{ $horario->dia_semana }}</strong>
                        </td>
                        <td>
                            <i class="bi bi-clock me-1"></i>
                            {{ substr($horario->hora_inicio, 0, 5) }} - {{ substr($horario->hora_fin, 0, 5) }}
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $horario->grupo->nombre }}</span>
                        </td>
                        <td>{{ $horario->grupo->materia->nombre }}</td>
                        <td>
                            @if($horario->grupo->docente)
                                <small>{{ $horario->grupo->docente->nombre }}</small>
                            @else
                                <span class="text-muted">Sin docente</span>
                            @endif
                        </td>
                        <td>
                            @if($horario->aula)
                                <span class="badge bg-info">Aula {{ $horario->aula->nro }}</span>
                            @else
                                <span class="badge bg-warning text-dark">Sin aula</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <form action="{{ route('planificacion.horarios.destroy', $horario->id_horario) }}" 
                                  method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Eliminar este horario?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No hay horarios asignados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $horarios->links() }}
    </div>
</div>
@endsection
        