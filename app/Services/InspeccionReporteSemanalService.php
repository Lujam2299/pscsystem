<?php

namespace App\Services;

use App\Models\InspeccionUnidad;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class InspeccionReporteSemanalService
{
    /**
     * @return array{
     *     inicio: CarbonImmutable,
     *     fin: CarbonImmutable,
     *     inspecciones: Collection<int, array<string, mixed>>,
     *     total_inspecciones: int,
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

        $primeraEvidenciaSql = <<<'SQL'
            SELECT MIN(im.fecha_mensaje)
            FROM inspeccion_mensajes im
            INNER JOIN inspeccion_revision_casos irc ON irc.id = im.caso_id
            WHERE irc.inspeccion_id = inspecciones_unidades.id
              AND im.incluido = 1
        SQL;
        $fechaReporteSql = "COALESCE(($primeraEvidenciaSql), inspecciones_unidades.fecha_inspeccion)";

        $inspecciones = InspeccionUnidad::query()
            ->select('inspecciones_unidades.*')
            ->selectRaw("$fechaReporteSql AS fecha_reporte")
            ->where('inspecciones_unidades.estado', 'validada')
            ->whereRaw("$fechaReporteSql BETWEEN ? AND ?", [
                $inicio->toDateTimeString(),
                $fin->toDateTimeString(),
            ])
            ->with([
                'unidad:id,placas,marca,modelo',
                'casoRecepcion:id,inspeccion_id,confirmed_at',
                'revisor:id,name',
            ])
            ->withCount('evidencias')
            ->orderBy('fecha_reporte')
            ->get()
            ->map(fn (InspeccionUnidad $inspeccion): array => [
                'caso_id' => $inspeccion->casoRecepcion?->id,
                'fecha_reporte' => CarbonImmutable::parse($inspeccion->fecha_reporte),
                'unidad_id' => $inspeccion->unidad_id,
                'placa' => $inspeccion->unidad?->placas ?: 'Sin placa',
                'vehiculo' => trim(implode(' ', array_filter([
                    $inspeccion->unidad?->marca,
                    $inspeccion->unidad?->modelo,
                ]))) ?: 'Sin información',
                'evidencias' => (int) $inspeccion->evidencias_count,
                'inspeccion_id' => $inspeccion->id,
                'resultado' => $inspeccion->resultado ?: 'Sin resultado',
                'validada_at' => $inspeccion->casoRecepcion?->confirmed_at ?? $inspeccion->updated_at,
                'revisor' => $inspeccion->revisor?->name ?: 'Sin identificar',
            ]);

        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'inspecciones' => $inspecciones,
            'total_inspecciones' => $inspecciones->count(),
            'total_evidencias' => $inspecciones->sum('evidencias'),
            'total_unidades' => $inspecciones->pluck('unidad_id')->filter()->unique()->count(),
        ];
    }
}
