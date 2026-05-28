<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Itinerario Misión #{{ $mision->id }}</title>
    <style>
        /* CONFIGURACIÓN BASE */
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

        /* ENCABEZADO */
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
        .header-meta {
            text-align: right;
            font-size: 8pt;
            color: #6b7280;
            margin-top: 5px;
        }

        /* TARJETAS DE INFORMACIÓN */
        .info-cards {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-cards table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-cards td {
            width: 33%;
            padding: 8px 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .info-cards .label {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 3px;
        }
        .info-cards .value {
            font-size: 9.5pt;
            font-weight: 600;
            color: #1f2937;
        }

        /* TABLA DE EVENTOS */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #111827;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }
        table.events {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.events th {
            background: #f3f4f6;
            padding: 6px 5px;
            text-align: left;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        table.events td {
            padding: 6px 5px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 8.5pt;
        }
        table.events tr:nth-child(even) {
            background: #fafafa;
        }

        /* COLUMNAS */
        .col-num { width: 5%; text-align: center; font-weight: bold; }
        .col-agent { width: 20%; }
        .col-date { width: 10%; }
        .col-time { width: 8%; text-align: center; font-weight: bold; }
        .col-event { width: 30%; }
        .col-location { width: 20%; }
        .col-registered { width: 7%; text-align: right; font-size: 7.5pt; }

        /* AVATAR AGENTE */
        .agent-info {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            font-weight: bold;
            font-size: 9pt;
            text-align: center;
            line-height: 24px;
        }
        .agent-name {
            font-weight: 600;
            color: #1f2937;
        }

        /* UBICACIÓN */
        .location {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7.5pt;
            border: 1px solid #bfdbfe;
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

        /* UTILIDADES */
        .text-green { color: #22c55e; }
        .text-blue { color: #1d4ed8; }
        .text-gray { color: #6b7280; }
        .font-bold { font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <div class="header">
        <h1>Itinerario de Misión #{{ $mision->id }}</h1>
        <p><strong>{{ $mision->tipo_servicio }}</strong>
            @if($mision->nombre_clave) • {{ $mision->nombre_clave }} @endif</p>
        <p>Cliente: {{ $mision->cliente ?? 'N/A' }}</p>
        <div class="header-meta">
            Generado: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    {{-- TARJETAS DE RESUMEN --}}
    <div class="info-cards">
        <table>
            <tr>
                <td>
                    <div class="label">Periodo</div>
                    <div class="value">
                        {{ \Carbon\Carbon::parse($mision->fecha_inicio)->format('d/m/Y') }}<br>
                        <span class="text-gray">al</span><br>
                        {{ \Carbon\Carbon::parse($mision->fecha_fin)->format('d/m/Y') }}
                    </div>
                </td>
                <td>
                    <div class="label">Total Eventos</div>
                    <div class="value text-green" style="font-size: 12pt;">{{ count($eventosPlanos) }}</div>
                </td>
                <td>
                    <div class="label">Agentes</div>
                    <div class="value">
                        @if($mision->nombres_agentes)
                            {{ $mision->nombres_agentes }}
                        @else
                            <span class="text-gray">Sin asignar</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- TÍTULO DE SECCIÓN --}}
    <div class="section-title">Detalle de Eventos</div>

    {{-- TABLA DE EVENTOS --}}
    @if(empty($eventosPlanos))
        <table style="width:100%; border:1px dashed #d1d5db; padding:15px;">
            <tr>
                <td style="text-align:center; color:#6b7280; font-style:italic; padding:15px;">
                    No hay eventos registrados en este itinerario.
                </td>
            </tr>
        </table>
    @else
        <table class="events">
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th class="col-agent">Agente</th>
                    <th class="col-date">Fecha</th>
                    <th class="col-time">Hora</th>
                    <th class="col-event">Evento</th>
                    <th class="col-location">Ubicación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($eventosPlanos as $index => $evento)
                    <tr>
                        <td class="col-num text-center">{{ $index + 1 }}</td>

                        <td class="col-agent">
                            <div class="agent-info">
                                <span class="agent-name">{{ $evento['user_name'] }}</span>
                            </div>
                        </td>

                        <td class="col-date">
                            {{ $evento['fecha'] ? \Carbon\Carbon::parse($evento['fecha'])->format('d/m/y') : 'N/A' }}
                        </td>

                        <td class="col-time">{{ $evento['hora'] ?? 'N/A' }}</td>

                        <td class="col-event">
                            {{ Str::limit($evento['descripcion'] ?? 'Sin descripción', 80) }}
                        </td>

                        <td class="col-location">
                            @if($evento['ubicacion'])
                                <span class="location">📍 {{ Str::limit($evento['ubicacion'], 30) }}</span>
                            @else
                                <span class="text-gray">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- OBSERVACIONES --}}
    @if($mision->notas ?? false)
        <div class="section-title">Observaciones</div>
        <div style="background:#f9fafb; border:1px solid #e5e7eb; padding:8px 10px; font-size:8.5pt;">
            {{ $mision->notas }}
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        Documento confidencial • Sistema de Gestión Integral • Servicios de Protección y Traslado<br>
        Página <script type="text/php">
            if (isset($pdf)) {
                echo $pdf->get_page_number() . ' de ' . $pdf->get_page_count();
            }
        </script>
        • Generado: {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
