<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carga Horaria Docente</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .header { text-align: center; margin-bottom: 20px; }
        .total { background-color: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Carga Horaria Docente</h1>
        <p><strong>Generado:</strong> {{ date('d/m/Y H:i') }}</p>
    </div>

    @if($docentes->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Docente</th>
                <th>Materia</th>
                <th>Grupo</th>
                <th>Aula</th>
                <th>Día</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Horas</th>
            </tr>
        </thead>
        <tbody>
            @php $totalHoras = 0; @endphp
            @foreach($docentes as $docente)
                @php $horasDocente = 0; @endphp
                @foreach($docente->grupos as $grupo)
                    @foreach($grupo->horarios as $horario)
                        @php 
                            $horaInicio = \Carbon\Carbon::createFromFormat('H:i', $horario->hora_inicio);
                            $horaFin = \Carbon\Carbon::createFromFormat('H:i', $horario->hora_fin);
                            $horas = $horaFin->diffInMinutes($horaInicio) / 60;
                            $horasDocente += $horas;
                            $totalHoras += $horas;
                        @endphp
                        <tr>
                            <td>{{ $docente->nombre }}</td>
                            <td>{{ $grupo->materia->nombre }}</td>
                            <td>{{ $grupo->nombre }}</td>
                            <td>{{ $horario->aula->nro ?? 'N/A' }}</td>
                            <td>{{ $horario->dia_semana }}</td>
                            <td>{{ $horario->hora_inicio }}</td>
                            <td>{{ $horario->hora_fin }}</td>
                            <td>{{ number_format($horas, 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach
                @if($horasDocente > 0)
                <tr class="total">
                    <td colspan="7"><strong>Total {{ $docente->nombre }}:</strong></td>
                    <td><strong>{{ number_format($horasDocente, 2) }} horas</strong></td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right;">
        <h3 style="background-color: #4CAF50; color: white; padding: 10px;">
            Total de Horas: <strong>{{ number_format($totalHoras, 2) }} horas</strong>
        </h3>
    </div>
    @else
    <div style="text-align: center; color: #666; margin-top: 30px;">
        <p>No hay datos de carga horaria para mostrar</p>
    </div>
    @endif
</body>
</html>