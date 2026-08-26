<?php

namespace App\Http\Controllers;

use App\Exports\GpsOperationalReportExport;
use App\Services\GpsOperationalReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class GpsReportController extends Controller
{
    public function show(Request $request, GpsOperationalReportService $reports): JsonResponse
    {
        [$deviceIds, $from, $to] = $this->parameters($request);

        return response()->json($reports->generate($deviceIds, $from, $to));
    }

    public function xlsx(Request $request, GpsOperationalReportService $reports, GpsOperationalReportExport $export): Response
    {
        [$deviceIds, $from, $to] = $this->parameters($request);

        return $export->download($reports->generate($deviceIds, $from, $to));
    }

    public function pdf(Request $request, GpsOperationalReportService $reports): Response
    {
        [$deviceIds, $from, $to] = $this->parameters($request);
        $report = $reports->generate($deviceIds, $from, $to);

        return Pdf::loadView('monitoreo.reportes.gps-operativo-pdf', compact('report'))
            ->setPaper('a4', 'landscape')
            ->download('reporte-operativo-gps-'.now()->format('Ymd-His').'.pdf');
    }

    /** @return array{0: array<int, int>, 1: string, 2: string} */
    private function parameters(Request $request): array
    {
        $validated = $request->validate([
            'device_ids' => ['nullable', 'array', 'max:250'],
            'device_ids.*' => ['integer', 'min:1'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
        ]);
        $from = CarbonImmutable::parse($validated['from'])->utc();
        $to = CarbonImmutable::parse($validated['to'])->utc();
        if ($from->diffInSeconds($to) > 31 * 24 * 60 * 60) {
            throw ValidationException::withMessages(['to' => 'El periodo máximo permitido es de 31 días.']);
        }

        return [$validated['device_ids'] ?? [], $from->toIso8601String(), $to->toIso8601String()];
    }
}
