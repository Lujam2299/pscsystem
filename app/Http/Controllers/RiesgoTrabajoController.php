<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RiesgoTrabajo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class RiesgoTrabajoController extends Controller
{
    public function index()
    {

        return view('auxadmin.riesgosTrabajoList');
    }

    public function create(User $user)
    {
        return view('auxadmin.riesgosTrabajoForm', compact('user'));
    }

    public function store(Request $request, $id = null)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tipo_riesgo' => 'required|in:En el trabajo,En trayecto',
            'descripcion_observaciones' => 'nullable|string',
            'fecha' => 'nullable|date',
            'folio' => 'nullable|string|max:100',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:2048',
            'arch_alta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $userId = $request->user_id;
        $baseDir = "RiesgosTrabajo/{$userId}";

        $rutaArchivoPdf = null;
        if ($request->hasFile('archivo_pdf')) {
            $originalName = $request->file('archivo_pdf')->getClientOriginalName();
            $extension = $request->file('archivo_pdf')->getClientOriginalExtension();
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $rutaArchivoPdf = $request->file('archivo_pdf')->storeAs($baseDir, $fileName, 'public');
        }

        $rutaArchAlta = null;
        if ($request->hasFile('arch_alta')) {
            $originalName = $request->file('arch_alta')->getClientOriginalName();
            $extension = $request->file('arch_alta')->getClientOriginalExtension();
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $rutaArchAlta = $request->file('arch_alta')->storeAs($baseDir, $fileName, 'public');
        }

        RiesgoTrabajo::create([
            'user_id' => $userId,
            'tipo_riesgo' => $request->tipo_riesgo,
            'descripcion_observaciones' => $request->descripcion_observaciones,
            'fecha' => $request->fecha,
            'folio' => $request->folio,
            'ruta_archivo_pdf' => $rutaArchivoPdf ? 'storage/' . $rutaArchivoPdf : null,
            'arch_alta' => $rutaArchAlta ? 'storage/' . $rutaArchAlta : null,
        ]);

        return redirect()->route('aux.riesgosTrabajo')
            ->with('success', 'Riesgo de trabajo registrado exitosamente.');
    }

    public function showHistorialRiesgosTrabajo()
    {
        return view('auxadmin.historialRiesgosTrabajo');
    }

    public function actualizar(Request $request)
    {
        \Log::info('Valor recibido en id: ', ['id' => $request->id]);
        $request->validate([
            'id' => 'required|exists:riesgo_trabajos,id',
            'tipo_riesgo' => 'required|in:En el trabajo,En trayecto',
            'descripcion_observaciones' => 'nullable|string',
            'fecha' => 'nullable|date',
            'folio' => 'nullable|string|max:100',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:2048',
            'arch_alta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $riesgo = RiesgoTrabajo::findOrFail($request->id);
        $userId = $riesgo->user_id;
        $baseDir = "RiesgosTrabajo/{$userId}";

        $nuevaRutaPdf = $riesgo->ruta_archivo_pdf;
        if ($request->hasFile('archivo_pdf')) {
            // Eliminar archivo anterior si existe
            if ($riesgo->ruta_archivo_pdf) {
                $rutaAnterior = str_replace('storage/', '', $riesgo->ruta_archivo_pdf);
                if (Storage::disk('public')->exists($rutaAnterior)) {
                    Storage::disk('public')->delete($rutaAnterior);
                }
            }
            // Guardar nuevo archivo
            $originalName = $request->file('archivo_pdf')->getClientOriginalName();
            $extension = $request->file('archivo_pdf')->getClientOriginalExtension();
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $nuevaRutaPdf = 'storage/' . $request->file('archivo_pdf')->storeAs($baseDir, $fileName, 'public');
        }

        $nuevaRutaAlta = $riesgo->arch_alta;
        if ($request->hasFile('arch_alta')) {

            if ($riesgo->arch_alta) {
                $rutaAnterior = str_replace('storage/', '', $riesgo->arch_alta);
                if (Storage::disk('public')->exists($rutaAnterior)) {
                    Storage::disk('public')->delete($rutaAnterior);
                }
            }
            $originalName = $request->file('arch_alta')->getClientOriginalName();
            $extension = $request->file('arch_alta')->getClientOriginalExtension();
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $nuevaRutaAlta = 'storage/' . $request->file('arch_alta')->storeAs($baseDir, $fileName, 'public');
        }

        $riesgo->update([
            'tipo_riesgo' => $request->tipo_riesgo,
            'descripcion_observaciones' => $request->descripcion_observaciones,
            'fecha' => $request->fecha,
            'folio' => $request->folio,
            'ruta_archivo_pdf' => $nuevaRutaPdf,
            'arch_alta' => $nuevaRutaAlta,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Riesgo actualizado correctamente.',
        ]);
    }

}
