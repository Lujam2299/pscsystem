<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kárdex de Vacaciones - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        h1 {
            font-size: 18px;
            color: #1e40af;
            text-align: center;
            margin-bottom: 5px;
        }
        h2 {
            font-size: 14px;
            color: #1e40af;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 5px;
        }
        .header-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
        }
        .user-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #1e40af;
        }
        .user-photo-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #1e40af;
        }
        .user-details h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #1e40af;
        }
        .user-details p {
            margin: 0;
            font-size: 11px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .period-blue { background-color: #eff6ff; border-left: 3px solid #3b82f6; }
        .period-emerald { background-color: #ecfdf5; border-left: 3px solid #10b981; }
        .period-violet { background-color: #f5f3ff; border-left: 3px solid #8b5cf6; }
        .period-amber { background-color: #fffbeb; border-left: 3px solid #f59e0b; }
        .period-rose { background-color: #fff1f2; border-left: 3px solid #f43f5e; }
        .period-cyan { background-color: #ecfeff; border-left: 3px solid #06b6d4; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-green { background-color: #dcfce7; color: #166534; }
        .badge-yellow { background-color: #fef9c3; color: #854d0e; }
        .text-red { color: #dc2626; font-weight: bold; }
        .text-green { color: #16a34a; font-weight: bold; }
    </style>
</head>
<body>
    <h1>KÁRDEX DE VACACIONES</h1>

    <!-- Información del Usuario -->
    <div class="header-info">
        @if($fotoRuta)
            <img src="{{ public_path($fotoRuta) }}" alt="Foto" class="user-photo"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="user-photo-placeholder" style="display:none;">
                <span style="font-size: 30px; color: #64748b;">👤</span>
            </div>
        @else
            <div class="user-photo-placeholder">
                <span style="font-size: 30px; color: #64748b;"></span>
            </div>
        @endif

        <div class="user-details">
            <h3>{{ $user->name }}</h3>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Fecha de Generación:</strong> {{ $fechaGeneracion }}</p>
        </div>
    </div>

    <!-- Tabla de Vacaciones -->
    @if(count($vacaciones) > 0)
        <h2>Detalle de Vacaciones Aceptadas</h2>
        <table>
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Tipo</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Días por Derecho</th>
                    <th>Días Disponibles</th>
                    <th>Días Solicitados</th>
                    <th>Días Restantes</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $periodColors = ['period-blue', 'period-emerald', 'period-violet', 'period-amber', 'period-rose', 'period-cyan'];
                @endphp
                @foreach($vacaciones as $index => $vacacion)
                    @php
                        $colorClass = $periodColors[$index % count($periodColors)];
                    @endphp
                    <tr class="{{ $colorClass }}">
                        <td>{{ $vacacion->periodo }}</td>
                        <td>{{ ucfirst($vacacion->tipo) }}</td>
                        <td>{{ \Carbon\Carbon::parse($vacacion->fecha_inicio)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($vacacion->fecha_fin)->format('d/m/Y') }}</td>
                        <td>{{ $vacacion->dias_por_derecho }}</td>
                        <td>{{ $vacacion->dias_disponibles }}</td>
                        <td>{{ $vacacion->dias_solicitados }}</td>
                        <td class="{{ ($vacacion->dias_disponibles - $vacacion->dias_solicitados) < 0 ? 'text-red' : 'text-green' }}">
                            {{ $vacacion->dias_disponibles - $vacacion->dias_solicitados }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Resumen por Periodo -->
        <h2>Resumen por Periodo</h2>
        <table>
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Días por Derecho</th>
                    <th>Días Solicitados</th>
                    <th>Días Restantes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumenPorPeriodo as $resumen)
                    <tr>
                        <td>{{ $resumen['periodo'] }}</td>
                        <td>{{ $resumen['dias_por_derecho'] }}</td>
                        <td>{{ $resumen['dias_solicitados'] }}</td>
                        <td class="{{ $resumen['dias_restantes'] < 0 ? 'text-red' : 'text-green' }}">
                            {{ $resumen['dias_restantes'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #64748b; padding: 20px;">
            No se encontraron solicitudes de vacaciones aceptadas para este usuario.
        </p>
    @endif

    <div class="footer">
        <p>Documento generado automáticamente el {{ $fechaGeneracion }}</p>
        <p>Sistema de Gestión Interna - Kárdex de Vacaciones</p>
    </div>
</body>
</html>
