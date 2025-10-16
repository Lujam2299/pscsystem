<?php

namespace App\Http\Controllers;

use App\Models\Eventuales;
use App\Models\Punto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\ValesComida;
use App\Models\ComprobanteVale;
use App\Models\User;

class OperacionesController extends Controller
{
    public function eventualesList(){
        $users = User::where('rol', 'EVENTUAL')
            ->where('estatus', 'Activo')
            ->paginate(10);
        return view('operaciones.eventuales', compact('users'));
    }

    public function storeRegistroEventual(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'fecha' => 'required|date',
                'subpunto_id' => 'required|exists:subpuntos,id',
                'turnos' => 'required|array|min:1',
                'turnos.*' => 'in:dia,tarde,noche',
                'tipo_pago' => 'required|in:nomina,eventual',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar el registro'
                ], 422);
            }

            $data = $validator->validated();
            Eventuales::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Registro guardado correctamente'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al registrar eventual: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pagosEventuales(){
        $registros = Eventuales::where('arch_pago', null)
            ->where('fecha', '>=', now()->subDays(15))
            ->paginate(10);
        return view('operaciones.pagosEventuales', compact('registros'));
    }

    public function subirPagoEventual(Request $request, $id)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $registro = Eventuales::findOrFail($id);

        $rutaRelativa = $request->file('archivo')->store("PagoEventuales/{$id}", 'public');

        $registro->arch_pago = "storage/" . $rutaRelativa;
        $registro->save();

        return response()->json([
            'success' => true,
            'message' => 'Comprobante subido correctamente'
        ]);
    }

    public function historialPagosEventuales(){
        return view('operaciones.historialPagosEventuales');
    }

    public function valesIndex(){
        return view('operaciones.valesComidas');
    }

    public function createValeComida(){
        return view('operaciones.createValeComida');
    }

    public function valesPendientes(){
        $vales = ValesComida::where('estatus', 'Aceptada')
            ->where('observaciones', 'Pendiente subir archivos')
            ->paginate(10);

        return view('operaciones.valesPendientes', compact('vales'));
    }

    public function mostrarFormularioComprobantes($id)
    {
        $vale = ValesComida::with('comprobantes')->findOrFail($id);

        if ($vale->estatus !== 'Aceptada') {
            abort(403, 'No se pueden subir comprobantes en este estatus');
        }

        return view('operaciones.formularioComprobantes', compact('vale'));
    }

public function subirComprobantes(Request $request, $id)
{
    $vale = ValesComida::findOrFail($id);

    $request->validate([
        'archivos' => 'required|array|min:1',
        'archivos.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        'montos' => 'required|array|min:1',
        'montos.*' => 'required|numeric|min:0.01',
        'comprobantes' => 'required|array',
        'comprobantes.*.usuario_id' => 'required|exists:users,id'
    ]);

    if (count($request->archivos) !== count($request->montos)) {
        return back()->withErrors(['error' => 'El número de archivos debe coincidir con el número de montos']);
    }

    $sumaMontos = array_sum($request->montos);
    if (abs($sumaMontos - $vale->monto) > 0.01) {
        return back()->withErrors(['error' => 'La suma de los montos debe ser igual al monto del vale: $' . number_format($vale->monto, 2)]);
    }

    foreach ($request->archivos as $index => $archivo) {
        $ruta = $archivo->store('comprobantes-vales/' . $vale->id, 'public');
        ComprobanteVale::create([
            'vale_comida_id' => $vale->id,
            'archivo' => 'storage/' . $ruta,
            'monto' => $request->montos[$index],
            'user_id' => $request->comprobantes[$index]['usuario_id'] // ✅ Guardar usuario por comprobante
        ]);
    }

    $vale->estatus = 'Comprobación En Revisión';
    $vale->save();

    return redirect()->route('operaciones.valesPendientes')->with('success', 'Comprobantes subidos correctamente');
}
}
