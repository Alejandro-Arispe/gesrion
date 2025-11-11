<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Aulas - Formato {{ $formato }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            padding: 20mm;
        }

        .container {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 10mm;
            justify-content: flex-start;
        }

        /* Formato 4x4 (1 por página) */
        @if($formato === '4x4')
            .qr-item {
                width: 100%;
                height: 25cm;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                page-break-after: always;
                border: 3px solid #333;
                padding: 20mm;
                background: white;
            }

            .qr-item svg {
                width: 10cm !important;
                height: 10cm !important;
                margin-bottom: 20mm;
            }

            .qr-info {
                text-align: center;
                font-size: 32pt;
                font-weight: bold;
            }

            .qr-label {
                font-size: 14pt;
                color: #666;
                margin-top: 10mm;
            }
        @endif

        /* Formato 2x3 (6 por página) */
        @if($formato === '2x3')
            .container {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 8mm;
                width: 100%;
            }

            .qr-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                border: 2px solid #ccc;
                padding: 10mm;
                background: white;
                page-break-inside: avoid;
                aspect-ratio: 1;
                break-inside: avoid;
            }

            .qr-item svg {
                width: 8cm !important;
                height: 8cm !important;
                margin-bottom: 5mm;
            }

            .qr-info {
                text-align: center;
                font-size: 18pt;
                font-weight: bold;
            }

            .qr-label {
                font-size: 10pt;
                color: #666;
                margin-top: 3mm;
            }
        @endif

        /* Formato 3x4 (12 por página) */
        @if($formato === '3x4')
            .container {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 5mm;
                width: 100%;
            }

            .qr-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                border: 1px solid #ddd;
                padding: 5mm;
                background: white;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .qr-item svg {
                width: 6cm !important;
                height: 6cm !important;
                margin-bottom: 3mm;
            }

            .qr-info {
                text-align: center;
                font-size: 12pt;
                font-weight: bold;
            }

            .qr-label {
                font-size: 8pt;
                color: #666;
                margin-top: 2mm;
            }
        @endif

        /* Formato 5x6 (30 por página) */
        @if($formato === '5x6')
            .container {
                display: grid;
                grid-template-columns: repeat(6, 1fr);
                gap: 3mm;
                width: 100%;
            }

            .qr-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                border: 0.5px solid #ddd;
                padding: 3mm;
                background: white;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .qr-item svg {
                width: 4cm !important;
                height: 4cm !important;
                margin-bottom: 1mm;
            }

            .qr-info {
                text-align: center;
                font-size: 10pt;
                font-weight: bold;
            }

            .qr-label {
                font-size: 7pt;
                color: #666;
                margin-top: 1mm;
            }
        @endif

        .header {
            text-align: center;
            margin-bottom: 20mm;
            page-break-after: avoid;
        }

        .header h1 {
            font-size: 24pt;
            margin-bottom: 5mm;
        }

        .header p {
            font-size: 12pt;
            color: #666;
        }

        @media print {
            body {
                padding: 10mm;
            }

            .qr-item {
                background: white;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Códigos QR para Aulas</h1>
        <p>Generado: {{ date('d/m/Y H:i') }}</p>
        <p style="font-size: 10pt; color: #999;">Formato: {{ $formato }} - Total: {{ $qrs->count() }} QRs</p>
    </div>

    <div class="container">
        @foreach($qrs as $qr)
            <div class="qr-item">
                {!! $qr->codigo_qr !!}
                <div class="qr-info">
                    Aula {{ $qr->aula->nro }}
                </div>
                <div class="qr-label">
                    {{ $qr->aula->tipo_aula ?? 'N/A' }} - Piso {{ $qr->aula->piso }}
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
