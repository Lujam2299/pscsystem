<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BajaAcuse;
use App\Models\SolicitudBajas;
use Illuminate\Support\Facades\Storage;

class BajaAcuseController extends Controller
{
      public function index()
    {
        $bajas = SolicitudBajas::with('usuario')
        ->where('estatus', 'Aceptada')
        ->where('created_at', '>=', now()->subDays(15))
        ->orderByDesc('created_at')
        ->paginate(10);

    return view('auxadmin.acusesBajas', compact('bajas'));
    }

    public function upload(Request $request, SolicitudBajas $solicitudBaja)
    {
        $request->validate([
            'archivo' => 'required|mimes:pdf|max:2048',
        ]);

        if ($solicitudBaja->acuse) {
            return back()->with('error', 'Ya existe un acuse para esta baja.');
        }

        $file = $request->file('archivo');
        $path = $file->storeAs("acuses_bajas/{$solicitudBaja->id}", $file->getClientOriginalName(), 'public');

        BajaAcuse::create([
            'solicitud_baja_id' => $solicitudBaja->id,
            'archivo' => $path,
        ]);

        return back()->with('success', 'Acuse subido correctamente.');
    }

    public function historialAcusesBajas(){
        return view('auxadmin.historialAcusesBajas');
    }
}
