<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Operativo Misión #{{ $mision->id }}</title>
    <style>
        @page { margin: 14mm 10mm; size: letter portrait; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 8.5pt; color: #111827; line-height: 1.35; }
        h1 { font-size: 15pt; margin: 0 0 4px; }
        h2 { font-size: 11pt; margin: 16px 0 8px; padding-bottom: 4px; border-bottom: 2px solid #d1d5db; }
        h3 { font-size: 9.5pt; margin: 0 0 4px; }
        .header { border-bottom: 3px solid #059669; padding-bottom: 9px; margin-bottom: 12px; }
        .muted { color: #6b7280; }
        .cards { width: 100%; border-collapse: collapse; margin: 10px 0 14px; }
        .cards td { width: 25%; border: 1px solid #e5e7eb; padding: 7px; text-align: center; }
        .label { font-size: 7pt; color: #6b7280; font-weight: bold; text-transform: uppercase; }
        .value { font-size: 11pt; font-weight: bold; margin-top: 2px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data th { background: #f3f4f6; color: #374151; font-size: 7pt; text-transform: uppercase; text-align: left; padding: 6px; border: 1px solid #e5e7eb; }
        table.data td { padding: 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        .right { text-align: right; }
        .box { border: 1px solid #e5e7eb; padding: 8px; margin-bottom: 8px; background: #f9fafb; page-break-inside: avoid; }
        .pre { white-space: pre-line; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte Operativo - Misión #{{ $mision->id }}</h1>
        <div class="muted">
            Cliente: {{ $mision->cliente ?? 'No definido' }} ·
            Periodo: {{ $mision->fecha_inicio ? \Carbon\Carbon::parse($mision->fecha_inicio)->format('d/m/Y') : '-' }}
            -
            {{ $mision->fecha_fin ? \Carbon\Carbon::parse($mision->fecha_fin)->format('d/m/Y') : '-' }}
        </div>
        <div class="muted">Generado: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table class="cards">
        <tr>
            <td><div class="label">Estado</div><div class="value">{{ $mision->estado_normalizado }}</div></td>
            <td><div class="label">Itinerarios</div><div class="value">{{ count($eventosPlanos) }}</div></td>
            <td><div class="label">Gastos</div><div class="value">${{ number_format($totalGastos, 2) }}</div></td>
            <td><div class="label">Cierres</div><div class="value">{{ $cierres->count() }}</div></td>
        </tr>
    </table>

    <h2>Revisión administrativa</h2>
    <table class="data">
        <tr>
            <th>Estado de revisión</th>
            <th>Revisado por</th>
            <th>Fecha de revisión</th>
        </tr>
        <tr>
            <td>{{ $mision->revision_estado_normalizado }}</td>
            <td>{{ $mision->revisionUser?->name ?? '-' }}</td>
            <td>{{ $mision->revision_at ? $mision->revision_at->format('d/m/Y H:i') : '-' }}</td>
        </tr>
        @if(filled($mision->revision_observaciones))
            <tr>
                <th colspan="3">Observaciones internas</th>
            </tr>
            <tr>
                <td colspan="3" class="pre">{{ $mision->revision_observaciones }}</td>
            </tr>
        @endif
    </table>

    <h2>Datos generales</h2>
    <table class="data">
        <tr>
            <th>Tipo de servicio</th>
            <th>Agentes</th>
        </tr>
        <tr>
            <td>{{ $mision->tipo_servicio ?? 'No definido' }}</td>
            <td>{{ $agentes->pluck('name')->implode(', ') ?: 'Sin agentes' }}</td>
        </tr>
    </table>

    <h2>Itinerario cronológico</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Agente</th>
                <th>Descripción</th>
                <th>Ubicación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($eventosPlanos as $evento)
                <tr>
                    <td>{{ $evento['fecha'] ?? '-' }}</td>
                    <td>{{ $evento['hora'] ?? '-' }}</td>
                    <td>{{ $evento['user_name'] }}</td>
                    <td>{{ $evento['descripcion'] ?? '-' }}</td>
                    <td>{{ $evento['ubicacion'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No hay itinerarios registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Gastos</h2>
    <p class="muted">Viáticos: ${{ number_format($totalViaticos, 2) }} · Gasolina: ${{ number_format($totalGasolina, 2) }} · Total: ${{ number_format($totalGastos, 2) }}</p>
    <table class="data">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Agente</th>
                <th>Tipo</th>
                <th>Categoría</th>
                <th class="right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($gastos as $gasto)
                <tr>
                    <td>{{ $gasto->Fecha ? \Carbon\Carbon::parse($gasto->Fecha)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $gasto->user?->name ?? $agentesNombres[$gasto->user_id] ?? 'Agente #' . $gasto->user_id }}</td>
                    <td>{{ $gasto->Tipo }}</td>
                    <td>{{ $gasto->categoria_label ?? $gasto->Categoria ?? '-' }}</td>
                    <td class="right">${{ number_format((float) $gasto->Monto, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No hay gastos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Cierres operativos</h2>
    @forelse($cierres as $cierre)
        <div class="box">
            <h3>{{ $cierre->fecha ? $cierre->fecha->format('d/m/Y') : 'Sin fecha' }} · {{ $cierre->user?->name ?? 'Escolta #' . $cierre->user_id }}</h3>
            <div class="pre">{{ $cierre->resumen }}</div>
            @foreach(['novedades' => 'Novedades', 'incidencias' => 'Incidencias', 'pendientes' => 'Pendientes', 'observaciones' => 'Observaciones'] as $campo => $label)
                @if(filled($cierre->{$campo}))
                    <p><strong>{{ $label }}:</strong></p>
                    <div class="pre">{{ $cierre->{$campo} }}</div>
                @endif
            @endforeach
        </div>
    @empty
        <p class="muted">No hay cierres operativos registrados.</p>
    @endforelse
</body>
</html>
