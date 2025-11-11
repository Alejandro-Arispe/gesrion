<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Horarios</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .header { text-align: center; margin-bottom: 20px; }
        .dia-titulo { background-color: #2196F3; color: white; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Horarios</h1>
        <p>FICCT - Sistema de Gestión de Horarios</p>
        @if($docente)
            <p><strong>Docente:</strong> {{ $docente->nombre }}</p>
        @endif
        @if($gestion)
            <p><strong>Gestión:</strong> {{ $gestion->anio }}-{{ $gestion->semestre }}</p>
        @endif
        <p><strong>Fecha de generación:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    @foreach($horariosPorDia as $dia => $horarios)
        <h3 class="dia-titulo" style="padding: 10px; margin-top: 20px;">{{ $dia }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Materia</th>
                    <th>Grupo</th>
                    <th>Docente</th>
                    <th>Aula</th>
                </tr>
            </thead>
            <tbody>
                @foreach($horarios as $horario)
                <tr>
                    <td>{{ substr($horario->hora_inicio, 0, 5) }} - {{ substr($horario->hora_fin, 0, 5) }}</td>
                    <td>{{ $horario->grupo->materia->nombre }}</td>
                    <td>{{ $horario->grupo->nombre }}</td>
                    <td>{{ $horario->grupo->docente->nombre ?? 'Sin asignar' }}</td>
                    <td>{{ $horario->aula->nro }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>