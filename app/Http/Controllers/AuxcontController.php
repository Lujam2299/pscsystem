<?php

namespace App\Http\Controllers;

use App\Exports\RegistrosValesComidasExport;
use App\Exports\RegistrosEventualesExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\SolicitudBajas;
use App\Models\ValesComida;
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
        $registros = ValesComida::where('estatus', 'En Proceso')
        ->paginate(10);
        return view('auxcont.valesComida', compact('registros'));
    }

    public function aceptarSolicitudVales(Request $request, $id):JsonResponse
    {
        $vale = ValesComida::findOrFail($id);
        $vale->estatus = 'Aceptada';
        $vale->observaciones = 'Pendiente subir archivos';
        $vale->save();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud respondida correctamente'
        ]);
    }

    public function rechazarSolicitudVales(Request $request, $id): JsonResponse
    {
        $vale = ValesComida::findOrFail($id);
        $vale->estatus = 'Rechazada';
        $vale->observaciones = $request->observaciones ?? '';
        $vale->save();

        return response()->json([
            'success' => true,
            'message' => 'Vale rechazado correctamente'
        ]);
    }

    public function verComprobantes(){
        $vales = ValesComida::with('comprobantes', 'user')
            ->where('estatus', 'Comprobación En Revisión')
            ->paginate(10);

        return view('auxcont.verComprobantes', compact('vales'));
    }

    public function aprobarComprobacion(Request $request, $id)
{
    $vale = ValesComida::findOrFail($id);
    $vale->estatus = 'Comprobación Aprobada';
    $vale->save();

    return response()->json([
        'success' => true,
        'message' => 'Comprobación aprobada correctamente'
    ]);
}

    public function rechazarComprobacion(Request $request)
    {
        $vale = ValesComida::findOrFail($request->route('id'));
        $vale->estatus = 'Comprobación Rechazada';
        $vale->observaciones = $request->input('motivo') ?? '';
        $vale->save();

        return response()->json([
            'success' => true,
            'message' => 'Comprobación rechazada correctamente'
        ]);
    }

    public function historialValesComida(){
        return view('auxcont.historialVales');
    }

    public function obtenerComprobantes($id)
    {
        $vale = ValesComida::with('comprobantes')->findOrFail($id);

        return response()->json([
            'comprobantes' => $vale->comprobantes
        ]);
    }

    public function exportarValesComida(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'monto_desde' => 'nullable|numeric|min:0',
            'monto_hasta' => 'nullable|numeric|min:0|gte:monto_desde',
            'estatus' => 'nullable|string|in:En Proceso,Aceptada,Comprobación Pendiente,Comprobación En Revisión,Comprobación Aprobada,Comprobación Rechazada'
        ]);

        $search = $request->search;
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;
        $monto_desde = $request->monto_desde;
        $monto_hasta = $request->monto_hasta;
        $estatus = $request->estatus;

        return (new RegistrosValesComidasExport(
            $search, $fecha_desde, $fecha_hasta, $monto_desde, $monto_hasta, $estatus
        ))->generateFile();
    }

    public function exportarRegistrosEventuales(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'tipo_pago' => 'nullable|in:nomina,efectivo',
            'subpunto_id' => 'nullable|exists:subpuntos,id',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde'
        ]);

        $search = $request->search;
        $tipo_pago = $request->tipo_pago;
        $subpunto_id = $request->subpunto_id;
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        return (new RegistrosEventualesExport($search, $tipo_pago, $subpunto_id, $fecha_desde, $fecha_hasta))->generateFile();
    }

}
