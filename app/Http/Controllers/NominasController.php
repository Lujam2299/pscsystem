<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Alerta;
use App\Models\Archivonomina;
use App\Models\ArchivoPagoSemanal;
use App\Models\Asistencia;
use App\Models\Nomina;
use App\Models\SolicitudVacaciones;
use App\Models\SolicitudAlta;
use App\Models\SolicitudBajas;
use App\Models\Punto;
use App\Models\Subpunto;
use App\Models\Deducciones;
use App\Exports\DestajosSpreadsheetExport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Finiquito;
use App\Services\FiniquitoCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class NominasController extends Controller
{
    public function antiguedades(){
        return view('nominas.antiguedades');
    }

    public function verBajas(){
        Gate::authorize('viewFiniquitos', SolicitudBajas::class);

        $bajas = SolicitudBajas::where('estatus', 'Aceptada')
            ->where('por', 'Renuncia')
            ->where('calculo_finiquito', null)
            ->with('user')
            ->orderByDesc('fecha_baja')
            ->paginate(10);
        return view('nominas.verBajas', compact('bajas'));
    }

    public function nuevasAltas(){
        $solicitudes = SolicitudAlta::where('status', 'Aceptada')
            ->whereDate('fecha_ingreso', '>=', Carbon::today('America/Mexico_City')->subDays(15))//si se requiere respetar a toda la quincena
            ->with('usuario')
            ->get();
        $users = User::where('estatus', 'Activo')
            ->where('num_empleado', null)
            ->where('fecha_ingreso', '>=', Carbon::today('America/Mexico_City')->subDays(15))
            ->get();
        return view('nominas.nuevasAltas', compact('solicitudes', 'users'));
    }

    public function guardarCalculoFiniquito(Request $request, FiniquitoCalculator $calculator)
    {
        $validated = $request->validate([
            'imagen' => ['required', 'string', 'max:10485760', 'regex:/^data:image\/png;base64,/'],
            'solicitud_id' => 'required|integer|exists:solicitud_bajas,id',
        ]);

        $solicitud = SolicitudBajas::with('user.solicitudAlta')->findOrFail($validated['solicitud_id']);
        Gate::authorize('processFiniquito', $solicitud);

        if (!$solicitud->arch_renuncia) {
            return response()->json(['success' => false, 'error' => 'Falta la renuncia firmada.'], 422);
        }

        $encoded = substr($validated['imagen'], strpos($validated['imagen'], ',') + 1);
        $imagen = base64_decode($encoded, true);
        if ($imagen === false || !str_starts_with($imagen, "\x89PNG\r\n\x1a\n")) {
            return response()->json(['success' => false, 'error' => 'La imagen del cálculo no es un PNG válido.'], 422);
        }

        try {
            $calculo = $calculator->calculate($solicitud);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
        $carpeta = "finiquitos/{$solicitud->id}";
        $nombreArchivo = 'finiquito_' . now()->format('Ymd_His_u') . '.png';
        $rutaCompleta = "{$carpeta}/{$nombreArchivo}";
        if (!Storage::disk('local')->put($rutaCompleta, $imagen)) {
            return response()->json(['success' => false, 'error' => 'No fue posible almacenar el documento.'], 500);
        }
        $rutaAnterior = $solicitud->calculo_finiquito;

        try {
            DB::transaction(function () use ($solicitud, $rutaCompleta, $calculo) {
                $solicitud->update([
                    'calculo_finiquito' => 'private:' . $rutaCompleta,
                    'observaciones' => 'Finiquito enviado a RH.',
                ]);

                $this->persistCalculation($solicitud, $calculo);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($rutaCompleta);
            Log::error('Error al guardar finiquito.', ['exception' => $e]);
            return response()->json(['success' => false, 'error' => 'No fue posible guardar el finiquito.'], 500);
        }

        $this->deletePrivateFiniquito($rutaAnterior);

        return response()->json([
            'success' => true,
            'ruta' => route('finiquitos.archivo', $solicitud),
            'total' => $calculo['total'],
        ]);
    }

    public function asistenciasNominas(){
        return view('nominas.asistencias');
    }

    public function vacacionesNominas(){
        return view('nominas.vacaciones');
    }

    public function vacacionesIndex(Request $request)
    {
        $query = SolicitudVacaciones::query()->where('estatus', 'Aceptada');
        if ($request->filled('fecha_inicio')) {
            $query->where('fecha_inicio', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->where('fecha_fin', '<=', $request->fecha_fin);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('punto')) {
            $userIdsDirectos = User::where('punto', $request->punto)->pluck('id');

            if ($userIdsDirectos->isNotEmpty()) {
                $query->whereIn('user_id', $userIdsDirectos);
            } else {
                $punto = Punto::where('nombre', $request->punto)->first();
                if ($punto) {
                    $subpuntos = Subpunto::where('punto_id', $punto->id)->pluck('nombre');
                    $userIds = User::whereIn('punto', $subpuntos)->pluck('id');
                    $query->whereIn('user_id', $userIds);
                }
            }
        }
        $vacaciones = $query->with('user')->get();

        return view('nominas.vacaciones', compact('vacaciones'));
    }

    public function vistaNominas(){
        return view('nominas.vistaNominas');
    }

    public function calculosNominas(Request $request)
    {
        $query = User::query()->where('estatus', 'Activo');

        if ($request->filled('punto')) {
            $punto = Punto::where('nombre', $request->punto)->first();

            if ($punto) {
                $subpuntos = Subpunto::where('punto_id', $punto->id)->pluck('nombre');
                $query->whereIn('punto', $subpuntos);
            } else {
                $query->where('punto', $request->punto);
            }
        }

        $usuarios = $query->get();
        $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : null;
    $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : null;

    foreach ($usuarios as $user) {
        $asistencias = Asistencia::query()
            ->when($fechaInicio && $fechaFin, function ($q) use ($fechaInicio, $fechaFin) {
                return $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            })
            ->where('punto', $user->punto)
            ->get();

        $asistencias_count = 0;
        $descansos_count = 0;
        $faltas_count = 0;

        foreach ($asistencias as $registro) {
            $enlistados = json_decode($registro->elementos_enlistados, true) ?? [];
            $descansos = json_decode($registro->descansos, true) ?? [];
            $faltas = json_decode($registro->faltas, true) ?? [];

            if (in_array($user->id, $enlistados)) $asistencias_count++;
            if (in_array($user->id, $descansos)) $descansos_count++;
            if (in_array($user->id, $faltas)) $faltas_count++;
        }
        $deducciones = Deducciones::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('status', 'Pendiente')
                ->orWhere('monto_pendiente', '>', 0);
            })
            ->get();

        $montoDeducciones = 0;

        foreach ($deducciones as $deduccion) {
            $montoQuincenal = $deduccion->monto / $deduccion->num_quincenas;
            $montoQuincenal = round($montoQuincenal, 2);
            $montoDeducciones += $montoQuincenal;
        }

        $vacaciones = SolicitudVacaciones::where('user_id', $user->id)
            ->where('estatus', 'Aceptada')
            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin]);
            })
            ->get();

        $montoVacaciones = 0;
        foreach ($vacaciones as $vacacion) {
            $dias = $vacacion->dias_solicitados ?? 0;
            $sd = $user->solicitudAlta->sd ?? 0;
            $monto = $sd * $dias;
            $montoVacaciones += $monto;
        }

        $user->monto_vacaciones = round($montoVacaciones, 2);
        $user->monto_deducciones = $montoDeducciones;
        $user->asistencias_count = $asistencias_count;
        $user->descansos_count = $descansos_count;
        $user->faltas_count = $faltas_count;
    }

        return view('nominas.vistaNominas', compact('usuarios'));
    }

    public function graficas(){
        return view('nominas.graficas');
    }

    public function deduccionesIndex(){
        $deducciones = Deducciones::where('status', 'Pendiente')
            ->paginate(10);
        return view('nominas.deducciones', compact('deducciones'));
    }

    public function nuevaDeduccionForm(){
        return view('nominas.deduccionForm');
    }

    public function guardarDeduccion(Request $request){
        try{
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'concepto' => 'required|string',
                'fecha_inicio' => 'required|date',
                'monto' => 'required|numeric',
                'num_quincenas' => 'required|integer',
            ]);


            $deduccion = new Deducciones();
            $deduccion->user_id = $request->user_id;
            $deduccion->concepto = $request->concepto;
            $deduccion->fecha_inicio = $request->fecha_inicio;
            $deduccion->monto = $request->monto;
            $deduccion->num_quincenas = $request->num_quincenas;
            $deduccion->monto_pendiente = $request->monto;
            $deduccion->status = 'Pendiente';
            $deduccion->save();

            return redirect()->route('nominas.deducciones')
                ->with('success', 'Deducción guardada correctamente');
        }catch(\Exception $e){
            Log::error('Error al guardar deduccion: '. $e->getMessage());
            return redirect()->route('nominas.deducciones')
                ->with('error', 'Error al guardar la deducción: '. $e->getMessage())
                ->withInput();
        }
    }

    public function asignarNumEmpleado(Request $request){
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'num_empleado' => 'required|integer|min:1',
        ]);

        $user = User::find($request->user_id);
        $user->num_empleado = $request->num_empleado;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Número de empleado asignado correctamente.'
        ]);
    }

