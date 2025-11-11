<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Horarios</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h1 { text-align: center; color: #333; margin-bottom: 5px; }
        .fecha { text-align: center; font-size: 10px; color: #666; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #4CAF50; color: white; padding: 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 7px; border: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .total { background-color: #e8f5e9; font-weight: bold; }
    </style>
</head>
<body>
    <h1>📋 Reporte de Horarios</h1>
    <div class="fecha">Generado: {{ date('d/m/Y H:i') }}</div>

    @if($horarios->count() > 0)
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
            </tr>
        </thead>
        <tbody>
            @foreach($horarios as $horario)
            <tr>
                <td>{{ $horario->grupo->docente->nombre ?? 'Sin asignar' }}</td>
                <td>{{ $horario->grupo->materia->nombre }}</td>
                <td>{{ $horario->grupo->nombre }}</td>
                <td>{{ $horario->aula->nro ?? 'N/A' }}</td>
                <td>{{ $horario->dia_semana }}</td>
                <td>{{ $horario->hora_inicio }}</td>
                <td>{{ $horario->hora_fin }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="text-align: right; margin-top: 15px; font-weight: bold;">
        Total de horarios: {{ $horarios->count() }}
    </p>
    @else
    <p style="text-align: center; color: #999; margin-top: 30px;">No hay horarios para mostrar</p>
    @endif
</body>
</html>
