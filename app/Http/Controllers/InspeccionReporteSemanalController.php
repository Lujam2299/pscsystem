<?php

namespace App\Http\Controllers;

use App\Exports\InspeccionReporteSemanalExport;
use App\Services\InspeccionReporteSemanalService;
use App\Support\Authorization\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class InspeccionReporteSemanalController extends Controller
{
    public function xlsx(
        Request $request,
        InspeccionReporteSemanalService $reportes,
        InspeccionReporteSemanalExport $exportador,
    ): Response {
        Gate::authorize(Permission::INSPECTION_INBOX_VIEW);

        $datos = $request->validate([
            'semana' => ['nullable', 'date_format:Y-m-d'],
        ]);

        return $exportador->download($reportes->generar($datos['semana'] ?? null));
    }
}
