<?php

namespace App\Http\Controllers;

use App\Models\Eventuales;
use App\Models\Punto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
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
                'tipo_pago' => 'required|in:nomina,efectivo',
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
            \Log::error('Error al registrar eventual: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el registro'
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

        $directorio = "PagoEventuales/{$id}";
        $archivo = $request->file('archivo');
        $nombreArchivo = Str::random(20) . '.' . $archivo->getClientOriginalExtension();

        $ruta = $archivo->storeAs($directorio, $nombreArchivo, 'public');

        $registro->arch_pago = $ruta;
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
}
