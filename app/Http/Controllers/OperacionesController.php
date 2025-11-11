<?php

namespace App\Http\Controllers;

use App\Models\Eventuales;
use App\Models\Punto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\ValesComida;
use App\Models\ComprobanteVale;
use App\Models\User;
use App\Models\Subpunto;
use App\Models\Asistencia;

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

    public function asistenciaDiaria()
    {
        $subpuntosMap = $this->getSubpuntosPorPunto();
        $puntosConAsistenciaHoy = Asistencia::whereDate('fecha', now()->toDateString())
            ->pluck('punto')
            ->map(fn($p) => trim($p))
            ->toArray();

        return view('operaciones.asistenciaDiaria', compact('subpuntosMap', 'puntosConAsistenciaHoy'));
    }

    public function listaAsistencia(string $punto)
{
    $punto = urldecode($punto);


    $yaRegistrado = \App\Models\Asistencia::where('punto', $punto)
        ->whereDate('fecha', now()->toDateString())
        ->exists();

    $elementos = \App\Models\User::where('estatus', 'Activo')
        ->where('rol', 'GUARDIA')
        ->where('punto', $punto)
        ->with('solicitudAlta.documentacion')
        ->orderBy('name')
        ->get();

    return view('operaciones.lista-asistencia', compact('elementos', 'punto', 'yaRegistrado'));
}

public function guardarAsistencias(Request $request)
{
    $validated = $request->validate([
        'asistencias' => 'required|array',
        'asistencias.*' => 'integer',
        'fecha_registro' => 'required|date|date_format:Y-m-d',
        'foto_evidencia' => 'nullable|array',
        'foto_evidencia.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        'observaciones' => 'nullable|string|max:255',
        'coberturas' => 'nullable|array',
        'coberturas.*' => 'required|string',
        'punto_seleccionado' => 'required|string',
    ]);

    $user = Auth::user();
    $puntoSeleccionado = $request->input('punto_seleccionado');
    $fechaRegistro = $request->input('fecha_registro');
    $horaRegistro = now('America/Mexico_City')->toTimeString();

    $todosUsuarios = \App\Models\User::where('estatus', 'Activo')
        ->where('rol', 'GUARDIA')
        ->where('punto', $puntoSeleccionado)
        ->pluck('id')
        ->toArray();

    $asistencias = $request->input('asistencias', []);
    $faltas = array_values(array_diff($todosUsuarios, $asistencias));

    $coberturasRaw = $request->input('coberturas', []);
    $coberturas = array_map(function ($item) {
        return json_decode($item, true);
    }, $coberturasRaw);

    // 📁 Subir archivos a carpeta temporal y guardar rutas (no objetos)
    $rutasTemporales = [];
    if ($request->hasFile('foto_evidencia')) {
        foreach ($request->file('foto_evidencia') as $userId => $file) {
            if ($file && $file->isValid()) {
                $nombre = 'temp_' . $userId . '_' . time() . '.' . $file->extension();
                $ruta = $file->storeAs('asistencias/temp', $nombre, 'public');
                $rutasTemporales[$userId] = $ruta;
            }
        }
    }

    session([
        'asistencias_data' => [
            'asistencias' => $asistencias,
            'fotos_temporales' => $rutasTemporales,
            'observaciones' => $request->input('observaciones'),
            'coberturas' => $coberturas,
            'faltas' => $faltas,
            'fecha' => $fechaRegistro,
            'hora' => $horaRegistro,
            'punto' => $puntoSeleccionado,
            'user_id_registrador' => $user->id,
        ]
    ]);

    return redirect()->route('operaciones.confirmarFaltas');
}

public function confirmarFaltas()
{
    $data = session('asistencias_data');

    if (!$data) {
        return redirect()->route('operaciones.asistenciaDiaria')
            ->with('error', 'No hay datos de asistencia pendientes.');
    }

    $faltantes = \App\Models\User::whereIn('id', $data['faltas'])
        ->with('solicitudAlta.documentacion')
        ->get();

    return view('operaciones.confirmar-faltas', compact('faltantes'));
}