public function solicitarConstancia(Request $request)
{
    $user = User::findOrFail($request->user_id);
    Log::info('Solicitud de constancia iniciada por el usuario: ' . Auth::id());

    try {
        $usuariosRH = User::where('estatus', 'Activo')->where(function ($query) {
            $query->where('rol', 'admin')
                ->orWhereIn('rol', [
                    'AUXILIAR RECURSOS HUMANOS', 'Auxiliar recursos humanos'
                ])
                ->orWhereHas('solicitudAlta', function ($q) {
                    $q->where('departamento', 'Recursos Humanos')
                        ->orWhereIn('rol', [
                        'AUXILIAR RECURSOS HUMANOS', 'AUXILIAR RH', 'AUX RH',
                        'Auxiliar RH', 'Auxiliar Recursos Humanos', 'Aux RH'
                    ]);
                });
        })->get();

        Log::info('Usuarios RH encontrados: ' . $usuariosRH->count());

            Alerta::create([
                'user_id' => $user->id,
                'titulo' => 'Solicitud de Constancia',
                'mensaje' =>'Depto. Nóminas solicitó una Constancia de Situación Fiscal del usuario: '. $user->name,
            ]);


        return response()->json(['ok' => true]);

    } catch (\Exception $e) {
        Log::error('Error al enviar solicitud de constancia: ' . $e->getMessage());
        return response()->json(['ok' => false], 500);
    }
}

    public function destajos(){
        return view('nominas.destajos');
    }

    private function calcularIMSS(float $sdi, int $asistencias, int $descansos): float
    {
        $dias = $asistencias + $descansos;
        $sueldo = $dias * $sdi;
        return round(($sueldo * 0.00625) + ($sueldo * 0.01125) + ($sdi * 0.05), 2);
    }

    private function calcularISR(float $sd, int $asistencias, int $descansos, int $faltas): float
{
    $diasTrabajados = $asistencias + $descansos;
    $sueldo = $sd * $diasTrabajados;

    if ($faltas === 0) {
        $sueldo += $sueldo * 0.20; // Bono de 20% si no faltó
    }

    $tablaISR = [
        ['limInf' => 0.01,       'limSup' =>  368.10,    'cuotaFija' => 0.00,     'porcentaje' => 1.92],
        ['limInf' => 368.11,     'limSup' => 3124.35,    'cuotaFija' => 7.05,     'porcentaje' => 6.40],
        ['limInf' => 3124.36,    'limSup' => 5490.75,    'cuotaFija' => 183.45,   'porcentaje' => 10.88],
        ['limInf' => 5490.76,    'limSup' => 6382.80,    'cuotaFija' => 441.00,   'porcentaje' => 16.00],
        ['limInf' => 6382.81,    'limSup' => 7641.90,    'cuotaFija' => 583.65,   'porcentaje' => 17.92],
        ['limInf' => 7641.91,    'limSup' => 15412.80,   'cuotaFija' => 809.25,   'porcentaje' => 21.36],
        ['limInf' => 15412.81,   'limSup' => 24292.65,   'cuotaFija' => 2469.15,  'porcentaje' => 23.52],
        ['limInf' => 24292.66,   'limSup' => 46378.50,   'cuotaFija' => 4557.75,  'porcentaje' => 30.00],
        ['limInf' => 46378.51,   'limSup' => 61838.10,   'cuotaFija' => 11183.40, 'porcentaje' => 32.00],
        ['limInf' => 61838.11,   'limSup' => 185514.30,  'cuotaFija' => 16130.55, 'porcentaje' => 34.00],
        ['limInf' => 185514.31,  'limSup' => INF,        'cuotaFija' => 58180.35, 'porcentaje' => 35.00],
    ];

    foreach ($tablaISR as $rango) {
        if ($sueldo >= $rango['limInf'] && $sueldo <= $rango['limSup']) {
            $excedente = $sueldo - $rango['limInf'];
            $isr = $rango['cuotaFija'] + ($excedente * ($rango['porcentaje'] / 100));
            return round($isr, 2);
        }
    }

    return 0;
}

    public function calculoDestajos(Request $request)
    {
        $query = User::query()->where('estatus', 'Activo');

        if ($request->filled('punto')) {
            $punto = Punto::where('nombre', $request->punto)->first();

            if ($punto) {
                $subpuntos = Subpunto::where('punto_id', $punto->id)->pluck('nombre');
                $query->whereIn('punto', $subpuntos);
            } else {
                $query->where('punto', $request->punto);
            }
        }

        $usuarios = $query->get();

        $periodo = $request->get('periodo');
        $mesNombre = strtolower($request->get('mes'));
        $anio = now()->year;

        $meses = [
            'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
            'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
            'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
        ];

        $mesNumero = $meses[$mesNombre] ?? now()->month;
        $periodoTexto = $periodo && $mesNombre ? "{$periodo} {$mesNombre} {$anio}" : null;

        $nominasPorUsuario = collect();
        if ($periodoTexto) {
            $nominas = Nomina::where('periodo', $periodoTexto)->get();
            $nominasPorUsuario = $nominas->keyBy('user_id');
        }

        if ($periodo === '1°') {
            $fechaInicio = Carbon::create($anio, $mesNumero - 1, 26)->startOfDay();
            $fechaFin = Carbon::create($anio, $mesNumero, 10)->endOfDay();
        } else {
            $fechaInicio = Carbon::create($anio, $mesNumero, 11)->startOfDay();
            $fechaFin = Carbon::create($anio, $mesNumero, 25)->endOfDay();
        }

        $destajos = [];

        foreach ($usuarios as $user) {
            Log::info("Calculando destajo para: {$user->name} (ID: {$user->id})");

            $asistencias = Asistencia::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where('punto', $user->punto)
                ->get();

            $asistencias_count = 0;
            $descansos_count = 0;
            $faltas_count = 0;

            foreach ($asistencias as $registro) {
                $enlistados = json_decode($registro->elementos_enlistados, true) ?? [];
                $descansos = json_decode($registro->descansos, true) ?? [];
                $faltas = json_decode($registro->faltas, true) ?? [];

                if (in_array($user->id, $enlistados)) $asistencias_count++;
                if (in_array($user->id, $descansos)) $descansos_count++;
                if (in_array($user->id, $faltas)) $faltas_count++;
            }

            Log::info("Asistencias: $asistencias_count, Descansos: $descansos_count, Faltas: $faltas_count");

            $sd = floatval($user->solicitudAlta->sd ?? 0);
            $sdi = floatval($user->solicitudAlta->sdi ?? 0);
            $sueldoMensualTexto = $user->solicitudAlta->sueldo_mensual ?? '';

            preg_match('/\((.*?)\)/', $sueldoMensualTexto, $matches);
            $sueldoMensual = isset($matches[1]) ? floatval(str_replace(['$', ','], '', $matches[1])) : 0;

            preg_match('/^\$?[\d,]+/', $sueldoMensualTexto, $matchesMin);
            $sueldoMinimo = isset($matchesMin[0]) ? floatval(str_replace(['$', ','], '', $matchesMin[0])) : 0;

            $nominaNormal = $sd * 15;
            $diasTrabajados = 15; // Fijo, puedes cambiarlo si prefieres usar asistencias + descansos

            // 💰 Cálculo de vacaciones
            $vacaciones = SolicitudVacaciones::where('user_id', $user->id)
                ->where('estatus', 'Aceptada')
                ->where(function ($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                        ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin]);
                })->get();

            $montoVacaciones = 0;
            foreach ($vacaciones as $v) {
                $montoVacaciones += $sd * $v->dias_solicitados;
            }

            // 📉 Deducciones
            $deducciones = Deducciones::where('user_id', $user->id)
                ->where(function ($q) {
                    $q->where('status', 'Pendiente')
                        ->orWhere('monto_pendiente', '>', 0);
                })->get();

            $montoDeducciones = 0;
            foreach ($deducciones as $d) {
                $quincenal = round($d->monto / $d->num_quincenas, 2);
                $montoDeducciones += $quincenal;
            }

            // 🧮 IMSS e ISR
            $imss = $this->calcularIMSS($sdi, 7, 8);
            $isr = $this->calcularISR($sd, 7, 8, $faltas_count);

            if ($faltas_count == 0) {
                $nominaNormal += $nominaNormal * 0.20;
                $nominaNormal += $montoVacaciones;

                if (($sueldoMensual / 2) < 5018.59)
                    $nominaNormal = $nominaNormal - $isr + 234.2;
                else
                    $nominaNormal = $nominaNormal - $isr - $imss;

                $nominaNormal -= $montoDeducciones;

                $destajo = ($sueldoMensual / 2) - $nominaNormal;
            } else {
                $nominaTrabajada = $sd * $diasTrabajados;
                $nominaTrabajada += $montoVacaciones;

                if (($sueldoMensual / 2) < 5018.59)
                    $nominaNormal = $nominaTrabajada - $isr + 234.2;
                else
                    $nominaNormal = $nominaTrabajada - $isr;

                $nominaNormal -= $montoDeducciones;

                $destajoNormal = ($sueldoMensual / 2) - $nominaNormal;
                $destajo = $destajoNormal * ($diasTrabajados / 15);
            }

            $destajos[$user->id] = [
                'asistencias' => $asistencias_count,
                'descansos' => $descansos_count,
                'faltas' => $faltas_count,
                'vacaciones' => $montoVacaciones,
                'deducciones' => $montoDeducciones,
                'destajo' => round($destajo, 2),
            ];
        }

        return view('nominas.destajos', compact('usuarios', 'nominasPorUsuario', 'periodoTexto', 'destajos'));
    }

    public function subidasArchivosForm(){
        return view('nominas.subidasArchivos');
    }

