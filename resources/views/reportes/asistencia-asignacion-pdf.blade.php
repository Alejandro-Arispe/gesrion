<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Asistencia por Asignación</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px; 
            margin: 0; 
            padding: 15px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #2E7D32;
            padding-bottom: 10px;
        }
        
        .header h1 { 
            margin: 0; 
            color: #2E7D32; 
            font-size: 16px;
        }
        
        .header-info {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }
        
        .docente-info {
            margin-top: 15px;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #E8F5E9;
            border-left: 4px solid #2E7D32;
        }
        
        .docente-info p {
            margin: 5px 0;
            font-weight: bold;
        }
        
        .asignacion-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .asignacion-header {
            background-color: #2E7D32;
            color: white;
            padding: 8px;
            border-radius: 3px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .asignacion-details {
            margin-left: 10px;
            margin-bottom: 10px;
            font-size: 9px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 3px;
        }
        
        .detail-label {
            width: 80px;
            font-weight: bold;
            color: #1565C0;
        }
        
        .detail-value {
            flex: 1;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        th { 
            background-color: #1976D2; 
            color: white; 
            padding: 6px; 
            text-align: left; 
            border: 1px solid #ccc;
            font-weight: bold;
        }
        
        td { 
            padding: 5px; 
            border: 1px solid #ddd; 
        }
        
        tr:nth-child(even) { 
            background-color: #f9f9f9; 
        }
        
        .presente { 
            background-color: #C8E6C9; 
            color: #1B5E20; 
            font-weight: bold; 
        }
        
        .atrasado { 
            background-color: #FFF9C4; 
            color: #F57F17; 
            font-weight: bold; 
        }
        
        .ausente { 
            background-color: #FFCDD2; 
            color: #B71C1C; 
            font-weight: bold; 
        }
        
        .fuera { 
            background-color: #F3E5F5; 
            color: #512DA8; 
            font-weight: bold; 
        }
        
        .porcentaje-alto {
            background-color: #C8E6C9;
            font-weight: bold;
            color: #1B5E20;
        }
        
        .porcentaje-medio {
            background-color: #FFF9C4;
            font-weight: bold;
            color: #F57F17;
        }
        
        .porcentaje-bajo {
            background-color: #FFCDD2;
            font-weight: bold;
            color: #B71C1C;
        }
        
        .stats-container {
            margin-top: 20px;
            padding: 10px;
            background-color: #E3F2FD;
            border-left: 4px solid #1976D2;
            border-radius: 3px;
        }
        
        .stats-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 10px;
        }
        
        .stats-label {
            font-weight: bold;
        }
        
        .total-general {
            margin-top: 25px;
            padding: 15px;
            background-color: #2E7D32;
            color: white;
            border-radius: 3px;
            text-align: center;
        }
        
        .total-general h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
        }
        
        .total-stats {
            display: flex;
            justify-content: space-around;
            font-size: 10px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 REPORTE DE ASISTENCIA POR ASIGNACIÓN</h1>
        <div class="header-info">
            <p>Período: {{ $fechaInicio }} al {{ $fechaFin }} | Generado: {{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="docente-info">
        <p>👨‍🏫 Docente: {{ $docente->nombre }}</p>
        <p>📋 Total de Asignaciones: {{ $reporteAsignaciones->count() }}</p>
    </div>

    @forelse($reporteAsignaciones as $index => $asignacion)
        <div class="asignacion-section">
            <div class="asignacion-header">
                {{ $loop->iteration }}. {{ $asignacion['materia'] }} - Grupo {{ $asignacion['grupo'] }}
            </div>

            <div class="asignacion-details">
                <div class="detail-row">
                    <span class="detail-label">Aula:</span>
                    <span class="detail-value">{{ $asignacion['aula'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Día:</span>
                    <span class="detail-value">{{ $asignacion['dia_semana'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Horario:</span>
                    <span class="detail-value">{{ $asignacion['horario'] }}</span>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 10%;">Hora</th>
                        <th style="width: 15%;">Estado</th>
                        <th style="width: 25%;">Ubicación GPS</th>
                        <th style="width: 38%;">Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asignacion['asistencias'] as $asistencia)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $asistencia->hora_marcado }}</td>
                            <td class="
                                @if($asistencia->estado == 'Presente')
                                    presente
                                @elseif($asistencia->estado == 'Atrasado')
                                    atrasado
                                @elseif($asistencia->estado == 'Ausente')
                                    ausente
                                @elseif($asistencia->estado == 'Fuera de aula')
                                    fuera
                                @endif
                            ">
                                @if($asistencia->estado == 'Presente')
                                    ✓ PRESENTE
                                @elseif($asistencia->estado == 'Atrasado')
                                    ⏱ ATRASADO
                                @elseif($asistencia->estado == 'Ausente')
                                    ✗ AUSENTE
                                @elseif($asistencia->estado == 'Fuera de aula')
                                    📍 FUERA
                                @endif
                            </td>
                            <td>
                                @if($asistencia->latitud && $asistencia->longitud)
                                    {{ number_format($asistencia->latitud, 4) }}, 
                                    {{ number_format($asistencia->longitud, 4) }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($asistencia->estado == 'Fuera de aula')
                                    <strong>⚠️ Ubicación fuera de rango (50m)</strong>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #999;">
                                No hay registros de asistencia en este período
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="stats-container">
                <div class="stats-row">
                    <span class="stats-label">Total Registros:</span>
                    <span>{{ $asignacion['total'] }}</span>
                </div>
                <div class="stats-row">
                    <span class="stats-label">✓ Presentes:</span>
                    <span>{{ $asignacion['presentes'] }}</span>
                </div>
                <div class="stats-row">
                    <span class="stats-label">⏱ Atrasados:</span>
                    <span>{{ $asignacion['atrasados'] }}</span>
                </div>
                <div class="stats-row">
                    <span class="stats-label">✗ Ausentes:</span>
                    <span>{{ $asignacion['ausentes'] }}</span>
                </div>
                <div class="stats-row">
                    <span class="stats-label">📍 Fuera de Aula:</span>
                    <span>{{ $asignacion['fuera_aula'] }}</span>
                </div>
                <div class="stats-row" style="border-top: 1px solid #90CAF9; padding-top: 8px; margin-top: 8px;">
                    <span class="stats-label">📊 % Asistencia:</span>
                    <span class="
                        @if($asignacion['porcentaje_asistencia'] >= 80)
                            porcentaje-alto
                        @elseif($asignacion['porcentaje_asistencia'] >= 60)
                            porcentaje-medio
                        @else
                            porcentaje-bajo
                        @endif
                    ">
                        {{ $asignacion['porcentaje_asistencia'] }}%
                    </span>
                </div>
            </div>
        </div>

        @if(!$loop->last && $loop->iteration % 2 == 0)
            <div class="page-break"></div>
        @endif
    @empty
        <p style="text-align: center; color: #999; margin-top: 30px;">
            No hay asignaciones para mostrar
        </p>
    @endforelse

    <!-- Total General -->
    <div class="total-general">
        <h3>📈 RESUMEN GENERAL DEL PERÍODO</h3>
        <div class="total-stats">
            <div>
                <p style="margin: 0;">Total: <strong>{{ $totalGeneral }}</strong></p>
            </div>
            <div>
                <p style="margin: 0;">✓ Presentes: <strong>{{ $presentesGeneral }}</strong></p>
            </div>
            <div>
                <p style="margin: 0;">⏱ Atrasados: <strong>{{ $atrasadosGeneral }}</strong></p>
            </div>
            <div>
                <p style="margin: 0;">✗ Ausentes: <strong>{{ $ausentesGeneral }}</strong></p>
            </div>
            <div>
                <p style="margin: 0;">📍 Fuera: <strong>{{ $fueraAulaGeneral }}</strong></p>
            </div>
            <div>
                <p style="margin: 0;">📊 Asistencia: <strong>{{ round($porcentajeGeneral, 2) }}%</strong></p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Este documento fue generado automáticamente por el sistema de gestión GESTION</p>
        <p>Confidencial - Uso exclusivo del docente y administración académica</p>
    </div>
</body>
</html>