public function finalizarAsistencia(Request $request)
{
    $data = session('asistencias_data');

    if (!$data) {
        return redirect()->route('operaciones.asistenciaDiaria')
            ->with('error', 'No hay datos de asistencia pendientes.');
    }

    DB::beginTransaction();
    try {
        $userRegistrador = \App\Models\User::find($data['user_id_registrador']);
        $punto = $data['punto'];
        $descansan = $request->input('descansan', []);

        // Recalcular faltas finales (solo guardias)
        $faltasOriginales = collect($data['faltas'])
            ->map(fn($id) => \App\Models\User::find($id))
            ->filter(fn($u) => $u && $u->rol === 'GUARDIA')
            ->pluck('id')
            ->toArray();

        $faltasFinales = array_values(array_diff($faltasOriginales, $descansan));

        // 📁 Mover archivos temporales a carpeta definitiva
        $rutaDefinitiva = "asistencias/" . Str::slug($userRegistrador->name) . "/" . $data['fecha'];
        Storage::disk('public')->makeDirectory($rutaDefinitiva, 0755, true);

        $fotosAsistentes = [];
        foreach ($data['fotos_temporales'] ?? [] as $userId => $rutaTemp) {
            if (Storage::disk('public')->exists($rutaTemp)) {
                $nombreArchivo = basename($rutaTemp);
                $nuevaRuta = "{$rutaDefinitiva}/{$nombreArchivo}";
                Storage::disk('public')->move($rutaTemp, $nuevaRuta);
                $fotosAsistentes[$userId] = $nuevaRuta;
            }
        }

        // Guardar registro definitivo
        \App\Models\Asistencia::create([
            'user_id' => $userRegistrador->id,
            'fecha' => $data['fecha'],
            'hora_asistencia' => $data['hora'],
            'elementos_enlistados' => json_encode($data['asistencias']),
            'faltas' => json_encode($faltasFinales),
            'descansos' => json_encode($descansan),
            'coberturas' => json_encode($data['coberturas']),
            'observaciones' => $data['observaciones'] ?: 'Ninguna',
            'punto' => $punto,
            'empresa' => $userRegistrador->empresa ?? 'PSC',
            'fotos_asistentes' => json_encode($fotosAsistentes),
        ]);

        DB::commit();
        session()->forget('asistencias_data');

        return redirect()->route('operaciones.asistenciaDiaria')
            ->with('success', "Asistencia registrada exitosamente para el punto {$punto}.");

    } catch (\Exception $e) {
        DB::rollBack();
        // Opcional: limpiar archivos temporales en caso de error
        foreach ($data['fotos_temporales'] ?? [] as $rutaTemp) {
            if (Storage::disk('public')->exists($rutaTemp)) {
                Storage::disk('public')->delete($rutaTemp);
            }
        }
        Log::error('Error al finalizar asistencia (Operaciones): ' . $e->getMessage());
        return back()->with('error', 'Error al guardar la asistencia. Por favor, inténtalo de nuevo.');
    }
}

    private function getSubpuntosPorPunto()
{
    // Copiado directamente del Livewire, sin cambios
    $monterreyId = \App\Models\Punto::where('nombre', 'MONTERREY')->value('id');

    $codigos = [];
    if ($monterreyId) {
        $codigos = \App\Models\Subpunto::where('punto_id', $monterreyId)->pluck('codigo', 'nombre')->toArray();
    }

    $codigoMaryKay = $codigos['MARY KAY CORPORATIVO'] ?? $codigos['MARYKAY CORPORATIVO'] ?? $codigos['MAR KAY CORPORATIVO'] ?? null;

    $monterreySubpuntos = [
        ['nombre' => 'MONTERREY', 'codigo' => $codigos['MONTERREY'] ?? null],
        ['nombre' => 'CUSTODIO', 'codigo' => $codigos['CUSTODIO'] ?? null],
        ['nombre' => 'DALTILE', 'codigo' => $codigos['DALTILE'] ?? null],
        ['nombre' => 'TORRENOVO', 'codigo' => $codigos['TORRENOVO'] ?? null],
        ['nombre' => 'TRASLADOS', 'codigo' => $codigos['TRASLADOS'] ?? null],
        ['nombre' => 'BONETERA', 'codigo' => $codigos['BONETERA'] ?? null],
        ['nombre' => 'HOMEDEPOT', 'codigo' => $codigos['HOMEDEPOT'] ?? null],
        ['nombre' => 'AMERICAN AIRLINES', 'codigo' => $codigos['AMERICAN AIRLINES'] ?? null],
        ['nombre' => 'MARY KAY CORPORATIVO', 'codigo' => $codigoMaryKay],
        ['nombre' => 'KANSAS', 'codigo' => $codigos['KANSAS'] ?? null],
        ['nombre' => 'CIMARRON', 'codigo' => $codigos['CIMARRON'] ?? null],
        ['nombre' => 'OFICINA', 'codigo' => $codigos['OFICINA'] ?? null],
        ['nombre' => 'ASSET', 'codigo' => $codigos['ASSET'] ?? null],
        ['nombre' => 'TORRE DELTA', 'codigo' => $codigos['TORRE DELTA'] ?? null],
        ['nombre' => 'SACMI DE MEXICO', 'codigo' => $codigos['SACMI DE MEXICO'] ?? null],
        ['nombre' => 'THERMO ELÉCTRICA', 'codigo' => $codigos['THERMO ELÉCTRICA'] ?? null],
        ['nombre' => 'KINDER MORGAN', 'codigo' => $codigos['KINDER MORGAN'] ?? null],
        ['nombre' => 'GOBAR', 'codigo' => $codigos['GOBAR'] ?? null],
        ['nombre' => 'PEMCORP #2', 'codigo' => $codigos['PEMCORP #2'] ?? null],
        ['nombre' => 'ROCHE BOBOIS', 'codigo' => $codigos['ROCHE BOBOIS'] ?? null],
        ['nombre' => 'OFF ON GREEN', 'codigo' => $codigos['OFF ON GREEN'] ?? null],
        ['nombre' => 'COOPER LIGHT', 'codigo' => $codigos['COOPER LIGHT'] ?? null],
        ['nombre' => 'MONTE PALATINO', 'codigo' => $codigos['MONTE PALATINO'] ?? null],
        ['nombre' => 'OATEY', 'codigo' => $codigos['OATEY'] ?? null],
        ['nombre' => 'PLAZA DOMENA', 'codigo' => $codigos['PLAZA DOMENA'] ?? null],
    ];

    return [
        'MONTERREY' => $monterreySubpuntos,
        'GUANAJUATO' => [
            ['nombre' => 'SILAO', 'codigo' => null],
            ['nombre' => 'CELAYA', 'codigo' => null],
            ['nombre' => 'SALAMANCA', 'codigo' => null],
        ],
        'NUEVO LAREDO' => [
            ['nombre' => 'ZONA DE ABASTOS V', 'codigo' => null],
        ],
        'MEXICO' => [
            ['nombre' => 'VALLE DE MEXICO', 'codigo' => null],
        ],
        'SLP' => [
            ['nombre' => 'WATCO', 'codigo' => null],
            ['nombre' => 'BMW', 'codigo' => null],
            ['nombre' => 'ZONA DE ABASTOS I', 'codigo' => null],
            ['nombre' => 'INTERPUERTO Y TALLER', 'codigo' => null],
        ],
        'XALAPA' => [
            ['nombre' => 'XALAPA', 'codigo' => null],
        ],
        'MICHOACAN' => [
            ['nombre' => 'MICHOACÁN', 'codigo' => null],
        ],
        'PUEBLA' => [
            ['nombre' => 'PUEBLA', 'codigo' => null],
        ],
        'TOLUCA' => [
            ['nombre' => 'TOLUCA', 'codigo' => null],
        ],
        'QUERETARO' => [
            ['nombre' => 'QUERÉTARO', 'codigo' => null],
        ],
        'SALTILLO' => [
            ['nombre' => 'SALTILLO', 'codigo' => null],
        ],
        'DRONES' => [
            ['nombre' => 'DRONES', 'codigo' => null],
        ],
        'KANSAS' => [
            ['nombre' => 'KANSAS', 'codigo' => null],
        ],
    ];
}
    }
