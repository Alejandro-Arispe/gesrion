@if($asistencias->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Hora Marcado</th>
                    <th>Materia</th>
                    <th>Grupo</th>
                    <th>Aula</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asistencias as $asistencia)
                <tr>
                    <td>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</small>
                    </td>
                    <td>
                        <i class="bi bi-clock me-1"></i>
                        {{ substr($asistencia->hora_marcado, 0, 5) }}
                    </td>
                    <td>{{ $asistencia->horario->grupo->materia->nombre }}</td>
                    <td>
                        <span class="badge bg-info text-dark">{{ $asistencia->horario->grupo->nombre }}</span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark">{{ $asistencia->horario->aula->nro }}</span>
                    </td>
                    <td>
                        @php
                            $config = [
                                'Presente' => ['badge' => 'bg-success', 'icon' => 'check-circle'],
                                'Atrasado' => ['badge' => 'bg-warning', 'icon' => 'clock'],
                                'Ausente' => ['badge' => 'bg-danger', 'icon' => 'x-circle'],
                                'Fuera de aula' => ['badge' => 'bg-secondary', 'icon' => 'geo-alt'],
                            ];
                            $cfg = $config[$asistencia->estado] ?? ['badge' => 'bg-secondary', 'icon' => 'question'];
                        @endphp
                        <span class="badge {{ $cfg['badge'] }}">
                            <i class="bi bi-{{ $cfg['icon'] }} me-1"></i>{{ $asistencia->estado }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <nav class="mt-3">
        {{ $asistencias->links() }}
    </nav>
@else
    <div class="alert alert-info mb-0">
        <i class="bi bi-info-circle me-2"></i>Sin registros de asistencia
    </div>
@endif
