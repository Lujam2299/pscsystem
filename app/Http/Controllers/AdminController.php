<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Deducciones;
use App\Models\Finiquito;
use App\Models\SolicitudVacaciones;
use App\Models\SolicitudAlta;
use App\Models\SolicitudBajas;
use App\Models\DocumentacionAltas;
use App\Models\User;
use App\Models\Asistencia;
use App\Models\BuzonQueja;
use App\Models\Nomina;
use App\Models\Punto;
use App\Models\Reingreso;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function verUsuarios(){
        $users = User::all();
        return view('admi.verUsuarios', compact('users'));
    }

    public function tableroSupervisores(){
        return view('admi.tableroSupervisores');
    }

    public function verSolicitudesAltas(){
        $solicitudes = SolicitudAlta::where('status', 'Aceptada')
            ->whereDate('updated_at', Carbon::today('America/Mexico_City'))
            ->get();
        return view('admi.verSolicitudesAltas', compact('solicitudes'));
    }

    public function editarUsuarioForm($id){
        $user = User::find($id);
        return view('admi.editarUsuarioForm', compact('user'));
    }

    public function bajaUsuario($id, Request $request) {
        $user = User::find($id);
        $user->estatus = 'Inactivo';
        $user->save();

        $fechaBaja = $request->query('fecha')
            ? Carbon::parse($request->query('fecha'))->format('Y-m-d')
            : Carbon::today()->format('Y-m-d');

        $motivo = $request->query('motivo');

        if (!$fechaBaja || !$motivo) {
            return redirect()->back()->with('error', 'Faltan datos para dar de baja.');
        }

        $solicitud = new SolicitudBajas();
        $solicitud->user_id = $id;
        $solicitud->fecha_solicitud = Carbon::today();
        $solicitud->motivo = 'Desconocido';
        $solicitud->por = $motivo;
        $solicitud->incapacidad = '';
        $solicitud->fecha_baja = $fechaBaja;
        $solicitud->observaciones = 'Baja aceptada.';
        $solicitud->autoriza = Auth::user()->name;
        $solicitud->estatus = 'Aceptada';
        $solicitud->save();

        return redirect()->back()->with('success', 'El usuario ha sido dado de baja correctamente.');
    }

    public function editarUsuario($id){
    $user = User::find($id);
    $solicitudId = $user->sol_alta_id;
    $solicitud = SolicitudAlta::find($solicitudId);
    $docsId = $solicitud->sol_docs_id;
    $documentacion = DocumentacionAltas::find($docsId);
    $puntos = Punto::with('subpuntos')->get(); // Agregamos los puntos

    $tipoSeleccionado = $solicitud->tipo_empleado ?? 'oficina';

    return view('admi.admiEditarUsuarioForm', compact('user', 'solicitud', 'documentacion', 'puntos', 'tipoSeleccionado'));
}

    public function verBuzon(){
        $quejas = BuzonQueja::orderBy('created_at', 'desc')
                ->paginate(10);

        return view ('admi.verBuzon', compact('quejas'));
    }

    public function darReingreso(Request $request, $id)
{
    $user = User::findOrFail($id);

    $user->estatus = 'Activo';
    $user->fecha_ingreso = $request->query('fecha'); // Asegúrate de que este campo sea DATE en la BD
    $user->save(); // Guardar el usuario primero

    if ($user->solicitudAlta) { // Verificar que exista la relación antes de acceder
        $user->solicitudAlta->fecha_ingreso = $request->query('fecha'); // Asegúrate de que este campo sea DATE en la BD
        $user->solicitudAlta->save(); // Guardar la solicitud
    } else {
        // return back()->withErrors(['error' => 'El usuario no tiene una solicitud de alta asociada.']);

    }

    $ultimoReingreso = Reingreso::where('user_id', $user->id)->max('numero_reingreso'); // Obtiene el máximo número de reingreso
    $nuevoNumeroReingreso = $ultimoReingreso ? $ultimoReingreso + 1 : 1; // Si no hay previos, es el primero

    Reingreso::create([
        'user_id' => $user->id,
        'numero_reingreso' => $nuevoNumeroReingreso,
        'fecha' => $request->query('fecha'), // Asegúrate de que este campo sea DATE en la BD
    ]);

    // Opcional: Si aún deseas mantener el texto en solicitudAlta->reingreso por compatibilidad,
    // puedes dejar la lógica anterior comentada o eliminar se usa.
    // Si decides actualizarlo, asegúrate de que la fecha esté en el formato correcto para el texto.
    // $fechaReingreso = Carbon::parse($request->query('fecha'))->format('d-m-Y');
    // $reingresoTexto = $user->solicitudAlta->reingreso ?? 'NO';
    // if (trim($reingresoTexto) === '' || $reingresoTexto === 'NO') {
    //     $user->solicitudAlta->reingreso = "Reingreso 1: $fechaReingreso";
    // } else {
    //     preg_match_all('/Reingreso \d+:/', $reingresoTexto, $coincidencias);
    //     $reingresosHechos = count($coincidencias[0]);
    //     $nuevoNumero = $reingresosHechos + 1;
    //     $user->solicitudAlta->reingreso .= " Reingreso $nuevoNumero: $fechaReingreso";
    // }
    // $user->solicitudAlta->save();

    return redirect()->back()->with('success', 'El usuario ha sido dado de alta como reingreso correctamente.');
}

    public function tableroNominas(){
        return view('admi.tableroNominas');
    }
    public function tableroImss(){
        return view('admi.tableroImss');
    }
    public function tableroRh(){
        return view('admi.tableroRh');
    }
    public function tableroJuridico(){
        return view ('admi.tableroJuridico');
    }

    public function tableroAuxCont(){
        return view ('admi.tableroContabilidad');
    }

    public function tableroOperaciones(){
        return view ('admi.tableroOperaciones');
    }

    public function tableroMonitoreo(){
        return view('admi.tableroMonitoreo');
    }

    public function tableroCustodios(){
        return view('admi.tableroCustodios');
    }

    public function solicitudesVacaciones(){
        $vacaciones = SolicitudVacaciones::where('estatus', 'En Proceso')
        ->where('observaciones', '!=', 'Solicitud aceptada, falta subir archivo de solicitud.')
        ->whereHas('user', function ($query) {
            $query->where('empresa', 'Montana');
        })
        ->with('user')
        ->get();

        return view('admi.solicitudesVacaciones', compact('vacaciones'));
    }

    public function registrarNominas()
    {
        $hoy = now();
        $anio = $hoy->year;
        $mes = $hoy->month;

        if ($hoy->day <= 10) {
            $periodoInicio = Carbon::create($anio, $mes, 1)->subMonth()->setDay(11)->startOfDay();
            $periodoFin = Carbon::create($anio, $mes, 1)->subMonth()->setDay(25)->endOfDay();
            $quincena = '2°';
            $nombreMes = ucfirst($periodoFin->locale('es')->monthName);
            $anioPeriodo = $periodoFin->year;
        } elseif ($hoy->day <= 25) {
            $mesAnterior = $mes - 1;
            $anioAnterior = $anio;
            if ($mesAnterior < 1) {
                $mesAnterior = 12;
                $anioAnterior--;
            }
            $periodoInicio = Carbon::create($anioAnterior, $mesAnterior, 26)->startOfDay();
            $periodoFin = Carbon::create($anio, $mes, 10)->endOfDay();
            $quincena = '1°';
            $nombreMes = ucfirst(Carbon::create($anio, $mes)->locale('es')->monthName);
            $anioPeriodo = $anio;
        } else {
            $periodoInicio = Carbon::create($anio, $mes, 11)->startOfDay();
            $periodoFin = Carbon::create($anio, $mes, 25)->endOfDay();
            $quincena = '2°';
            $nombreMes = ucfirst(Carbon::create($anio, $mes)->locale('es')->monthName);
            $anioPeriodo = $anio;
        }

        $usuarios = User::where('estatus', 'Activo')->get();

        foreach ($usuarios as $user) {
            $sueldoMensualTexto = $user->solicitudAlta->sueldo_mensual ?? '';

            preg_match('/\((.*?)\)/', $sueldoMensualTexto, $matches);
            $sueldoMensual = isset($matches[1]) ? floatval(str_replace(['$', ','], '', $matches[1])) : 0;

            $asistencias = Asistencia::whereBetween('fecha', [$periodoInicio, $periodoFin])
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
            $asistencias_count = 7;
            $descansos_count = 8;
            $faltas_count = 0;

            $sd = $user->solicitudAlta->sd ?? 0;
            $sdi = $user->solicitudAlta->sdi ?? 0;
            $diasTrabajados = $asistencias_count + $descansos_count;
            $percepciones = $sd * $diasTrabajados;

            $vacaciones = SolicitudVacaciones::where('user_id', $user->id)
                ->where('estatus', 'Aceptada')
                ->where(function ($query) use ($periodoInicio, $periodoFin) {
                    $query->whereBetween('fecha_inicio', [$periodoInicio, $periodoFin])
                        ->orWhereBetween('fecha_fin', [$periodoInicio, $periodoFin]);
                })
                ->get();

            $montoVacaciones = 0;
            $totalDiasVacaciones = 0;

            foreach ($vacaciones as $vacacion) {
                $montoVacaciones += ($sd * $vacacion->dias_solicitados)*1.2;
                $totalDiasVacaciones += $vacacion->dias_solicitados;
            }

            if ($montoVacaciones > 0) {
                Log::info("🌴 Vacaciones para {$user->name}: {$totalDiasVacaciones} días, Monto: {$montoVacaciones}");
            }

            $percepciones += $montoVacaciones;

            if ($faltas_count === 0) {
                $percepciones *= 1.2;
            }

            $sueldo = $sdi * $diasTrabajados;
            $imss = ($sueldo * 0.00625) + ($sueldo * 0.01125) + ($sdi * 0.05);

            $sueldoBase = $sd * $diasTrabajados;
            if ($faltas_count === 0) $sueldoBase *= 1.2;

            $isr = 0;
            $tablaISR = [
                ['limInf' => 0.01, 'limSup' => 368.10, 'cuotaFija' => 0.00, 'porcentaje' => 1.92],
                ['limInf' => 368.11, 'limSup' => 3124.35, 'cuotaFija' => 7.05, 'porcentaje' => 6.4],
                ['limInf' => 3124.36, 'limSup' => 5437.91, 'cuotaFija' => 183.56, 'porcentaje' => 10.88],
                ['limInf' => 5437.92, 'limSup' => 7567.38, 'cuotaFija' => 544.68, 'porcentaje' => 16],
                ['limInf' => 7567.39, 'limSup' => INF, 'cuotaFija' => 913.63, 'porcentaje' => 17.92],
            ];
            foreach ($tablaISR as $r) {
                if ($sueldoBase >= $r['limInf'] && $sueldoBase <= $r['limSup']) {
                    $isr = $r['cuotaFija'] + (($sueldoBase - $r['limInf']) * ($r['porcentaje'] / 100));
                    break;
                }
            }
            if(($sueldoMensual / 2) < 5018.59)
                $neto = $percepciones - $isr + 234.2;
            else
                $neto = $percepciones - ($imss + $isr);

            $deducciones = Deducciones::where('user_id', $user->id)
                ->where(function ($q) {
                    $q->where('status', 'Pendiente')
                    ->orWhere('monto_pendiente', '>', 0);
                })
                ->get();
                Log::info("🧾 Usuario: {$user->name} (ID: {$user->id}) - Deducciones encontradas: {$deducciones->count()}");
            $montoDeducciones = 0;

            foreach ($deducciones as $deduccion) {
                $montoQuincenal = $deduccion->monto / $deduccion->num_quincenas;
                $montoQuincenal = round($montoQuincenal, 2);

                $montoDeducciones += $montoQuincenal;

                Log::info("💸 Deducción ID {$deduccion->id} | Total: {$deduccion->monto} | Quincenal: {$montoQuincenal} | Pendiente antes: {$deduccion->monto_pendiente}");

                $deduccion->monto_pendiente -= $montoQuincenal;

                if ($deduccion->monto_pendiente <= 0) {
                    $deduccion->monto_pendiente = 0;
                    $deduccion->status = 'Pagada';
                    Log::info("✅ Deducción ID {$deduccion->id} pagada completamente.");
                } else {
                    Log::info("🔄 Deducción ID {$deduccion->id} actualizada. Nuevo pendiente: {$deduccion->monto_pendiente}");
                }

                $deduccion->save();
            }

            Log::info("📉 Total deducciones aplicadas a {$user->name}: {$montoDeducciones}");

            $neto -= $montoDeducciones;

            $periodoStr = "{$quincena} {$nombreMes} {$anioPeriodo}";

            Nomina::updateOrCreate(
                ['user_id' => $user->id, 'periodo' => $periodoStr],
                ['monto' => round(max(0, $neto), 2)]
            );
        }

        return redirect()->route('dashboard')->with('success', 'Nóminas del último periodo generadas correctamente.');
    }

    public function registrarFiniquitos()
    {
        $solicitudes = SolicitudBajas::with(['user', 'user.solicitudAlta'])
            ->where('estatus', 'Aceptada')
            ->where('por', 'like', '%renuncia%')
            ->get();

        foreach ($solicitudes as $solicitud) {
            if (Finiquito::where('baja_id', $solicitud->id)->exists()) continue;

            $user = $solicitud->user;
            $alta = $user->solicitudAlta;
            if (!$alta) continue;

            $fechaBaja = Carbon::parse($solicitud->fecha_baja);
            $fechaIngreso = Carbon::parse($user->fecha_ingreso);
            $ultimaAsistencia = $solicitud->ultima_asistencia
                ? Carbon::parse($solicitud->ultima_asistencia)
                : $fechaBaja;

            $diasTrabajadosAnio = $fechaIngreso->diffInDays($fechaBaja) + 1;
            $diasNoLaborados = $ultimaAsistencia->diffInDays($fechaBaja);
            $descuentoNoLaborados = $diasNoLaborados * $alta->sd;

            $diasDisponibles = $alta->dias_vacaciones_disponibles ?? 6;
            $factorVacaciones = $diasDisponibles / 365;
            $diasVacaciones = $diasTrabajadosAnio * $factorVacaciones;
            $montoVacaciones = $diasVacaciones * $alta->sd;
            $primaVacacional = $montoVacaciones * 0.25;

            $factorAguinaldo = 15 / 365;
            $inicioAnio = now()->startOfYear();
            $diasTrabAnio = $fechaIngreso->greaterThanOrEqualTo($inicioAnio)
                ? $fechaIngreso->diffInDays($ultimaAsistencia) + 1
                : $inicioAnio->diffInDays($ultimaAsistencia) + 1;

            $diasAguinaldo = $diasTrabAnio * $factorAguinaldo;
            $montoAguinaldo = $diasAguinaldo * $alta->sd;
            $primaAguinaldo = $montoAguinaldo * 0.25;

            $descuentoNoEntregados = $solicitud->descuento ?? 0;

            $finiquito = $montoVacaciones + $primaVacacional + $montoAguinaldo + $primaAguinaldo
                - $descuentoNoLaborados - $descuentoNoEntregados;

            Finiquito::create([
                'baja_id' => $solicitud->id,
                'monto' => round($finiquito, 2)
            ]);
        }

        return response()->json(['status' => 'ok', 'mensaje' => 'Finiquitos generados correctamente']);
    }
}
