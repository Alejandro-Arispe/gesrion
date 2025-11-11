@if($horarios->count() > 0)
    <div class="alert alert-info mb-3">
        <i class="bi bi-calendar-day me-2"></i>
        <strong>Horarios para hoy: {{ \Carbon\Carbon::parse($hoy)->format('d/m/Y') }} ({{ ucfirst($diaSemana) }})</strong>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Hora</th>
                    <th>Materia</th>
                    <th>Grupo</th>
                    <th>Aula</th>
                    <th>Docente</th>
                    <th class="text-center">Estado Asistencia</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($horarios as $horario)
                <tr>
                    <td>
                        <i class="bi bi-clock me-1 text-info"></i>
                        <strong>{{ $horario->hora_inicio }} - {{ $horario->hora_fin }}</strong>
                    </td>
                    <td>{{ $horario->grupo->materia->nombre }}</td>
                    <td>
                        <span class="badge bg-info text-dark">{{ $horario->grupo->nombre }}</span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark">{{ $horario->aula->nro }}</span>
                    </td>
                    <td>{{ $horario->grupo->docente->nombre ?? 'N/A' }}</td>
                    <td class="text-center">
                        @if($horario->registrado)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Registrado ({{ $horario->asistencia->estado }})
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="bi bi-clock me-1"></i>Pendiente
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if(!$horario->registrado)
                            <a href="{{ route('control-seguimiento.asistencia.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Registrar
                            </a>
                        @else
                            <button type="button" class="btn btn-sm btn-secondary" disabled>
                                <i class="bi bi-check me-1"></i>Registrado
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        No hay horarios programados para hoy ({{ ucfirst($diaSemana) }})
    </div>
@endif
