<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 28px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { color: #0f766e; font-size: 19px; margin: 0 0 4px; }
        .periodo { color: #4b5563; margin: 0 0 14px; }
        .metricas { margin-bottom: 14px; width: 100%; }
        .metricas td { background: #ecfdf5; border: 1px solid #a7f3d0; padding: 8px; text-align: center; width: 33.33%; }
        .numero { color: #065f46; display: block; font-size: 18px; font-weight: bold; }
        table.detalle { border-collapse: collapse; width: 100%; }
        .detalle th { background: #0f766e; color: #fff; font-weight: bold; }
        .detalle th, .detalle td { border: 1px solid #d1d5db; padding: 5px; text-align: left; vertical-align: top; }
        .detalle tr:nth-child(even) { background: #f9fafb; }
        .centro { text-align: center !important; }
        .vacio { color: #6b7280; padding: 24px !important; text-align: center !important; }
    </style>
</head>
<body>
    <h1>Reporte ejecutivo semanal de evidencias</h1>
    <p class="periodo">
        Periodo: {{ $reporte['inicio']->format('d/m/Y') }} al {{ $reporte['fin']->format('d/m/Y') }} ·
        Generado: {{ now()->format('d/m/Y H:i') }}
    </p>

    <table class="metricas">
        <tr>
            <td><span class="numero">{{ $reporte['total_inspecciones'] }}</span>Inspecciones validadas</td>
            <td><span class="numero">{{ $reporte['total_evidencias'] }}</span>Evidencias</td>
            <td><span class="numero">{{ $reporte['total_unidades'] }}</span>Unidades diferentes</td>
        </tr>
    </table>

    <table class="detalle">
        <thead>
            <tr>
                <th>Fecha</th><th>Placa</th><th>Vehículo</th><th>Resultado</th>
                <th class="centro">Evidencias</th><th>Observación</th><th>Revisor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reporte['inspecciones'] as $inspeccion)
                <tr>
                    <td>{{ $inspeccion['fecha_reporte']->format('d/m/Y H:i') }}</td>
                    <td>{{ $inspeccion['placa'] }}</td>
                    <td>{{ $inspeccion['vehiculo'] }}</td>
                    <td>{{ str($inspeccion['resultado'])->replace('_', ' ')->title() }}</td>
                    <td class="centro">{{ $inspeccion['evidencias'] }}</td>
                    <td>{{ str($inspeccion['observaciones'] ?: 'Sin observaciones')->limit(150) }}</td>
                    <td>{{ $inspeccion['revisor'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="vacio">No hay inspecciones validadas en esta semana.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
