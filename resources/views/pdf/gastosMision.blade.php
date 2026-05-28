<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gastos Misión #{{ $mision->id }}</title>
    <style>
        @page {
            margin: 15mm 10mm;
            size: letter portrait;
        }
        body {
            font-family: "DejaVu Sans", "Helvetica", "Arial", sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        /* HEADER */
        .header {
            border-bottom: 3px solid #22c55e;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 14pt;
            font-weight: bold;
            color: #111827;
        }
        .header p {
            margin: 2px 0;
            font-size: 9pt;
            color: #4b5563;
        }

        /* RESUMEN */
        .resumen {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .resumen td {
            width: 25%;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            text-align: center;
        }
        .resumen .label {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 3px;
        }
        .resumen .value {
            font-size: 12pt;
            font-weight: bold;
            color: #1f2937;
        }
        .resumen .total {
            background: #d1fae5;
            border: 2px solid #22c55e;
        }
        .resumen .total .value {
            color: #059669;
            font-size: 14pt;
        }

        /* TABLA */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #111827;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }
        table.gastos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.gastos th {
            background: #f3f4f6;
            padding: 6px 5px;
            text-align: left;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        table.gastos td {
            padding: 6px 5px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 8.5pt;
        }
        table.gastos tr:nth-child(even) {
            background: #fafafa;
        }

        /* COLUMNAS */
        .col-num { width: 5%; text-align: center; font-weight: bold; }
        .col-agent { width: 18%; }
        .col-fecha { width: 12%; }
        .col-tipo { width: 10%; text-align: center; }
        .col-monto { width: 12%; text-align: right; font-weight: bold; }
        .col-detalles { width: 35%; }
        .col-evidencia { width: 8%; text-align: center; }

        /* TIPO BADGE */
        .badge-gasolina {
            background: #ffedd5;
            color: #c2410c;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
        }
        .badge-viaticos {
            background: #d1fae5;
            color: #059669;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10mm 10mm 5mm;
            border-top: 1px solid #e5e7eb;
            font-size: 7pt;
            color: #9ca3af;
            text-align: center;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-gray { color: #6b7280; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <h1>Gastos de Misión #{{ $mision->id }}</h1>
        <p><strong>{{ $mision->tipo_servicio }}</strong>
            @if($mision->nombre_clave) • {{ $mision->nombre_clave }} @endif</p>
        <p>Cliente: {{ $mision->cliente ?? 'N/A' }}</p>
        <p>Agentes: {{ implode(', ', $agentesNombres) }}</p>
    </div>

    {{-- RESUMEN --}}
    <table class="resumen">
        <tr>
            <td>
                <div class="label">Viáticos</div>
                <div class="value" style="color: #059669;">${{ number_format($totalViaticos, 2) }}</div>
                <div class="text-gray" style="font-size: 7.5pt;">{{ $gastos->where('Tipo', 'Viaticos')->count() }} registros</div>
            </td>
            <td>
                <div class="label">Gasolina</div>
                <div class="value" style="color: #ea580c;">${{ number_format($totalGasolina, 2) }}</div>
                <div class="text-gray" style="font-size: 7.5pt;">{{ number_format($totalLitros, 2) }} L • {{ number_format($totalKm, 2) }} Km</div>
            </td>
            <td>
                <div class="label">Total Gastos</div>
                <div class="value">{{ $gastos->count() }}</div>
                <div class="text-gray" style="font-size: 7.5pt;">en el período</div>
            </td>
            <td class="total">
                <div class="label">TOTAL GENERAL</div>
                <div class="value">${{ number_format($totalGeneral, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- TABLA --}}
    <div class="section-title">Detalle de Gastos</div>
    <table class="gastos">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-agent">Agente</th>
                <th class="col-fecha">Fecha</th>
                <th class="col-tipo">Tipo</th>
                <th class="col-monto">Monto</th>
                <th class="col-detalles">Detalles</th>
                <th class="col-evidencia">Evid.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gastos as $index => $gasto)
                <tr>
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-agent">{{ $agentesNombres[$gasto->user_id] ?? 'Agente #' . $gasto->user_id }}</td>
                    <td class="col-fecha">
                        {{ \Carbon\Carbon::parse($gasto->Fecha)->format('d/m/Y') }}
                        @if($gasto->Hora)
                            <br><span class="text-gray">{{ \Carbon\Carbon::parse($gasto->Hora)->format('H:i') }}</span>
                        @endif
                    </td>
                    <td class="col-tipo text-center">
                        @if($gasto->Tipo === 'Gasolina')
                            <span class="badge-gasolina">GASOLINA</span>
                        @else
                            <span class="badge-viaticos">VIÁTICOS</span>
                        @endif
                    </td>
                    <td class="col-monto">${{ number_format($gasto->Monto, 2) }}</td>
                    <td class="col-detalles">
                        @if($gasto->Tipo === 'Gasolina')
                            • {{ number_format($gasto->Litros, 2) }} litros<br>
                            • {{ number_format($gasto->Km, 2) }} km
                            @if($gasto->Gasolina_antes_carga && $gasto->Gasolina_despues_carga)
                                <br><span class="text-gray">Nivel: {{ number_format($gasto->Gasolina_antes_carga, 0) }} → {{ number_format($gasto->Gasolina_despues_carga, 0) }}</span>
                            @endif
                        @else
                            Viático registrado
                        @endif
                    </td>
                    <td class="col-evidencia">
                        {{ $gasto->Evidencia ? '✓' : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        Documento confidencial • ERP Seguridad • modulos.dominio.com.mx<br>
        Generado: {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
