<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 32px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #b45309; font-size: 19px; margin: 0 0 4px; }
        .periodo { color: #4b5563; margin: 0 0 14px; }
        .resumen { background: #fffbeb; border: 1px solid #fcd34d; margin-bottom: 14px; padding: 9px; }
        .incidencia { border: 1px solid #d1d5db; margin-bottom: 14px; padding: 10px; page-break-inside: avoid; }
        .titulo { color: #92400e; font-size: 13px; font-weight: bold; margin-bottom: 5px; }
        .datos { color: #4b5563; margin-bottom: 7px; }
        .observaciones { background: #f9fafb; border-left: 3px solid #f59e0b; padding: 7px; }
        .evidencia { margin-top: 9px; text-align: center; }
        .evidencia img { max-height: 310px; max-width: 100%; }
        .sin-imagen { color: #6b7280; font-style: italic; margin-top: 8px; }
        .vacio { color: #6b7280; padding: 30px; text-align: center; }
    </style>
</head>
<body>
    <h1>Reporte semanal de incidencias</h1>
    <p class="periodo">
        Periodo: {{ $reporte['inicio']->format('d/m/Y') }} al {{ $reporte['fin']->format('d/m/Y') }} ·
        Generado: {{ now()->format('d/m/Y H:i') }}
    </p>
    <div class="resumen">
        <strong>{{ $reporte['total_incidencias'] }}</strong> inspección(es) validada(s) con observaciones o resultado distinto de “Sin novedad”.
        Se muestra como máximo una evidencia por inspección.
    </div>

    @forelse ($reporte['inspecciones'] as $inspeccion)
        <div class="incidencia">
            <div class="titulo">{{ $inspeccion['placa'] }} · Inspección #{{ $inspeccion['inspeccion_id'] }}</div>
            <div class="datos">
                {{ $inspeccion['fecha_reporte']->format('d/m/Y H:i') }} · {{ $inspeccion['vehiculo'] }} ·
                {{ str($inspeccion['resultado'])->replace('_', ' ')->title() }} · Revisor: {{ $inspeccion['revisor'] }}
            </div>
            <div class="observaciones"><strong>Observaciones:</strong> {{ $inspeccion['observaciones'] ?: 'Sin observaciones registradas.' }}</div>
            @if ($inspeccion['imagen'])
                <div class="evidencia"><img src="{{ $inspeccion['imagen'] }}" alt="Evidencia representativa"></div>
            @else
                <div class="sin-imagen">Sin evidencia fotográfica disponible.</div>
            @endif
        </div>
    @empty
        <div class="vacio">No hay incidencias entre las inspecciones validadas de esta semana.</div>
    @endforelse
</body>
</html>
