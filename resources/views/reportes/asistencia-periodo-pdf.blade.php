<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asistencia por Período</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #333; }
        .header-info { text-align: center; margin: 15px 0; }
        .header-info p { margin: 5px 0; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { text-align: center; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; flex: 1; margin: 0 5px; }
        .stat-box .number { font-size: 18px; font-weight: bold; color: #2196F3; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #2196F3; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .presente { background-color: #c8e6c9; color: #2e7d32; font-weight: bold; }
        .ausente { background-color: #ffcdd2; color: #c62828; font-weight: bold; }
    </style>
</head>
<body>
    <h1>📈 Reporte de Asistencia por Período</h1>
    
    <div class="header-info">
        <p><strong>Docente:</strong> {{ $docente->nombre }}</p>
        <p><strong>Período:</strong> {{ date('d/m/Y', strtotime($asistencias->first()->fecha ?? date('Y-m-d'))) }} al {{ date('d/m/Y', strtotime($asistencias->last()->fecha ?? date('Y-m-d'))) }}</p>
        <p><strong>Generado:</strong> {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div>Total de registros</div>
            <div class="number">{{ $total }}</div>
        </div>
        <div class="stat-box">
            <div>Presentes</div>
            <div class="number" style="color: #4CAF50;">{{ $asistentes }}</div>
        </div>
        <div class="stat-box">
            <div>Ausentes</div>
            <div class="number" style="color: #f44336;">{{ $inasistentes }}</div>
        </div>
        <div class="stat-box">
            <div>Porcentaje de Asistencia</div>
            <div class="number" style="color: #2196F3;">{{ number_format($porcentaje, 1) }}%</div>
        </div>
    </div>

    @if($asistencias->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Materia</th>
                <th>Aula</th>
                <th>Horario</th>
                <th>Estado</th>
                <th>Ubicación GPS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($asistencias as $asistencia)
            <tr>
                <td>{{ $asistencia->fecha->format('d/m/Y') }}</td>
                <td>{{ $asistencia->horario->grupo->materia->nombre ?? 'N/A' }}</td>
                <td>{{ $asistencia->horario->aula->nro ?? 'N/A' }}</td>
                <td>{{ $asistencia->horario->hora_inicio }} - {{ $asistencia->horario->hora_fin }}</td>
                <td class="{{ $asistencia->asistio ? 'presente' : 'ausente' }}">
                    {{ $asistencia->asistio ? '✓ PRESENTE' : '✗ AUSENTE' }}
                </td>
                <td>{{ $asistencia->ubicacion_gps ?? 'Sin registrar' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align: center; color: #999; margin-top: 30px;">No hay registros de asistencia para este período</p>
    @endif
</body>
</html>
