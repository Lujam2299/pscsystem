<?php

namespace App\Http\Controllers;

use App\Models\InspeccionMensajeArchivo;
use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InspeccionMensajeArchivoController extends Controller
{
    public function show(InspeccionMensajeArchivo $archivo): StreamedResponse
    {
        Gate::authorize(Permission::INSPECTION_INBOX_VIEW);
        abort_unless(Storage::disk($archivo->disk)->exists($archivo->path), 404);

        return Storage::disk($archivo->disk)->response($archivo->path, $archivo->nombre_original, [
            'Cache-Control' => 'private, max-age=300',
            'Content-Type' => $archivo->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
