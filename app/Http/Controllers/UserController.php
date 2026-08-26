<?php

namespace App\Http\Controllers;

use App\Models\BuzonQueja;
use App\Models\DocumentacionAltas;
use App\Models\SolicitudAlta;
use App\Models\SolicitudBajas;
use App\Models\SolicitudVacaciones;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function crearUsuario()
    {
        $this->authorize('create', User::class);

        return view('admi.crearUsuario');
    }

    public function registrarUsuario(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'rol' => ['required', 'string', 'max:100'],
            'punto' => ['nullable', 'string', 'max:255'],
            'empresa' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
            'punto' => $validated['punto'] ?? null,
            'empresa' => $validated['empresa'] ?? null,
            'estatus' => 'Activo',
            'fecha_ingreso' => date('Y-m-d'),
        ]);

        return redirect()->route('admin.verUsuarios')->with('success', '¡Usuario creado exitosamente!');
    }

    public function solicitarBajaForm()
    {
        $user = User::find(Auth::user()->id);
        $solicitud = SolicitudAlta::where('id', $user->sol_alta_id)->first();
        $solicitudpendiente = SolicitudBajas::where('user_id', $user->id)->where('estatus', 'En Proceso')->first();

        return view('users.solicitarBajaForm', compact('user', 'solicitud', 'solicitudpendiente'));
    }

    public function solicitarBaja(Request $request, $id)
    {
        $request->validate([
            'fecha_hoy' => 'required|date',
            'incapacidad' => 'nullable|string|max:255',
            'por' => 'required|in:Ausentismo,Separación Voluntaria, Renuncia',
            'ultima_asistencia' => 'nullable|date',
            'motivo' => 'nullable|string',
        ]);

        $user = User::findorFail($id);
        $this->authorize('requestTermination', $user);

        $solicitud = new SolicitudBajas;
        $solicitud->user_id = $user->id;
        $solicitud->fecha_solicitud = $request->fecha_hoy;
        $solicitud->motivo = $request->motivo;
        $solicitud->incapacidad = $request->incapacidad;
        $solicitud->por = $request->por;
        $solicitud->ultima_asistencia = $request->ultima_asistencia;
        $solicitud->estatus = 'En Proceso';
        $solicitud->observaciones = 'Solicitud de baja en proceso';
        try {
            $solicitud->save();
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Error al enviar la solicitud');
        }

        return redirect()->route('dashboard')->with('success', 'Solicitud de baja enviada correctamente');
    }

    public function solicitarVacacionesForm()
    {
        $user = User::find(Auth::user()->id);
        $antiguedad = (int) floor(Carbon::parse($user->fecha_ingreso)->floatDiffInYears(now('America/Mexico_City')));
        $fechaIngreso = Carbon::parse($user->fecha_ingreso);
        $fechaActual = Carbon::now('America/Mexico_City');

        if ($antiguedad === 0) {
            $mesesLaborados = $fechaIngreso->diffInMonths($fechaActual);
        } else {
            $mesesLaborados = 0;
        }

        if ($antiguedad < 2) {
            $dias = 12;
        } elseif ($antiguedad == 2) {
            $dias = 14;
        } elseif ($antiguedad == 3) {
            $dias = 16;
        } elseif ($antiguedad == 4) {
            $dias = 18;
        } elseif ($antiguedad == 5) {
            $dias = 20;
        } elseif ($antiguedad > 5 && $antiguedad <= 10) {
            $dias = 22;
        } elseif ($antiguedad > 10 && $antiguedad <= 15) {
            $dias = 24;
        } elseif ($antiguedad > 15 && $antiguedad <= 20) {
            $dias = 26;
        } elseif ($antiguedad > 20 && $antiguedad <= 25) {
            $dias = 28;
        } elseif ($antiguedad > 25 && $antiguedad <= 30) {
            $dias = 30;
        } else {
            $dias = 32;
        }

        $diasDisponibles = $dias;
        $diasUtilizados = 0;
        $aniversario = Carbon::createFromDate(
            now()->year,
            $fechaIngreso->month,
            $fechaIngreso->day
        );

        if ($aniversario->isFuture()) {
            $aniversario->subYear();
        }
        $vacacionesTomadas = SolicitudVacaciones::where('user_id', $user->id)
            ->whereIn('estatus', ['Aceptada', 'En Proceso'])
            ->where('created_at', '>=', $aniversario)
            ->get();

        foreach ($vacacionesTomadas as $vacacion) {
            $diasDisponibles -= $vacacion->dias_solicitados;
            $diasUtilizados += $vacacion->dias_solicitados;
        }

        $solicitud = SolicitudAlta::where('id', $user->sol_alta_id)->first();
        $documentacion = DocumentacionAltas::where('solicitud_id', $user->sol_alta_id)->first();

        return view('users.solicitarVacacionesForm', compact('user', 'solicitud', 'documentacion', 'antiguedad', 'dias', 'diasDisponibles', 'diasUtilizados', 'mesesLaborados'));
    }

    public function solicitarVacaciones(Request $request, $id)
    {
        Log::info('Solicitud de vacaciones recibida', [
            'dias_solicitados' => $request->dias_solicitados,
            'turno_doble' => $request->turno_doble,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        $request->validate([
            'tipo' => 'required|string',
            'turno_doble' => 'nullable|boolean',
            'periodo' => 'nullable|integer|min:0|max:9999',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'dias_solicitados' => 'required|integer|min:1|max:30',
            'dias_utilizados' => 'required|integer|min:0|max:36',
            'dias_disponibles' => 'required|integer|min:0|max:36',
            'dias_por_derecho' => 'required|integer|min:0|max:36',
        ]);

        $user = User::findOrFail($id);
        $this->authorize('requestVacation', $user);

        if (Auth::user()->rol == 'Supervisor' || Auth::user()->rol == 'SUPERVISOR') {
            $supervisor = User::where('rol', 'admin')->get();
        }

        $periodo = $request->periodo;
        if ($periodo < 2) {
            $diasPorDerecho = 12;
        } elseif ($periodo == 2) {
            $diasPorDerecho = 14;
        } elseif ($periodo == 3) {
            $diasPorDerecho = 16;
        } elseif ($periodo == 4) {
            $diasPorDerecho = 18;
        } elseif ($periodo == 5) {
            $diasPorDerecho = 20;
        } elseif ($periodo > 5 && $periodo <= 10) {
            $diasPorDerecho = 22;
        } elseif ($periodo > 10 && $periodo <= 15) {
            $diasPorDerecho = 24;
        } elseif ($periodo > 15 && $periodo <= 20) {
            $diasPorDerecho = 26;
        } elseif ($periodo > 20 && $periodo <= 25) {
            $diasPorDerecho = 28;
        } elseif ($periodo > 25 && $periodo <= 30) {
            $diasPorDerecho = 30;
        } else {
            $diasPorDerecho = 32;
        }

        // ✅ Calcular días ya utilizados SIN sumar nada por turno doble
        $diasUtilizados = SolicitudVacaciones::where('user_id', $user->id)
            ->where('periodo', $request->periodo)
            ->whereIn('estatus', ['Aceptada', 'En Proceso'])
            ->sum('dias_solicitados');

        $diasDisponibles = $diasPorDerecho - $diasUtilizados;

        $supervisores = User::where('rol', 'admin')->pluck('id')->toArray();
        $solicitud = new SolicitudVacaciones;
        $solicitud->user_id = $user->id;
        $solicitud->tipo = $request->tipo;
        $solicitud->periodo = $request->periodo;
        $solicitud->fecha_inicio = $request->fecha_inicio;
        $solicitud->supervisores_ids = json_encode($supervisores);
        $solicitud->fecha_fin = $request->fecha_fin;

        // ✅ Guardar dias_solicitados tal cual lo envió el frontend
        $solicitud->dias_solicitados = $request->dias_solicitados;

        $solicitud->dias_ya_utilizados = $diasUtilizados;
        $solicitud->dias_disponibles = $diasDisponibles;
        $solicitud->dias_por_derecho = $diasPorDerecho;
        $solicitud->monto = 0.0;

        // ✅ Guardar turno_doble como string
        $solicitud->turno_doble = $request->turno_doble ? 'true' : 'false';

        if (Auth::user()->rol == 'Supervisor' || Auth::user()->rol == 'admin' || Auth::user()->rol == 'SUPERVISOR' || Auth::user()->solicitudAlta->departamento == 'Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RECURSOS HUMANOS' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RH' || Auth::user()->solicitudAlta->rol == 'AUX RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'Aux RH' || Auth::user()->rol == 'AUXILIAR RECURSOS HUMANOS' || Auth::user()->rol == 'Auxiliar recursos humanos') {
            $solicitud->observaciones = 'Solicitud aceptada, falta subir archivo de solicitud.';
        } else {
            $solicitud->observaciones = 'Solicitud de vacaciones en proceso';
        }

        $rol = strtolower(Auth()->user()->rol);
        if ($rol == 'admin' || $rol == 'administrador' || $rol == 'auxiliar recursos humanos') {
            $solicitud->estatus = 'Aceptada';
            $solicitud->observaciones = 'Solicitud de vacaciones aceptada.';
        } else {
            $solicitud->estatus = 'En Proceso';
        }
        $solicitud->save();

        if (Auth::user()->rol == 'admin' || Auth::user()->solicitudAlta->departamento == 'Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RECURSOS HUMANOS' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RH' || Auth::user()->solicitudAlta->rol == 'AUX RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'Aux RH' || Auth::user()->rol == 'AUXILIAR RECURSOS HUMANOS' || Auth::user()->rol == 'Auxiliar recursos humanos') {
            return redirect()->route('dashboard')->with('success', 'Solicitud de vacaciones enviada correctamente');
        } else {
            return redirect()->route('dashboard')->with('success', 'Solicitud de vacaciones enviada correctamente');
        }
    }

    public function historialVacaciones()
    {
        $user = User::find(Auth::user()->id);
        $vacaciones = SolicitudVacaciones::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('users.historialVacaciones', compact('vacaciones'));
    }

    public function verFicha($id)
    {
        $user = User::findorFail($id);
        $this->authorize('view', $user);
        $solicitud = SolicitudAlta::where('id', $user->sol_alta_id)->first();
        $documentacion = DocumentacionAltas::where('solicitud_id', $user->sol_alta_id)->first();

        return view('admi.verFichaUser', compact('user', 'solicitud', 'documentacion'));
    }

    public function buzon()
    {
        return view('users.buzon');
    }

    public function enviarSugerencia(Request $request, $id)
    {
        $user = User::findorFail($id);
        $this->authorize('submitComplaint', $user);
        $request->validate([
            'fecha' => 'required|date',
            'asunto' => 'required|string|max:255',
            'mensaje' => 'required|string|max:1024',
        ]);

        $mensaje = new BuzonQueja;
        $mensaje->user_id = $user->id;
        $mensaje->fecha = Carbon::parse($request->fecha);
        $mensaje->asunto = $request->asunto;
        $mensaje->mensaje = $request->mensaje;
        $mensaje->save();

        return redirect()->route('user.buzon')->with('success', 'Mensaje enviado correctamente');
    }

    public function buscarUsuarios(Request $request)
    {
        Gate::authorize(\App\Support\Authorization\Permission::MESSAGES_ACCESS);

        $request->validate([
            'search' => 'required|string|min:2|max:100',
        ]);

        $usuarios = User::where('estatus', 'Activo')
            ->where('name', 'like', '%'.$request->search.'%')
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'usuarios' => $usuarios,
        ]);
    }
}
