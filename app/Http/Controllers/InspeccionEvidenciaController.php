<?php

namespace App\Http\Controllers;

use App\Models\InspeccionEvidencia;
use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InspeccionEvidenciaController extends Controller
{
    public function show(InspeccionEvidencia $evidencia): StreamedResponse
    {
        Gate::authorize(Permission::INSPECTIONS_VIEW);

        abort_unless(Storage::disk($evidencia->disk)->exists($evidencia->path), 404);

        return Storage::disk($evidencia->disk)->response(
            $evidencia->path,
            $evidencia->nombre_original,
            [
                'Content-Type' => $evidencia->mime_type,
                'Content-Disposition' => 'inline; filename="'.addslashes($evidencia->nombre_original).'"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=300',
            ],
        );
    }
}
