<?php

namespace App\Services;

use App\Models\InspeccionMensaje;
use App\Models\InspeccionRevisionCaso;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class InspeccionReporteSemanalService
{
    /**
     * @return array{
     *     inicio: CarbonImmutable,
     *     fin: CarbonImmutable,
     *     casos: Collection<int, array<string, mixed>>,
     *     total_casos: int,
     *     total_evidencias: int,
     *     total_unidades: int
     * }
     */
    public function generar(?string $semana = null): array
    {
        $inicio = CarbonImmutable::parse($semana ?: 'now')
            ->startOfWeek(CarbonImmutable::MONDAY)
            ->startOfDay();
        $fin = $inicio->endOfWeek(CarbonImmutable::SUNDAY)->endOfDay();

        $casosDeLaSemana = InspeccionMensaje::query()
            ->select('caso_id')
            ->where('incluido', true)
            ->groupBy('caso_id')
            ->havingRaw('MIN(fecha_mensaje) BETWEEN ? AND ?', [
                $inicio->toDateTimeString(),
                $fin->toDateTimeString(),
            ]);

        $casos = InspeccionRevisionCaso::query()
            ->where('estado', 'confirmado')
            ->whereNotNull('inspeccion_id')
            ->whereIn('id', $casosDeLaSemana)
            ->withMin([
                'mensajes as primera_evidencia_at' => fn ($query) => $query->where('incluido', true),
            ], 'fecha_mensaje')
            ->with([
                'unidadConfirmada:id,placas,marca,modelo',
                'inspeccion' => fn ($query) => $query->withCount('evidencias'),
                'revisor:id,name',
            ])
            ->orderBy('primera_evidencia_at')
            ->get()
            ->map(fn (InspeccionRevisionCaso $caso): array => [
                'caso_id' => $caso->id,
                'primera_evidencia_at' => CarbonImmutable::parse($caso->primera_evidencia_at),
                'unidad_id' => $caso->unidad_confirmada_id,
                'placa' => $caso->unidadConfirmada?->placas ?: 'Sin placa',
                'vehiculo' => trim(implode(' ', array_filter([
                    $caso->unidadConfirmada?->marca,
                    $caso->unidadConfirmada?->modelo,
                ]))) ?: 'Sin información',
                'evidencias' => (int) ($caso->inspeccion?->evidencias_count ?? 0),
                'inspeccion_id' => $caso->inspeccion_id,
                'resultado' => $caso->inspeccion?->resultado ?: 'Sin resultado',
                'confirmado_at' => $caso->confirmed_at,
                'revisor' => $caso->revisor?->name ?: 'Sin identificar',
            ]);

        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'casos' => $casos,
            'total_casos' => $casos->count(),
            'total_evidencias' => $casos->sum('evidencias'),
            'total_unidades' => $casos->pluck('unidad_id')->filter()->unique()->count(),
        ];
    }
}
