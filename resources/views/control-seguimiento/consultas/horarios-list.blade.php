@if($horarios->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Día Semana</th>
                    <th>Hora</th>
                    <th>Materia</th>
                    <th>Grupo</th>
                    <th>Aula</th>
                    <th>Docente</th>
                </tr>
            </thead>
            <tbody>
                @foreach($horarios as $horario)
                <tr>
                    <td>
                        <strong>{{ ucfirst($horario->dia_semana) }}</strong>
                    </td>
                    <td>
                        <i class="bi bi-clock-fill me-1 text-info"></i>
                        {{ $horario->hora_inicio }} - {{ $horario->hora_fin }}
                    </td>
                    <td>{{ $horario->grupo->materia->nombre }}</td>
                    <td>
                        <span class="badge bg-info text-dark">{{ $horario->grupo->nombre }}</span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark">{{ $horario->aula->nro }}</span>
                    </td>
                    <td>{{ $horario->grupo->docente->nombre ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info mb-0">
        <i class="bi bi-info-circle me-2"></i>No hay horarios para mostrar
    </div>
@endif
