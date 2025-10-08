<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\SolicitudBajas;
use App\Models\User;

class AuxcontController extends Controller
{
    public function listaFiniquitos()
    {
        $renuncias = SolicitudBajas::where('estatus', 'Aceptada')
            ->where('observaciones', 'Finiquito enviado a RH.')
            ->whereDate('fecha_baja', '>=', now()->subDays(30))
            ->orderBy('fecha_baja', 'desc')
            ->paginate(10);

        return view('auxcont.listaFiniquitos', compact('renuncias'));
    }

    public function subirCheque(Request $request, $id)
    {
        $request->validate([
            'archivo_cheque' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        try {
            $solicitud = SolicitudBajas::findOrFail($id);

            if ($request->hasFile('archivo_cheque')) {
                // Crear directorio si no existe
                $directorio = 'solicitudesBajas/' . $id;
                Storage::disk('public')->makeDirectory($directorio);

                // Generar nombre de archivo único
                $extension = $request->file('archivo_cheque')->getClientOriginalExtension();
                $nombreArchivo = 'cheque_' . date('Ymd_His') . '.' . $extension;
                $rutaCompleta = $directorio . '/' . $nombreArchivo;

                // Guardar archivo
                $rutaArchivo = $request->file('archivo_cheque')->storeAs($directorio, $nombreArchivo, 'public');

                // Actualizar registro en la base de datos
                $solicitud->update([
                    'arch_cheque' => $rutaArchivo,
                    'observaciones' => 'Cheque subido correctamente.',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Cheque guardado correctamente.',
                    'ruta' => Storage::url($rutaArchivo)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se encontró el archivo.'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el cheque: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarCheque(Request $request, $id)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
        ]);

        $solicitud = SolicitudBajas::findOrFail($id);

        if ($solicitud->arch_cheque) {
            Storage::delete($solicitud->arch_cheque);
        }

        $extension = $request->file('archivo')->getClientOriginalExtension();
        $nombreArchivo = Str::slug($solicitud->user->name ?? 'usuario') . '_' . now()->format('Ymd_His') . '.' . $extension;
        $ruta = $request->file('archivo')->storeAs("solicitudesBajas/{$id}", $nombreArchivo, 'public');

        $solicitud->update([
            'arch_cheque' => $ruta,
            'observaciones' => 'Cheque cancelado.',
        ]);

        return response()->json(['success' => true, 'message' => 'Cheque actualizado correctamente.']);
    }

    public function historialCheques(){
        return view ('auxcont.historialCheques');
    }

    public function eventualesList(){
        $users = User::where('rol', 'EVENTUAL')
            ->where('estatus', 'Activo')
            ->paginate(10);

        return view ('auxcont.eventualesList', compact('users'));
    }

    public function valesComida(){
        return view('auxcont.valesComida');
    }
}
