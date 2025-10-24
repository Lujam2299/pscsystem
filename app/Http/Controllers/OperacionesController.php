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
        $rules = [
            'user_id' => 'required|exists:users,id',
            'fecha' => 'required|date',
            'subpunto_id' => 'required|exists:subpuntos,id',
            'turnos' => 'required|array|min:1',
            'turnos.*' => 'in:dia,tarde,noche',
            'tipo_pago' => 'required|in:nomina,eventual',
            'tipo_servicio' => 'required|in:12 Horas,24 horas,36 Horas',
            'motivo' => 'required|in:Falta de plantilla,Faltas de elementos,Vacaciones de elementos,Otro',
        ];

        if (in_array($request->motivo, ['Faltas de elementos', 'Vacaciones de elementos'])) {
            $rules['elemento_relacionado_id'] = 'required|exists:users,id,estatus,Activo';
        } else {
            $rules['elemento_relacionado_id'] = 'nullable';
        }

        if ($request->motivo === 'Otro') {
            $rules['observaciones'] = 'required|string|min:3|max:500';
        } else {
            $rules['observaciones'] = 'nullable|string|max:500';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el registro'
            ], 422);
        }

        $data = $validator->validated();

        if (!in_array($data['motivo'], ['Faltas de elementos', 'Vacaciones de elementos'])) {
            $data['elemento_relacionado_id'] = null;
        }

        if ($data['motivo'] !== 'Otro') {
            $data['observaciones'] = null;
        }

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
            'message' => 'Error al guardar el registro'
        ], 500);
    }
}

    public function pagosEventuales(){
        $registros = Eventuales::where('arch_pago', null)
            ->where('tipo_pago', 'eventual')
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

    public function show($id)
    {
        try {
            $registro = Eventuales::with(['user', 'elementoRelacionado'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $registro->id,
                    'user_name' => $registro->user?->name ?? 'Usuario eliminado',
                    'fecha_formateada' => \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y'),
                    'turnos' => $registro->turnos,
                    'tipo_servicio' => $registro->tipo_servicio,
                    'motivo' => $registro->motivo,
                    'tipo_pago' => $registro->tipo_pago,
                    'elemento_relacionado_name' => $registro->elementoRelacionado?->name,
                    'observaciones' => $registro->observaciones,
                    'arch_pago' => $registro->arch_pago,
                    'arch_pago_url' => $registro->arch_pago ? asset($registro->arch_pago) : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registro no encontrado'
            ], 404);
        }
    }
}