public function subirArchivosNominas(Request $request)
{
    \Log::info('=== INICIO SUBIR ARCHIVOS NOMINAS ===');
    \Log::info('Memoria al inicio', [
        'memory_limit' => ini_get('memory_limit'),
        'usage' => memory_get_usage(true),
    ]);

    try {
        // Validación
        $request->validate([
            'arch_nomina' => 'nullable|mimes:xlsx,xls,csv|max:10240',
            'arch_nomina_spyt' => 'nullable|mimes:xlsx,xls,csv|max:10240',
            'arch_nomina_montana' => 'nullable|mimes:xlsx,xls,csv|max:10240',
            'arch_destajo' => 'nullable|mimes:xlsx,xls,csv|max:10240',
            'periodo' => 'required|string',
        ]);

        \Log::info('Validación exitosa');

        $periodo = $request->get('periodo');
        \Log::info('Periodo recibido', ['periodo' => $periodo]);

        // Crear directorio si no existe
        $rutaDirectorio = 'archivos_nominas/' . $periodo;
        $rutaCompleta = storage_path('app/public/' . $rutaDirectorio);

        if (!file_exists($rutaCompleta)) {
            mkdir($rutaCompleta, 0755, true);
        }

        $rutaArchivoNomina = null;
        $rutaArchivoNominaSpyt = null;
        $rutaArchivoNominaMontana = null;
        $rutaArchivoDestajo = null;

        // === Guardado de archivos ===
        if ($request->hasFile('arch_nomina') && $request->file('arch_nomina')->isValid()) {
            $archivo = $request->file('arch_nomina');
            $nombre = time() . '_nominas.' . $archivo->getClientOriginalExtension();
            $rutaArchivoNomina = $archivo->storeAs($rutaDirectorio, $nombre, 'public');
            \Log::info('Archivo nóminas PSC guardado', ['ruta' => $rutaArchivoNomina]);
        }

        if ($request->hasFile('arch_nomina_spyt') && $request->file('arch_nomina_spyt')->isValid()) {
            $archivo = $request->file('arch_nomina_spyt');
            $nombre = time() . '_nominas_spyt.' . $archivo->getClientOriginalExtension();
            $rutaArchivoNominaSpyt = $archivo->storeAs($rutaDirectorio, $nombre, 'public');
            \Log::info('Archivo nóminas SPYT guardado', ['ruta' => $rutaArchivoNominaSpyt]);
        }

        if ($request->hasFile('arch_nomina_montana') && $request->file('arch_nomina_montana')->isValid()) {
            $archivo = $request->file('arch_nomina_montana');
            $nombre = time() . '_nominas_montana.' . $archivo->getClientOriginalExtension();
            $rutaArchivoNominaMontana = $archivo->storeAs($rutaDirectorio, $nombre, 'public');
            \Log::info('Archivo nóminas Montana guardado', ['ruta' => $rutaArchivoNominaMontana]);
        }

        if ($request->hasFile('arch_destajo') && $request->file('arch_destajo')->isValid()) {
            $archivo = $request->file('arch_destajo');
            $nombre = time() . '_destajos.' . $archivo->getClientOriginalExtension();
            $rutaArchivoDestajo = $archivo->storeAs($rutaDirectorio, $nombre, 'public');
            \Log::info('Archivo destajos guardado', ['ruta' => $rutaArchivoDestajo]);
        }

        // === Cálculo de subtotales ===
        $subtotalpsc = 0;
        $subtotalspyt = 0;
        $subtotalmontana = 0;
        $subtotalDestajo = 0;

        if ($rutaArchivoNomina) {
            $subtotalpsc = $this->calcularSubtotalNomina($rutaArchivoNomina, 'nomina');
            \Log::info('Subtotal PSC calculado', ['subtotal' => $subtotalpsc]);
            gc_collect_cycles();
        }

        if ($rutaArchivoNominaSpyt) {
            $subtotalspyt = $this->calcularSubtotalNomina($rutaArchivoNominaSpyt,'nomina');
            \Log::info('Subtotal SPYT calculado', ['subtotal' => $subtotalspyt]);
            gc_collect_cycles();
        }

        if ($rutaArchivoNominaMontana) {
            $subtotalmontana = $this->calcularSubtotalNomina($rutaArchivoNominaMontana,'nomina');
            \Log::info('Subtotal Montana calculado', ['subtotal' => $subtotalmontana]);
            gc_collect_cycles();
        }

        if ($rutaArchivoDestajo) {
            $subtotalDestajo = $this->calcularSubtotalNomina($rutaArchivoDestajo, 'destajo');
            \Log::info('Subtotal Destajo calculado', ['subtotal' => $subtotalDestajo]);
            gc_collect_cycles();
        }

        // === Guardar en base de datos ===
        $archivoNominaModel = new Archivonomina();
        $archivoNominaModel->periodo = $periodo . ' ' . now()->format('Y');
        $archivoNominaModel->arch_nomina = $rutaArchivoNomina;
        $archivoNominaModel->arch_nomina_spyt = $rutaArchivoNominaSpyt;
        $archivoNominaModel->arch_nomina_montana = $rutaArchivoNominaMontana;
        $archivoNominaModel->arch_destajo = $rutaArchivoDestajo;
        $archivoNominaModel->total_destajos = $subtotalDestajo;
        $archivoNominaModel->subtotal = $subtotalpsc + $subtotalspyt + $subtotalmontana;

        $archivoNominaModel->save();

        \Log::info('Registro guardado en BD', [
            'id' => $archivoNominaModel->id,
            'subtotal_total' => $archivoNominaModel->subtotal
        ]);

        \Log::info('=== FIN EXITOSO ===', [
            'memoria_final' => memory_get_usage(true),
            'memoria_pico' => memory_get_peak_usage(true),
        ]);

        return redirect()->back()->with('success', 'Archivos subidos y procesados correctamente');

    } catch (\Exception $e) {
        \Log::error('=== ERROR EN SUBIR ARCHIVOS NOMINAS ===', [
            'mensaje' => $e->getMessage(),
            'archivo' => $e->getFile(),
            'linea' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'memoria_usada' => memory_get_usage(true),
            'memoria_pico' => memory_get_peak_usage(true),
        ]);

        return redirect()->back()
            ->with('error', 'Error al subir los archivos: ' . $e->getMessage())
            ->withInput();
    }
}
/**
 * Calcular el subtotal de la nómina o destajos según el tipo de archivo
 *
 * @param string $rutaArchivo Ruta relativa en storage
 * @param string $tipo 'nomina' o 'destajo'
 * @return float
 */
