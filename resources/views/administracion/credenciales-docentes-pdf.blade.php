<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Credenciales de Docentes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20mm;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15mm;
            margin-bottom: 15mm;
        }

        .header h1 {
            font-size: 24pt;
            color: #007bff;
            margin-bottom: 5mm;
        }

        .header p {
            color: #666;
            font-size: 11pt;
            margin: 2mm 0;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 10mm;
            margin-bottom: 15mm;
            border-radius: 4px;
        }

        .info-box p {
            margin: 2mm 0;
            font-size: 10pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10mm;
        }

        thead {
            background: #007bff;
            color: white;
        }

        th {
            padding: 8mm;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
            font-size: 10pt;
        }

        td {
            padding: 6mm 8mm;
            border: 1px solid #dee2e6;
            font-size: 10pt;
        }

        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        tbody tr:hover {
            background: #e9ecef;
        }

        .status-activo {
            background: #d4edda;
            color: #155724;
            padding: 2mm 4mm;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
            font-size: 9pt;
        }

        .status-inactivo {
            background: #f8d7da;
            color: #721c24;
            padding: 2mm 4mm;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
            font-size: 9pt;
        }

        .footer {
            margin-top: 20mm;
            padding-top: 10mm;
            border-top: 1px solid #dee2e6;
            text-align: right;
            font-size: 9pt;
            color: #666;
        }

        .advertencia {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10mm;
            margin-bottom: 10mm;
            border-radius: 4px;
            font-size: 10pt;
            color: #856404;
        }

        .advertencia strong {
            display: block;
            margin-bottom: 3mm;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .container {
                padding: 20mm;
            }

            .page-break {
                page-break-after: always;
            }

            a {
                text-decoration: none;
                color: inherit;
            }
        }

        .table-responsive {
            overflow-x: auto;
        }

        .contrase-box {
            background: #fffbea;
            padding: 3mm 6mm;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Credenciales de Acceso - Docentes</h1>
            <p><strong>Sistema de Gestión de Horarios y Aulas</strong></p>
            <p>Generado: {{ $fecha_generacion }}</p>
        </div>

        <!-- Advertencia -->
        <div class="advertencia">
            <strong>⚠️ INFORMACIÓN CONFIDENCIAL</strong>
            Este documento contiene las credenciales de acceso de los docentes.<br>
            Se recomienda:<br>
            • Distribuir únicamente a los docentes correspondientes<br>
            • Almacenar en lugar seguro<br>
            • Destruir después de que los docentes cambien sus contraseñas
        </div>

        <!-- Información general -->
        <div class="info-box">
            <p><strong>Instrucciones de acceso:</strong></p>
            <p>1. Ingrese a la plataforma con su usuario y contraseña</p>
            <p>2. Cambie su contraseña en el primer acceso (Perfil → Seguridad)</p>
            <p>3. Marque asistencia mediante escaneo de QR en los horarios asignados</p>
            <p>4. Consulte su distribución de horarios en cualquier momento</p>
        </div>

        <!-- Tabla de credenciales -->
        <h2 style="color: #007bff; margin-bottom: 8mm; font-size: 14pt;">Credenciales de Acceso</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">Docente</th>
                    <th style="width: 20%;">Usuario</th>
                    <th style="width: 25%;">Contraseña</th>
                    <th style="width: 15%;">Correo</th>
                    <th style="width: 10%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($credenciales as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->nombre }}</strong>
                        </td>
                        <td>
                            <strong>{{ $item->username }}</strong>
                        </td>
                        <td>
                            <div class="contrase-box">
                                • • • • • • •
                            </div>
                            <small style="color: #999;">
                                (Ver en copia impresa o digital segura)
                            </small>
                        </td>
                        <td>
                            <small>{{ $item->correo ?? 'No registrado' }}</small>
                        </td>
                        <td>
                            @if($item->activo)
                                <div class="status-activo">Activo</div>
                            @else
                                <div class="status-inactivo">Inactivo</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Notas finales -->
        <div class="footer">
            <p><strong>Notas importantes:</strong></p>
            <p>• Total de docentes: <strong>{{ $credenciales->count() }}</strong></p>
            <p>• Las contraseñas se almacenan encriptadas en la base de datos</p>
            <p>• Cada docente puede cambiar su contraseña desde su perfil</p>
            <p style="margin-top: 8mm; color: #999; font-size: 9pt;">
                Documento generado automáticamente el {{ $fecha_generacion }}<br>
                Sistema de Gestión de Horarios y Aulas
            </p>
        </div>
    </div>
</body>
</html>
