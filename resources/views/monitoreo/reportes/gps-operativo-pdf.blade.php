<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { color: #1e3a8a; font-size: 18px; margin-bottom: 4px; }
        p { margin: 0 0 12px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #1e3a8a; color: #fff; font-weight: bold; }
        th, td { border: 1px solid #cbd5e1; padding: 5px; text-align: right; }
        th:first-child, td:first-child { text-align: left; }
        tr:nth-child(even) { background: #f8fafc; }
    </style>
</head>
<body>
    <h1>Reporte operativo GPS</h1>
    <p>Periodo: {{ \Carbon\Carbon::parse($report['from'])->format('d/m/Y H:i') }} — {{ \Carbon\Carbon::parse($report['to'])->format('d/m/Y H:i') }}</p>
    <table>
        <thead><tr><th>Unidad</th><th>Km</th><th>Máx.</th><th>Prom.</th><th>Motor h</th><th>Viajes</th><th>Paradas</th><th>Detenida h</th><th>Descon.</th><th>Excesos</th><th>Límite</th></tr></thead>
        <tbody>
        @foreach($report['rows'] as $row)
            <tr>
                <td>{{ $row['device_name'] }}</td><td>{{ $row['distance_km'] }}</td><td>{{ $row['max_speed_kmh'] }}</td><td>{{ $row['average_speed_kmh'] }}</td>
                <td>{{ $row['engine_hours'] }}</td><td>{{ $row['trips_count'] }}</td><td>{{ $row['stops_count'] }}</td><td>{{ $row['stopped_hours'] }}</td>
                <td>{{ $row['offline_events'] }}</td><td>{{ $row['overspeed_events'] + $row['trips_over_limit'] }}</td><td>{{ $row['speed_limit_kmh'] ?? 'N/D' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