private function calcularSubtotalNomina($rutaArchivo, $tipo = 'nomina')
{
    try {
        \Log::info('Calculando subtotal de nómina', [
            'ruta_db' => $rutaArchivo,
            'tipo' => $tipo
        ]);

        $rutaCompleta = storage_path('app/public/' . $rutaArchivo);
        \Log::info('Ruta completa construida', ['ruta_completa' => $rutaCompleta]);

        if (!file_exists($rutaCompleta)) {
            \Log::error('Archivo no encontrado', ['ruta_completa' => $rutaCompleta]);
            return 0;
        }

        $tipoArchivo = \PhpOffice\PhpSpreadsheet\IOFactory::identify($rutaCompleta);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($tipoArchivo);
        $reader->setReadDataOnly(true);
        $reader->setLoadAllSheets();
        $spreadsheet = $reader->load($rutaCompleta);

        $nombresHojas = $spreadsheet->getSheetNames();
        $totalGeneral = 0;

        // === MANEJO DE DESTAJOS (soporta DOS formatos) ===
        if ($tipo === 'destajo') {
            $hojaResumen = null;
            foreach ($nombresHojas as $nombreHoja) {
                if (strtoupper(trim($nombreHoja)) === 'RESUMEN') {
                    $hojaResumen = $spreadsheet->getSheetByName($nombreHoja);
                    break;
                }
            }

            if ($hojaResumen) {
                \Log::info('Hoja RESUMEN encontrada. Buscando "TOTAL DESTAJO"...');
                $dimension = $hojaResumen->getHighestRowAndColumn();
                $maxRow = min((int)$dimension['row'], 100); // Buscar en primeras 100 filas
                $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($dimension['column']);

                for ($row = 1; $row <= $maxRow; $row++) {
                    for ($colIndex = 1; $colIndex <= $maxColIndex; $colIndex++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                        $cellValue = $hojaResumen->getCell("{$colLetter}{$row}")->getValue();

                        // Verificar si la celda contiene "TOTAL DESTAJO" (case-insensitive)
                        if (is_string($cellValue) && preg_match('/^\s*TOTAL\s+DESTAJO\s*$/i', $cellValue)) {
                            $nextColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                            $montoCell = $hojaResumen->getCell("{$nextColLetter}{$row}");
                            $monto = $montoCell->getCalculatedValue();

                            if (is_numeric($monto)) {
                                $totalGeneral = (float)$monto;
                                \Log::info('✅ TOTAL DESTAJO encontrado en RESUMEN', [
                                    'fila' => $row,
                                    'columna' => $colLetter,
                                    'monto' => $totalGeneral
                                ]);
                                // Liberar recursos y retornar
                                $spreadsheet->disconnectWorksheets();
                                unset($spreadsheet, $reader, $hojaResumen);
                                gc_collect_cycles();
                                return $totalGeneral;
                            } else {
                                \Log::warning('TOTAL DESTAJO encontrado, pero el valor no es numérico', [
                                    'valor' => $monto
                                ]);
                            }
                        }
                    }
                }
                \Log::warning('Hoja RESUMEN encontrada, pero no se encontró "TOTAL DESTAJO"');
            }

            // Si no se usó RESUMEN, aplicar lógica original (columna P)
            \Log::info('Usando lógica original de destajo (columna P)');
            foreach ($nombresHojas as $nombreHoja) {
                $worksheet = $spreadsheet->getSheetByName($nombreHoja);
                $fila = 5;
                $espaciosBlancoSeguidos = 0;
                while ($espaciosBlancoSeguidos < 3 && $fila <= 1500) {
                    $nombreEmpleado = $worksheet->getCell('B' . $fila)->getValue();
                    $valorP = $worksheet->getCell('P' . $fila)->getCalculatedValue();
                    if (empty(trim((string)$nombreEmpleado))) {
                        $espaciosBlancoSeguidos++;
                    } else {
                        $espaciosBlancoSeguidos = 0;
                        if (is_numeric($valorP)) {
                            $totalGeneral += (float)$valorP;
                        }
                    }
                    $fila++;
                }
            }
        }
        // === LÓGICA PARA NÓMINA (sin cambios) ===
        elseif ($tipo === 'nomina') {
            foreach ($nombresHojas as $nombreHoja) {
                $worksheet = $spreadsheet->getSheetByName($nombreHoja);
                $dimension = $worksheet->getHighestRowAndColumn();

                $columnaNeto = null;
                $filaEncabezadoEncontrada = null;
                for ($filaEncabezado = 7; $filaEncabezado <= 9; $filaEncabezado++) {
                    for ($col = 'A'; $col <= 'Z'; $col++) {
                        $celda = $worksheet->getCell("{$col}{$filaEncabezado}")->getValue();
                        if (!$celda) continue;
                        $textoLimpio = strtoupper(trim($celda));
                        $textoLimpio = preg_replace('/[^A-Z0-9\s]/', ' ', $textoLimpio);
                        $textoLimpio = preg_replace('/\s+/', ' ', $textoLimpio);
                        if (str_contains($textoLimpio, 'NETO')) {
                            $palabrasProhibidas = ['AJUSTE', 'AJUSTES', 'POR PAGAR', 'PAGO', 'DESCUENTO'];
                            $tieneProhibida = false;
                            foreach ($palabrasProhibidas as $prohibida) {
                                if (str_contains($textoLimpio, $prohibida)) {
                                    $tieneProhibida = true;
                                    break;
                                }
                            }
                            if (!$tieneProhibida) {
                                $columnaNeto = $col;
                                $filaEncabezadoEncontrada = $filaEncabezado;
                                break 2;
                            }
                        }
                    }
                }

                if (!$columnaNeto) continue;

                $ultimoValorValido = 0;
                $ultimaFilaConDatos = (int)$dimension['row'];
                $fin = min($ultimaFilaConDatos, 1500);
                $inicio = $filaEncabezadoEncontrada + 1;

                $buscarEnColumna = function ($col) use ($worksheet, $inicio, $fin) {
                    $ultimoValor = 0;
                    for ($fila = $inicio; $fila <= $fin; $fila++) {
                        $valor = $worksheet->getCell("{$col}{$fila}")->getCalculatedValue();
                        if (is_numeric($valor) && !empty($valor)) {
                            $ultimoValor = (float)$valor;
                        }
                    }
                    return $ultimoValor;
                };

                $ultimoValorValido = $buscarEnColumna($columnaNeto);

                if ($ultimoValorValido == 0) {
                    $colIndex = array_search($columnaNeto, range('A', 'Z'));
                    if ($colIndex !== false) {
                        for ($i = $colIndex + 1; $i < 26; $i++) {
                            $colAdyacente = chr(65 + $i);
                            $valor = $buscarEnColumna($colAdyacente);
                            if ($valor > 0) {
                                $ultimoValorValido = $valor;
                                break;
                            }
                        }
                    }
                }

                $totalGeneral += $ultimoValorValido;
            }
        }

        \Log::info('Cálculo completado', [
            'total_general' => $totalGeneral,
            'tipo' => $tipo,
            'archivo' => $rutaArchivo
        ]);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $reader);
        gc_collect_cycles();

        return $totalGeneral;

    } catch (\Exception $e) {
        \Log::error('Error al calcular subtotal', [
            'mensaje' => $e->getMessage(),
            'archivo' => $rutaArchivo,
            'linea' => $e->getLine(),
            'tipo' => $tipo,
        ]);

        if (isset($spreadsheet)) {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
        gc_collect_cycles();
        return 0;
    }
}
    public function registros(){
        return view('nominas.registros');
    }

    public function guardarFiniquitoManual(Request $request, $id, FiniquitoCalculator $calculator)
{
    $request->validate([
        'finiquito_archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
    ]);

    $solicitud = SolicitudBajas::with('user.solicitudAlta')->findOrFail($id);
    Gate::authorize('processFiniquito', $solicitud);

    if (!$solicitud->arch_renuncia) {
        return redirect()->back()->with('error', 'Falta la renuncia firmada.');
    }

    if ($request->hasFile('finiquito_archivo')) {
        $directorio = 'finiquitos/' . $id;
        Storage::disk('local')->makeDirectory($directorio);

        $extension = $request->file('finiquito_archivo')->getClientOriginalExtension();
        $nombreArchivo = 'finiquito_' . date('Ymd_His') . '.' . $extension;
        $rutaCompleta = $directorio . '/' . $nombreArchivo;

        $rutaArchivo = $request->file('finiquito_archivo')->storeAs($directorio, $nombreArchivo, 'local');
        $rutaAnterior = $solicitud->calculo_finiquito;
        try {
            $calculo = $calculator->calculate($solicitud);
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        try {
            DB::transaction(function () use ($solicitud, $rutaArchivo, $calculo) {
                $solicitud->update([
                    'calculo_finiquito' => 'private:' . $rutaArchivo,
                    'observaciones' => 'Finiquito enviado a RH.'
                ]);
                $this->persistCalculation($solicitud, $calculo);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($rutaArchivo);
            Log::error('Error al guardar finiquito manual.', ['exception' => $e]);
            return redirect()->back()->with('error', 'No fue posible guardar el finiquito.');
        }

        $this->deletePrivateFiniquito($rutaAnterior);

        return redirect()->back()->with('success', 'Finiquito guardado correctamente.');
    }

    return redirect()->back()->with('error', 'No se pudo guardar el archivo.');
}

    public function descargarFiniquito(SolicitudBajas $solicitud)
    {
        Gate::authorize('viewFiniquitos', SolicitudBajas::class);
        abort_unless($solicitud->por === 'Renuncia' && $solicitud->calculo_finiquito, 404);

        $path = $solicitud->calculo_finiquito;
        if (str_starts_with($path, 'private:')) {
            $path = substr($path, 8);
            abort_unless(Storage::disk('local')->exists($path), 404);
            return response()->file(Storage::disk('local')->path($path));
        }

        $path = ltrim($path, '/');
        abort_unless(Storage::disk('public')->exists($path), 404);
        return response()->file(Storage::disk('public')->path($path));
    }

    private function persistCalculation(SolicitudBajas $solicitud, array $calculo): void
    {
        Finiquito::updateOrCreate(
            ['baja_id' => $solicitud->id],
            [
                'monto' => $calculo['total'],
                'salario_diario' => $calculo['employee']['daily_salary'],
                'desglose' => $calculo,
                'version_formula' => $calculo['version'],
                'calculado_por' => auth()->id(),
                'calculado_en' => now(),
            ]
        );
    }

    private function deletePrivateFiniquito(?string $path): void
    {
        if ($path && str_starts_with($path, 'private:')) {
            Storage::disk('local')->delete(substr($path, 8));
        }
    }

    public function formularioSemanal(){
        return view('nominas.semanal');
    }

    public function guardarSemanal(Request $request)
    {
        try {
            $request->validate([
                'mes' => 'required|integer|between:1,12',
                'semana' => 'required|integer|between:1,5',
                'archivo_semanal' => 'required|mimes:xlsx,xls,csv|max:10240',
            ]);

            $mes = $request->mes;
            $semana = $request->semana;
            $anio = now()->format('Y');

            $rutaDirectorio = "archivos_pagos_semanales/{$anio}/mes_{$mes}_semana_{$semana}";
            $rutaCompleta = storage_path("app/public/{$rutaDirectorio}");

            if (!file_exists($rutaCompleta)) {
                mkdir($rutaCompleta, 0755, true);
            }

            $archivo = $request->file('archivo_semanal');
            $nombre = time() . '_pago_semanal.' . $archivo->getClientOriginalExtension();
            $rutaGuardada = $archivo->storeAs($rutaDirectorio, $nombre, 'public');

            $total = $this->calcularSubtotalNomina($rutaGuardada, 'nomina');

            ArchivoPagoSemanal::create([
                'mes' => $mes,
                'semana' => $semana,
                'anio' => $anio,
                'archivo_semanal' => $rutaGuardada,
                'total_semanal' => $total,
            ]);

            return redirect()->back()->with('success', 'Registro semanal subido y procesado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al guardar pago semanal', [
                'mensaje' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Error al subir el archivo: ' . $e->getMessage());
        }
    }

    public function historialDeducciones(){
        return view('nominas.historialDeducciones');
    }

    public function exportar(Request $request)
    {
        $validated = $request->validate([
            'punto' => 'nullable|string',
            'empresa' => 'nullable|string|in:PSC,SPYT,Montana',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        // Validar que al menos uno de punto o empresa esté presente
        if (empty($validated['punto']) && empty($validated['empresa'])) {
            return redirect()->back()->with('error', 'Selecciona al menos un filtro: Punto o Empresa');
        }

        $punto = $validated['punto'] ?? '';
        $empresa = $validated['empresa'] ?? '';
        $fechaInicio = $validated['fecha_inicio'];
        $fechaFin = $validated['fecha_fin'];

        $export = new DestajosSpreadsheetExport($punto, $empresa, $fechaInicio, $fechaFin);
        return $export->generateFile();
    }

    public function historialFiniquitos(){
        Gate::authorize('viewFiniquitos', SolicitudBajas::class);
        return view('nominas.historialFiniquitos');
    }
}
