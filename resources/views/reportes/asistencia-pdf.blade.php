<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Asistencia</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h1 { text-align: center; color: #333; margin-bottom: 5px; }
        .fecha { text-align: center; font-size: 10px; color: #666; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #2196F3; color: white; padding: 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 7px; border: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .presente { color: #4CAF50; font-weight: bold; }
        .ausente { color: #f44336; font-weight: bold; }
        .total { background-color: #e3f2fd; font-weight: bold; }
    </style>
</head>
<body>
    <h1>📊 Reporte de Asistencia Docente</h1>
    <div class="fecha">Generado: {{ date('d/m/Y H:i') }}</div>

    @if($asistencias->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Docente</th>
                <th>Materia</th>
                <th>Aula</th>
                <th>Hora</th>
                <th>Estado</th>
                <th>Ubicación</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($asistencias as $asistencia)
            <tr>
                <td>{{ $asistencia->fecha->format('d/m/Y') }}</td>
                <td>{{ $asistencia->docente->nombre }}</td>
                <td>{{ $asistencia->horario->grupo->materia->nombre ?? 'N/A' }}</td>
                <td>{{ $asistencia->horario->aula->nro ?? 'N/A' }}</td>
                <td>{{ $asistencia->horario->hora_inicio }} - {{ $asistencia->horario->hora_fin }}</td>
                <td class="{{ $asistencia->asistio ? 'presente' : 'ausente' }}">
                    {{ $asistencia->asistio ? '✓ PRESENTE' : '✗ AUSENTE' }}
                </td>
                <td>{{ $asistencia->ubicacion_gps ?? 'N/A' }}</td>
                <td>{{ $asistencia->observacion ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; padding: 10px; background-color: #f5f5f5; border-left: 4px solid #2196F3;">
        <p><strong>Total registros:</strong> {{ $asistencias->count() }}</p>
        <p><strong>Presentes:</strong> {{ $asistencias->where('asistio', true)->count() }}</p>
        <p><strong>Ausentes:</strong> {{ $asistencias->where('asistio', false)->count() }}</p>
    </div>
    @else
    <p style="text-align: center; color: #999; margin-top: 30px;">No hay registros de asistencia</p>
    @endif
</body>
</html>
