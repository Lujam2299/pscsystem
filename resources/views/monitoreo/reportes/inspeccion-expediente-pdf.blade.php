<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 32px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #1d4ed8; font-size: 19px; margin: 0 0 4px; }
        h2 { border-bottom: 1px solid #bfdbfe; color: #1e40af; font-size: 13px; margin: 17px 0 8px; padding-bottom: 4px; }
        .subtitulo { color: #4b5563; margin: 0 0 14px; }
        .datos { border-collapse: collapse; width: 100%; }
        .datos th, .datos td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        .datos th { background: #eff6ff; width: 18%; }
        .observaciones { background: #f9fafb; border: 1px solid #d1d5db; min-height: 45px; padding: 8px; }
        .evidencia { border: 1px solid #d1d5db; margin-bottom: 14px; padding: 8px; page-break-inside: avoid; text-align: center; }
        .evidencia img { max-height: 520px; max-width: 100%; }
        .nombre { color: #4b5563; font-size: 9px; margin-bottom: 6px; text-align: left; }
        .no-disponible { color: #6b7280; font-style: italic; padding: 20px; }
    </style>
</head>
<body>
    <h1>Expediente de inspección #{{ $inspeccion->id }}</h1>
    <p class="subtitulo">Documento generado el {{ now()->format('d/m/Y H:i') }}</p>

    <table class="datos">
        <tr><th>Placa</th><td>{{ $inspeccion->unidad?->placas ?: 'Sin placa' }}</td><th>Vehículo</th><td>{{ trim(($inspeccion->unidad?->marca ?? '').' '.($inspeccion->unidad?->modelo ?? '')) ?: 'Sin información' }}</td></tr>
        <tr><th>Fecha</th><td>{{ $inspeccion->fecha_inspeccion?->format('d/m/Y H:i') }}</td><th>Tipo</th><td>{{ str($inspeccion->tipo)->replace('_', ' ')->title() }}</td></tr>
        <tr><th>Resultado</th><td>{{ str($inspeccion->resultado)->replace('_', ' ')->title() }}</td><th>Estado</th><td>{{ str($inspeccion->estado)->title() }}</td></tr>
        <tr><th>Origen</th><td>{{ str($inspeccion->origen)->replace('_', ' ')->title() }}</td><th>Revisor</th><td>{{ $inspeccion->revisor?->name ?: 'Sin identificar' }}</td></tr>
        <tr><th>Caso</th><td>{{ $inspeccion->casoRecepcion?->id ? '#'.$inspeccion->casoRecepcion->id : 'Captura directa' }}</td><th>Evidencias</th><td>{{ $inspeccion->evidencias->count() }}</td></tr>
    </table>

    <h2>Observaciones</h2>
    <div class="observaciones">{{ $inspeccion->observaciones ?: 'Sin observaciones registradas.' }}</div>

    <h2>Evidencia fotográfica</h2>
    @forelse ($imagenes as $evidencia)
        <div class="evidencia">
            <div class="nombre">{{ $evidencia['nombre'] }}</div>
            @if ($evidencia['imagen'])
                <img src="{{ $evidencia['imagen'] }}" alt="Evidencia de inspección">
            @else
                <div class="no-disponible">La evidencia no está disponible para incorporarse al documento.</div>
            @endif
        </div>
    @empty
        <div class="no-disponible">Esta inspección no tiene evidencia fotográfica.</div>
    @endforelse
</body>
</html>
