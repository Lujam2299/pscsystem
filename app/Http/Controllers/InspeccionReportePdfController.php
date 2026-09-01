<?php

namespace App\Http\Controllers;

use App\Models\InspeccionEvidencia;
use App\Models\InspeccionUnidad;
use App\Services\InspeccionReporteSemanalService;
use App\Support\Authorization\Permission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InspeccionReportePdfController extends Controller
{
    public function ejecutivo(Request $request, InspeccionReporteSemanalService $reportes): Response
    {
        Gate::authorize(Permission::INSPECTION_INBOX_VIEW);
        $reporte = $reportes->generar($this->semana($request));

        return Pdf::loadView('monitoreo.reportes.inspecciones-semanal-ejecutivo-pdf', compact('reporte'))
            ->setPaper('a4', 'landscape')
            ->download($this->nombreSemanal('ejecutivo', $reporte));
    }

    public function incidencias(Request $request, InspeccionReporteSemanalService $reportes): Response
    {
        Gate::authorize(Permission::INSPECTION_INBOX_VIEW);
        $reporte = $reportes->generar($this->semana($request));
        $incidencias = $reporte['inspecciones']
            ->filter(fn (array $inspeccion): bool => $this->esIncidencia($inspeccion))
            ->values();

        $representativas = InspeccionEvidencia::query()
            ->whereIn('inspeccion_id', $incidencias->pluck('inspeccion_id'))
            ->orderBy('inspeccion_id')
            ->orderBy('orden')
            ->get()
            ->unique('inspeccion_id')
            ->keyBy('inspeccion_id');

        $reporte['inspecciones'] = $incidencias->map(function (array $inspeccion) use ($representativas): array {
            $evidencia = $representativas->get($inspeccion['inspeccion_id']);
            $inspeccion['imagen'] = $evidencia ? $this->imagenDataUri($evidencia) : null;

            return $inspeccion;
        });
        $reporte['total_incidencias'] = $reporte['inspecciones']->count();

        return Pdf::loadView('monitoreo.reportes.inspecciones-semanal-incidencias-pdf', compact('reporte'))
            ->setPaper('a4', 'portrait')
            ->download($this->nombreSemanal('incidencias', $reporte));
    }

    public function expediente(InspeccionUnidad $inspeccion): Response
    {
        Gate::authorize(Permission::INSPECTIONS_VIEW);
        abort_unless($inspeccion->estado === 'validada', 404);

        $inspeccion->load([
            'unidad:id,placas,marca,modelo',
            'evidencias',
            'casoRecepcion:id,inspeccion_id,confirmed_at',
            'revisor:id,name',
        ]);
        $imagenes = $inspeccion->evidencias
            ->map(fn (InspeccionEvidencia $evidencia): array => [
                'nombre' => $evidencia->nombre_original,
                'imagen' => $this->imagenDataUri($evidencia),
            ]);

        return Pdf::loadView(
            'monitoreo.reportes.inspeccion-expediente-pdf',
            compact('inspeccion', 'imagenes'),
        )->setPaper('a4', 'portrait')
            ->download('expediente-inspeccion-'.$inspeccion->id.'.pdf');
    }

    private function semana(Request $request): ?string
    {
        return $request->validate([
            'semana' => ['nullable', 'date_format:Y-m-d'],
        ])['semana'] ?? null;
    }

    private function esIncidencia(array $inspeccion): bool
    {
        return Str::slug((string) $inspeccion['resultado'], '_') !== 'sin_novedad'
            || filled($inspeccion['observaciones']);
    }

    private function imagenDataUri(InspeccionEvidencia $evidencia): ?string
    {
        if (! in_array($evidencia->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        try {
            $disk = Storage::disk($evidencia->disk);
            if (! $disk->exists($evidencia->path)) {
                return null;
            }

            return 'data:'.$evidencia->mime_type.';base64,'.base64_encode($disk->get($evidencia->path));
        } catch (Throwable) {
            return null;
        }
    }

    private function nombreSemanal(string $tipo, array $reporte): string
    {
        return sprintf(
            'reporte-evidencias-%s-%s-al-%s.pdf',
            $tipo,
            $reporte['inicio']->format('Ymd'),
            $reporte['fin']->format('Ymd'),
        );
    }
}
