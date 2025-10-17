<?php

namespace App\Http\Controllers;

use App\Models\SolicitudAlta;
use App\Models\DocumentacionAltas;
use App\Models\SolicitudBajas;
use App\Models\SolicitudVacaciones;
use App\Models\User;
use App\Models\Punto;
use App\Models\Subpunto;
use App\Models\Asistencia;
use App\Models\TiemposExtra;
use App\Models\CubrirTurno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class SupervisorController extends Controller
{
    public function nuevoUsuarioForm(){
        $puntos = Punto::with('subpuntos')->get();
        return view('supervisor.nuevoUsuarioForm', compact('puntos'));
    }
    public function formAlta(Request $request)
    {
        $tipo = $request->get('tipo', 'noarmado');
        $puntos = Punto::with('subpuntos')->get();
        return view('supervisor.nuevoUsuarioForm', compact('tipo', 'puntos'));
    }
    public function guardarInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo' => 'required|in:armado,noarmado',
                'name' => 'nullable|string|max:255',
                'apellido_paterno' => 'nullable|string|max:255',
                'apellido_materno' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'curp' => 'nullable|string|max:255',
                'nss' => 'nullable|string|max:255',
                'edo_civil' => 'nullable|string',
                'rfc' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:255',
                'calle' => 'nullable|string|max:255',
                'num_ext' => 'nullable|string|max:255',
                'colonia' => 'nullable|string|max:255',
                'ciudad' => 'nullable|string|max:255',
                'peso' => 'nullable|string|max:255',
                'estatura' => 'nullable|string|max:255',
                'cp_fiscal' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:255',
                'liga_rfc' => 'nullable|string|max:255',
                'infonavit' => 'nullable|string|max:255',
                'fonacot' => 'nullable|string|max:255',
                'domicilio_comprobante' => 'nullable|string|max:255',
                'departamento' => 'nullable|string|max:255',
                'rol' => 'nullable|string|max:255',
                'reingreso' => 'nullable|string',
                'punto' => 'nullable|string|max:255',
                'empresa' => 'nullable|string',
                'sueldo_mensual' => 'nullable|string',
                'fecha_ingreso' => 'nullable|date',
                'email' => 'nullable|email|unique:solicitud_altas,email',
            ]);

            $tipoSeleccionado = $request->get('tipo', 'noarmado');
            $solicitud = new SolicitudAlta();
            $solicitud->solicitante = auth()->user()->name;
            $solicitud->nombre = $request->name;
            $solicitud->apellido_paterno = $request->apellido_paterno;
            $solicitud->apellido_materno = $request->apellido_materno;
            $solicitud->fecha_nacimiento = $request->fecha_nacimiento;
            $solicitud->tipo_empleado = $request->get('tipo', 'noarmado');
            $solicitud->curp = $request->curp;
            $solicitud->nss = $request->nss;
            $solicitud->estado_civil = $request->edo_civil;
            $solicitud->rfc = $request->rfc;
            $solicitud->telefono = $request->telefono;
            $solicitud->domicilio_calle = $request->calle;
            $solicitud->domicilio_numero = $request->num_ext;
            $solicitud->domicilio_colonia = $request->colonia;
            $solicitud->domicilio_ciudad = $request->ciudad;
            $solicitud->peso = $request->peso;
            $solicitud->estatura = $request->estatura;
            $solicitud->cp_fiscal = $request->cp_fiscal;
            $solicitud->domicilio_estado = $request->estado;
            $solicitud->liga_rfc = $request->liga_rfc;
            $solicitud->infonavit = $request->infonavit;
            $solicitud->fonacot = $request->fonacot;
            $solicitud->domicilio_comprobante = $request->domicilio_comprobante;
            $solicitud->departamento = $request->departamento;
            $solicitud->reingreso = $request->reingreso;
            $solicitud->rol = $request->rol;
            $solicitud->punto = $request->punto;
            $solicitud->empresa = $request->empresa;
            $solicitud->email = $request->email;
            $solicitud->sueldo_mensual = $request->sueldo_mensual;
            $solicitud->fecha_ingreso = $request->fecha_ingreso;
            $solicitud->status = 'En Proceso';
            $solicitud->observaciones = 'Solicitud en revisión';
            $solicitud->created_at = Carbon::now('America/Mexico_City');
            $solicitud->updated_at = Carbon::now('America/Mexico_City');

            $solicitud->save();

            return redirect()->route('sup.subirArchivosForm', ['id' => $solicitud->id, 'tipo' => $tipoSeleccionado]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al guardar la solicitud, intente nuevamente.');
        }
    }

    public function subirArchivosForm($id)
    {
        $tipo = request('tipo');
        $solicitud = SolicitudAlta::findOrFail($id);
        return view('supervisor.subirArchivosForm', compact('solicitud', 'tipo'));
    }

    public function guardarArchivos(Request $request, $id)
{
    $request->validate([
        'arch_acta_nacimiento' => 'nullable|file',
        'arch_solicitud_empleo' => 'nullable|file',
        'arch_curp' => 'nullable|file',
        'arch_ine' => 'nullable|file',
        'arch_comprobante_domicilio' => 'nullable|file',
        'arch_rfc' => 'nullable|file',
        'arch_comprobante_estudios' => 'nullable|file',
        'arch_carta_rec_laboral' => 'nullable|file',
        'arch_carta_rec_personal' => 'nullable|file',
        'arch_cartilla_militar' => 'nullable|file',
        'arch_infonavit' => 'nullable|file',
        'arch_fonacot' => 'nullable|file',
        'arch_licencia_conducir' => 'nullable|file',
        'arch_carta_no_penales' => 'nullable|file',
        'arch_foto' => 'nullable|file',
        'visa' => 'nullable|file',
        'pasaporte' => 'nullable|file',
    ]);

    $solicitudId = $id;
    $documentacion = DocumentacionAltas::firstOrNew(['solicitud_id' => $solicitudId]);
    $carpeta = 'solicitudesAltas/' . $solicitudId;

    $archivos = [
        'arch_acta_nacimiento',
        'arch_curp',
        'arch_ine',
        'arch_comprobante_domicilio',
        'arch_rfc',
        'arch_comprobante_estudios',
        'arch_carta_rec_laboral',
        'arch_carta_rec_personal',
        'arch_cartilla_militar',
        'arch_infonavit',
        'arch_fonacot',
        'arch_licencia_conducir',
        'arch_carta_no_penales',
        'arch_foto',
        'arch_solicitud_empleo',
        'visa',
        'pasaporte',
    ];

    foreach ($archivos as $campo) {
        if ($request->hasFile($campo)) {
            $archivo = $request->file($campo);
            $nombreArchivo = $campo . '.' . $archivo->getClientOriginalExtension();
            $ruta = $archivo->storeAs($carpeta, $nombreArchivo, 'public');
            $documentacion->$campo = 'storage/' . $ruta;
        }
    }

    $documentacion->solicitud_id = $solicitudId;
    $documentacion->save();

    $solicitud = SolicitudAlta::find($solicitudId);

    if(Auth::user()->rol == 'admin') {
        $docs = DocumentacionAltas::where('solicitud_id', $id)->first();

        $user = new User();
        $user->sol_alta_id = $solicitud->id;
        $user->sol_docs_id = $docs->id;
        $user->name = $solicitud->nombre . " " . $solicitud->apellido_paterno . " " . $solicitud->apellido_materno;
        $user->email = $solicitud->email;
        $user->password = Hash::make($solicitud->rfc);
        $user->fecha_ingreso = Carbon::now();
        $user->punto = $solicitud->punto;
        $user->rol = $solicitud->rol;
        $user->estatus = 'Activo';
        $user->empresa = $solicitud->empresa;
        $user->save();

        $solicitud->status = 'Aceptada';
        $solicitud->observaciones = 'Solicitud Aceptada.';
        $solicitud->save();
    }

    return redirect()->route('sup.nuevoUsuarioForm')->with('success', 'Documentación subida correctamente');
}


    public function historialSolicitudes()
    {
        $usuario = Auth::user();
        if($usuario->rol == 'Supervisor')
        {
            $solicitudes = SolicitudAlta::where('solicitante', $usuario)
            ->orderBy('created_at', 'desc')
            ->get();
        }else{
            $solicitudes = SolicitudAlta::orderBy('created_at', 'desc')
            ->get();
        }
        return view('supervisor.historialSolicitudes', compact('solicitudes'));
    }

    public function detalleSolicitud($id){
        $solicitud = SolicitudAlta::find($id);
        $documentacion = DocumentacionAltas::where('solicitud_id', $id)->first();
        return view('supervisor.detalleSolicitud', compact('solicitud', 'documentacion'));
    }

    public function editarSolicitudForm($id){
        $solicitud = SolicitudAlta::find($id);
        $documentacion = DocumentacionAltas::where('solicitud_id', $id)->first();
        return view('supervisor.editarSolicitudForm', compact('solicitud', 'documentacion'));
    }

    public function editarInformacionSolicitud(Request $request, $id){
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'apellido_paterno' => 'nullable|string|max:255',
                'apellido_materno' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'curp' => 'nullable|string|max:255',
                'nss' => ['nullable', 'digits:11'],
                'edo_civil' => 'nullable|string',
                'rfc' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:255',
                'calle' => 'nullable|string|max:255',
                'num_ext' => 'nullable|integer',
                'colonia' => 'nullable|string|max:255',
                'ciudad' => 'nullable|string|max:255',
                'peso' => 'nullable|string|max:255',
                'estatura' => 'nullable|string|max:255',
                'cp_fiscal' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:255',
                'domicilio_comprobante' => 'nullable|string|max:255',
                'fonacot' => 'nullable|string|max:255',
                'infonavit' => 'nullable|string|max:255',
                'liga_rfc' => 'nullable|string|max:255',
                'rol' => 'nullable|string|max:255',
                'fecha_ingreso' => 'nullable|date',
                'punto' => 'nullable|string|max:255',
                'empresa' => 'nullable|string',
                'sueldo_mensual' => 'nullable|string',
                'email' => 'nullable|email|unique:solicitud_altas,email,' . $id . ',id',

            ]);

            $solicitud = SolicitudAlta::findOrFail($id);
            $id=$solicitud->id;
            $solicitud->solicitante = auth()->user()->name;
            $solicitud->nombre = $request->name;
            $solicitud->apellido_paterno = $request->apellido_paterno;
            $solicitud->apellido_materno = $request->apellido_materno;
            $solicitud->fecha_nacimiento = $request->fecha_nacimiento;
            $solicitud->curp = $request->curp;
            $solicitud->nss = $request->nss;
            $solicitud->estado_civil = $request->edo_civil;
            $solicitud->rfc = $request->rfc;
            $solicitud->telefono = $request->telefono;
            $solicitud->domicilio_calle = $request->calle;
            $solicitud->domicilio_numero = $request->num_ext;
            $solicitud->domicilio_colonia = $request->colonia;
            $solicitud->domicilio_ciudad = $request->ciudad;
            $solicitud->cp_fiscal = $request->cp_fiscal;
            $solicitud->domicilio_estado = $request->estado;
            $solicitud->domicilio_comprobante = $request->domicilio_comprobante;
            $solicitud->liga_rfc = $request->liga_rfc;
            $solicitud->fecha_ingreso = $request->fecha_ingreso;
            $solicitud->peso = $request->peso;
            $solicitud->estatura = $request->estatura;
            $solicitud->rol = $request->rol;
            $solicitud->punto = $request->punto;
            $solicitud->empresa = $request->empresa;
            $solicitud->sueldo_mensual = $request->sueldo_mensual;
            $solicitud->email = $request->email;
            $solicitud->ultima_edicion = Auth::user()->name . " " . Carbon::now('America/Mexico_City');

            if(Auth::user()->rol == 'admin' || Auth::user()->solicitudAlta->departamento == 'Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RECURSOS HUMANOS' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RH' || Auth::user()->solicitudAlta->rol == 'AUX RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'Aux RH' || Auth::user()->rol == 'AUXILIAR RECURSOS HUMANOS'){
                $solicitud->status = 'Aceptada';
                $solicitud->observaciones = 'Solicitud Aceptada.';
            }else{
                $solicitud->status = 'En Proceso';
                $solicitud->observaciones = 'Cambios realizados, en espera de revisión.';
            }
            $solicitud->save();
            $user = User::where('sol_alta_id', $id)->first();

            $documentacion = DocumentacionAltas::where('solicitud_id', $id)->first();
            if(Auth()->user()->rol == 'admin' || Auth::user()->solicitudAlta->departamento == 'Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RECURSOS HUMANOS' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RH' || Auth::user()->solicitudAlta->rol == 'AUX RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'Aux RH' || Auth::user()->rol == 'AUXILIAR RECURSOS HUMANOS'){
                $user = User::where('sol_alta_id', $id)->first();
                $user->name = $solicitud->nombre . " " . $solicitud->apellido_paterno . " " . $solicitud->apellido_materno;
                $user->email = $solicitud->email;
                $user->punto = $solicitud->punto;
                $user->rol = $solicitud->rol;

                $user->empresa = $solicitud->empresa;

                $user->save();
            }
            $tipo = $solicitud->tipo_empleado;
            return view('supervisor.editarArchivosForm', compact('solicitud','id' ,'documentacion', 'user', 'tipo'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar la solicitud: ' . $e->getMessage());
        }
    }

    public function subirArchivosEditados(Request $request, $id)
    {
        $solicitudId = $id;
        $sol = SolicitudAlta::find($solicitudId);
        $user = User::where('sol_alta_id', $solicitudId)->first();
        $documentacion = DocumentacionAltas::firstOrNew(['solicitud_id' => $solicitudId]);
        $carpeta = 'solicitudesAltas/' . $solicitudId;

        $camposArchivos = [
            'arch_acta_nacimiento',
            'arch_curp',
            'arch_ine',
            'arch_comprobante_domicilio',
            'arch_rfc',
            'arch_comprobante_estudios',
            'arch_carta_rec_laboral',
            'arch_carta_rec_personal',
            'arch_cartilla_militar',
            'arch_contrato',
            'arch_nss',
            'arch_antidoping',
            'arch_infonavit',
            'arch_fonacot',
            'arch_licencia_conducir',
            'arch_carta_no_penales',
            'arch_foto',
            'arch_solicitud_empleo',
            'visa',
            'pasaporte',
        ];

        foreach ($camposArchivos as $campo) {
            if ($request->hasFile($campo)) {
                $nuevoArchivo = $request->file($campo);
                $nombreArchivo = $campo . '.' . $nuevoArchivo->getClientOriginalExtension();
                $rutaCompleta = $carpeta . '/' . $nombreArchivo;

                if (!empty($documentacion->$campo)) {
                    $rutaAnterior = str_replace('storage/', '', $documentacion->$campo);
                    if (Storage::disk('public')->exists($rutaAnterior)) {
                        Storage::disk('public')->delete($rutaAnterior);
                    }
                }

                $nuevoArchivo->storeAs($carpeta, $nombreArchivo, 'public');
                $documentacion->$campo = 'storage/' . $rutaCompleta;
            }
        }

        $documentacion->solicitud_id = $solicitudId;
        $documentacion->save();
        if(Auth()->user()->rol == 'admin' || Auth::user()->solicitudAlta->departamento == 'Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RECURSOS HUMANOS' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RH' || Auth::user()->solicitudAlta->rol == 'AUX RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'Aux RH' || Auth::user()->rol == 'AUXILIAR RECURSOS HUMANOS')
            $sol->observaciones = 'Solicitud Aceptada.';
        else
            $sol->observaciones = 'Documentación actualizada, en espera de revisión.';
        $sol->save();

        if(Auth()->user()->rol == 'admin' || Auth::user()->solicitudAlta->departamento == 'Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RECURSOS HUMANOS' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RH' || Auth::user()->solicitudAlta->rol == 'AUX RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar Recursos Humanos' || Auth::user()->solicitudAlta->rol == 'Aux RH')
            return redirect()->route('user.verFicha', $user->id)->with('success', 'Documentos actualizados correctamente.');
        else
            return redirect()->route('sup.solicitud.detalle', $solicitudId)->with('success', 'Documentos actualizados correctamente.');
    }

    public function solicitarBajaForm()
    {
        $usuario = auth()->user();

        $puntoUsuarioRaw = Auth::user()->punto;
        $puntoUsuario = null;
        $subpuntosZona = collect();

        $punto = Punto::where('nombre', $puntoUsuarioRaw)->first();
        if ($punto) {
            $puntoUsuario = $punto;
        } else {
            $subpunto = Subpunto::where('nombre', $puntoUsuarioRaw)->first();
            if ($subpunto) {
                $puntoUsuario = $subpunto;

                $zona = $subpunto->zona;
                if ($zona) {
                    $subpuntosZona = Subpunto::where('zona', $zona)->pluck('nombre');
                }
            } else {
                $subpuntoPorCodigo = Subpunto::where('codigo', $puntoUsuarioRaw)->first();
                if ($subpuntoPorCodigo) {
                    $puntoUsuario = $subpuntoPorCodigo;
                    $zona = $subpuntoPorCodigo->zona;
                    if ($zona) {
                        $subpuntosZona = Subpunto::where('zona', $zona)->pluck('nombre');
                    }
                }
            }
        }
        $elementos = User::where('empresa', $usuario->empresa)
            ->where(function ($query) use ($usuario, $subpuntosZona) {
                $query->where('punto', $usuario->punto)
                    ->orWhereIn('punto', $subpuntosZona);
            })
            ->get();

        return view('supervisor.solicitarBajaForm', compact('elementos'));
    }

    public function solicitarBajaVista($id){
        $user = User::find($id);
        $solicitud = SolicitudAlta::where('id', $user->sol_alta_id)->first();
        $solicitudpendiente = SolicitudBajas::where('user_id', $user->id)->where('estatus', 'En Proceso')->first();
        return view('supervisor.vistaSolicitarBaja', compact('user','solicitud','solicitudpendiente'));
    }

    public function guardarBajaNueva(Request $request, $id)
    {
        $request->validate([
            'fecha_hoy' => 'required|date',
            'incapacidad' => 'nullable|string|max:255',
            'por' => 'required|in:Ausentismo,Separación Voluntaria,Renuncia',
            'ultima_asistencia' => 'nullable|date',
            'motivo' => 'nullable|string',
            'descuento' => 'nullable|integer',
            'archivo_baja' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'arch_equipo_entregado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $user = User::findOrFail($id);

        $solicitud = new SolicitudBajas();
        $solicitud->user_id = $user->id;
        $solicitud->fecha_solicitud = $request->fecha_hoy;
        $solicitud->motivo = $request->motivo;
        $solicitud->incapacidad = $request->incapacidad;
        $solicitud->por = $request->por;
        $solicitud->ultima_asistencia = $request->ultima_asistencia;
        $solicitud->archivo_baja = ' ';
        $solicitud->arch_equipo_entregado = ' ';
        $solicitud->descuento = $request->descuento;
        $solicitud->estatus = 'En Proceso';
        $solicitud->observaciones = 'Solicitud de baja en proceso';

        try {
            $solicitud->save();

            $carpeta = 'solicitudesBajas/' . $solicitud->id;

            Storage::disk('public')->makeDirectory($carpeta);

            $archivos = [
                'archivo_baja',
                'arch_equipo_entregado',
            ];

            foreach ($archivos as $campo) {
                if ($request->hasFile($campo)) {
                    $archivo = $request->file($campo);
                    $nombreArchivo = $campo . '_' . time() . '.' . $archivo->getClientOriginalExtension();
                    $ruta = $archivo->storeAs($carpeta, $nombreArchivo, 'public');

                    $solicitud->$campo = $ruta;
                }
            }

            $solicitud->save();

            return redirect()->route('dashboard')->with('success', 'Solicitud de baja enviada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al enviar la solicitud: ' . $e->getMessage());
        }
    }

    public function historialBajas()
    {
        $user = Auth::user();

        $puntoUsuarioRaw = $user->punto;
        $subpuntosZona = collect();

        $punto = \App\Models\Punto::where('nombre', $puntoUsuarioRaw)->first();
        if (!$punto) {
            $subpunto = \App\Models\Subpunto::where('nombre', $puntoUsuarioRaw)->first();
            if (!$subpunto) {
                $subpunto = \App\Models\Subpunto::where('codigo', $puntoUsuarioRaw)->first();
            }

            if ($subpunto && $subpunto->zona) {
                $subpuntosZona = \App\Models\Subpunto::where('zona', $subpunto->zona)
                    ->pluck('nombre')
                    ->merge(
                        \App\Models\Subpunto::where('zona', $subpunto->zona)->pluck('codigo')
                    );
            }
        }

        $solicitudes = \App\Models\SolicitudBajas::whereHas('user', function ($query) use ($user, $subpuntosZona) {
            $query->where('empresa', $user->empresa)
                ->where('por', 'Renuncia')
                ->where(function ($q) use ($user, $subpuntosZona) {
                    $q->where('punto', $user->punto);
                    if ($subpuntosZona->isNotEmpty()) {
                        $q->orWhereIn('punto', $subpuntosZona);
                    }
                });
        })->with('user')->get();

        return view('supervisor.historialBajas', compact('solicitudes'));
    }

    public function listaAsistencia(){
        $user = Auth::user();

        $asistenciasHoy = 0;
        $supervisores = User::where('estatus', 'Activo')
            ->whereRaw("LOWER(rol) LIKE ?", ['%supervisor%'])
            ->get();

        $supervisores->map(function ($supervisor) {
            $supervisor->envio_asistencia = Asistencia::where('user_id', $supervisor->id)
                ->whereDate('fecha', Carbon::today())
                ->exists() ? 'Sí' : 'No';

            return $supervisor;
        });

        $asistencia_hoy = Asistencia::where('fecha', Carbon::now()->toDateString())
                        ->where('user_id', $user->id)
                        ->get();

        $puntoUsuarioRaw = $user->punto;
        $subpuntosZona = collect();

        $punto = \App\Models\Punto::where('nombre', $puntoUsuarioRaw)->first();
        if (!$punto) {
            $subpunto = \App\Models\Subpunto::where('nombre', $puntoUsuarioRaw)->first();
            if (!$subpunto) {
                $subpunto = \App\Models\Subpunto::where('codigo', $puntoUsuarioRaw)->first();
            }

            if ($subpunto && $subpunto->zona) {
                $subpuntosZona = \App\Models\Subpunto::where('zona', $subpunto->zona)
                    ->pluck('nombre')
                    ->merge(
                        \App\Models\Subpunto::where('zona', $subpunto->zona)->pluck('codigo')
                    );
            }
        }

        $elementos = \App\Models\User::where('empresa', $user->empresa)
            ->where('estatus', 'Activo')
            ->where('rol', '!=', 'Supervisor')
            ->where('rol', 'GUARDIA')
            ->where(function ($query) use ($user, $subpuntosZona) {
                $query->where('punto', $user->punto);
                if ($subpuntosZona->isNotEmpty()) {
                    $query->orWhereIn('punto', $subpuntosZona);
                }
            })
            ->with('solicitudAlta.documentacion')
            ->get();
        return view('supervisor.listaAsistencia', compact('elementos', 'asistencia_hoy', 'supervisores'));
    }

    public function guardarAsistencias(Request $request)
{
    $validated = $request->validate([
        'asistencias' => 'required|array',
        'asistencias.*' => 'integer',
        'foto_evidencia' => 'nullable|array',
        'foto_evidencia.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        'observaciones' => 'nullable|string|max:255',
        'coberturas' => 'nullable|array',
        'coberturas.*' => 'required|string',
    ]);

    $user = Auth::user();
    $now = now('America/Mexico_City');

    $puntoUsuarioRaw = $user->punto;
    $subpuntosZona = collect();

    $punto = \App\Models\Punto::where('nombre', $puntoUsuarioRaw)->first();
    if (!$punto) {
        $subpunto = \App\Models\Subpunto::where('nombre', $puntoUsuarioRaw)->first();
        if (!$subpunto) {
            $subpunto = \App\Models\Subpunto::where('codigo', $puntoUsuarioRaw)->first();
        }

        if ($subpunto && $subpunto->zona) {
            $subpuntosZona = \App\Models\Subpunto::where('zona', $subpunto->zona)
                ->pluck('nombre')
                ->merge(
                    \App\Models\Subpunto::where('zona', $subpunto->zona)->pluck('codigo')
                );
        }
    }


    $asistencias = $request->input('asistencias', []);
    $todosUsuarios = User::where('empresa', $user->empresa)
            ->where('estatus', 'Activo')
            ->where('rol', '!=', 'Supervisor')
            ->where(function ($query) use ($user, $subpuntosZona) {
                $query->where('punto', $user->punto);
                if ($subpuntosZona->isNotEmpty()) {
                    $query->orWhereIn('punto', $subpuntosZona);
                }
            })
        ->pluck('id')
        ->toArray();

    $faltas = array_values(array_diff($todosUsuarios, $asistencias));

    $coberturasRaw = $request->input('coberturas', []);
    $coberturas = array_map(function ($item) {
        return json_decode($item, true);
    }, $coberturasRaw);

    session([
        'asistencias_data' => [
            'asistencias' => $asistencias,
            'foto_evidencia' => $request->file('foto_evidencia', []),
            'observaciones' => $request->input('observaciones'),
            'coberturas' => $coberturas,
            'faltas' => $faltas,
            'fecha' => $now->toDateString(),
            'hora' => $now->toTimeString(),
        ]
    ]);

    return redirect()->route('asistencias.confirmarFaltas');
}

    public function confirmarFaltas()
    {
        $data = session('asistencias_data');

        if (!$data) {
            return redirect()->route('dashboard')->with('error', 'No hay datos de asistencia pendientes.');
        }

        $faltantes = User::whereIn('id', $data['faltas'])
        ->with('solicitudAlta.documentacion')->get();

        return view('supervisor.confirmar-faltas', compact('faltantes'));
    }

public function finalizarAsistencia(Request $request)
{
    $data = session('asistencias_data');

    if (!$data) {
        return redirect()->route('dashboard')->with('error', 'No hay datos para finalizar.');
    }

    DB::beginTransaction();
    try {
        $user = Auth::user();
        $descansan = $request->input('descansan', []);
        Log::info('Descansan recibidos:', $descansan);
        $faltasFinales = array_values(array_diff($data['faltas'], $descansan));

        $rutaBase = "asistencias/" . Str::slug($user->name) . "/" . $data['fecha'];
        Storage::disk('public')->makeDirectory($rutaBase, 0755, true);

        $fotosAsistentes = [];
        foreach ($data['foto_evidencia'] ?? [] as $elementoId => $foto) {
            if ($foto && $foto->isValid()) {
                $extension = $foto->extension();
                $nombreArchivo = $elementoId . time() . '.' . $extension;
                $rutaCompleta = $foto->storeAs($rutaBase, $nombreArchivo, 'public');
                $fotosAsistentes[$elementoId] = $rutaCompleta;
            }
        }
        Log::info('Coberturas a guardar:', $data['coberturas']);
        Asistencia::create([
            'user_id' => $user->id,
            'fecha' => $data['fecha'],
            'hora_asistencia' => $data['hora'],
            'elementos_enlistados' => json_encode($data['asistencias']),
            'faltas' => json_encode($faltasFinales),
            'descansos' => json_encode($descansan),
            'coberturas' => json_encode($data['coberturas']),
            'observaciones' => $data['observaciones'] ?: 'Ninguna',
            'punto' => $user->punto,
            'empresa' => $user->empresa,
            'fotos_asistentes' => json_encode($fotosAsistentes),
        ]);

        DB::commit();
        session()->forget('asistencias_data');

        return redirect()->route('dashboard')->with('success', 'Asistencia registrada con faltas y descansos.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al finalizar asistencia: '.$e->getMessage());

        return back()->with('error', 'Error al finalizar asistencia: '.$e->getMessage());
    }
}

    public function verAsistencias($id){
        $user = User::find($id);

        if($user->rol == 'Supervisor')
        {
            $asistencias = Asistencia::where('user_id', $user->id)
                ->with('usuario')
                ->orderBy('fecha', 'desc')
                ->get();
        }else{
            $asistencias = Asistencia::orderBy('fecha', 'desc')
                ->with('usuario')
                ->get();
        }

        return view('supervisor.verAsistencias', compact('asistencias', 'user'));
    }

    public function detalleAsistencia($id){
        $asistencia = Asistencia::find($id);
        $idsAsistieron = json_decode($asistencia->elementos_enlistados, true) ?? [];
        $idsFaltaron = json_decode($asistencia->faltas, true) ?? [];
        $idsDescansaron = json_decode($asistencia->descansos, true) ?? [];
        $coberturas = json_decode($asistencia->coberturas, true) ?? [];
        $idsCoberturas = collect($coberturas)->pluck('id')->toArray();

        $usuariosAsistieron = User::whereIn('id', $idsAsistieron)->with('solicitudAlta.documentacion')->get();
        $usuariosFaltaron = User::whereIn('id', $idsFaltaron)->with('solicitudAlta.documentacion')->get();
        $usuariosDescansaron = User::whereIn('id', $idsDescansaron)->with('solicitudAlta.documentacion')->get();
        $usuariosCoberturas = User::whereIn('id', $idsCoberturas)
            ->with('solicitudAlta.documentacion')
            ->get();


        $fotos = json_decode($asistencia->fotos_asistentes, true) ?? [];
        if (is_array($fotos)) {
            foreach ($fotos as $id => $path) {
                $fotos[$id] = asset('storage/' . $path);
            }
        }
        $asistencia->usuarios_coberturas = $usuariosCoberturas;
        $asistencia->usuarios_enlistados = $usuariosAsistieron;
        $asistencia->usuarios_faltantes = $usuariosFaltaron;
        $asistencia->usuarios_descansos = $usuariosDescansaron;
        $asistencia->fotos_asistentes = $fotos;

        return view('supervisor.detalleAsistencia', compact('asistencia'));
    }

    public function verFechaAsistencias(Request $request)
    {
        $user = Auth::user();
        $fechaSeleccionada = $request->input('fecha');

        $query = Asistencia::where('user_id', $user->id);

        if ($fechaSeleccionada) {
            $query->whereDate('fecha', $fechaSeleccionada);
        }

        $asistencias = $query->orderBy('fecha', 'desc')->get();

        $asistenciasElementos = $asistencias->map(function ($asistencia) {
            $ids = json_decode($asistencia->elementos_enlistados, true);
            $usuarios = User::whereIn('id', $ids ?: [])->with('solicitudAlta.documentacion')->get();
            $asistencia->usuarios_enlistados = $usuarios;
            return $asistencia;
        });

        return view('supervisor.verAsistencias', compact('asistenciasElementos', 'fechaSeleccionada'));
    }

    public function solicitudesVacaciones()
    {
        $user = Auth::user();
        $puntoUsuarioRaw = $user->punto;
        $subpuntosZona = collect();

        if ($user->rol == 'admin') {
            $solicitudes = SolicitudVacaciones::where('estatus', 'En Proceso')->get();
        } else {
            $punto = \App\Models\Punto::where('nombre', $puntoUsuarioRaw)->first();

            if (!$punto) {
                $subpunto = \App\Models\Subpunto::where('nombre', $puntoUsuarioRaw)->first();

                if (!$subpunto) {
                    $subpunto = \App\Models\Subpunto::where('codigo', $puntoUsuarioRaw)->first();
                }

                if ($subpunto && $subpunto->zona) {
                    $subpuntosZona = \App\Models\Subpunto::where('zona', $subpunto->zona)
                        ->pluck('nombre')
                        ->merge(
                            \App\Models\Subpunto::where('zona', $subpunto->zona)->pluck('codigo')
                        );
                }
            }

            $solicitudes = SolicitudVacaciones::where('estatus', 'En Proceso')
                ->whereHas('user', function ($query) use ($user, $subpuntosZona) {
                    $query->where('empresa', $user->empresa)
                        ->where(function ($q) use ($user, $subpuntosZona) {
                            $q->where('punto', $user->punto);
                            if ($subpuntosZona->isNotEmpty()) {
                                $q->orWhereIn('punto', $subpuntosZona);
                            }
                        });
                })
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('supervisor.solicitudesVacaciones', compact('solicitudes'));
    }

    public function aceptarSolicitudVacaciones($id){
        $solicitud = SolicitudVacaciones::find($id);
        $solicitud->estatus = 'En Proceso';
        $solicitud->observaciones = 'Solicitud aceptada, falta subir archivo de solicitud.';
        $solicitud->autorizado_por = Auth::user()->name;

        $solicitud->save();
        if(Auth::user()->rol == 'admin')
            return redirect()->route('admin.solicitudesVacaciones')->with('success', 'Solicitud de vacaciones respondida correctamente, a la espera del archivo de solicitud.');
        else
            return redirect()->route('sup.solicitudesVacaciones')->with('success', 'Solicitud de vacaciones respondida correctamente, a la espera del archivo de solicitud.');
    }

    public function rechazarSolicitudVacaciones($id){
        $solicitud = SolicitudVacaciones::find($id);
        $solicitud->estatus = 'Rechazada';
        $solicitud->observaciones = 'Solicitud de vacaciones rechazada';

        $solicitud->save();

        return redirect()->route('sup.solicitudesVacaciones')->with('success', 'Solicitud de vacaciones rechazada correctamente.');
    }

    public function verSolicitudBaja($id){
        $solicitudBaja = SolicitudBajas::findOrFail($id);
        $user = User::findOrFail($solicitudBaja->user_id);
        $solicitudAlta = SolicitudAlta::findOrFail($user->sol_alta_id);

        return view('supervisor.detalleBaja', compact('user','solicitudAlta', 'solicitudBaja'));
    }

    public function tiemposExtras()
    {
        $user = Auth::user();
        $puntoUsuarioRaw = $user->punto;
        $subpuntosZona = collect();

        $punto = \App\Models\Punto::where('nombre', $puntoUsuarioRaw)->first();

        if (!$punto) {
            $subpunto = \App\Models\Subpunto::where('nombre', $puntoUsuarioRaw)->first();

            if (!$subpunto) {
                $subpunto = \App\Models\Subpunto::where('codigo', $puntoUsuarioRaw)->first();
            }

            if ($subpunto && $subpunto->zona) {
                $subpuntosZona = \App\Models\Subpunto::where('zona', $subpunto->zona)
                    ->pluck('nombre')
                    ->merge(
                        \App\Models\Subpunto::where('zona', $subpunto->zona)->pluck('codigo')
                    );
            }
        }

        $elementos = \App\Models\User::where('empresa', $user->empresa)
            ->where('estatus', 'Activo')
            ->where('rol', '!=', 'Supervisor')
            ->where(function ($query) use ($user, $subpuntosZona) {
                $query->where('punto', $user->punto);
                if ($subpuntosZona->isNotEmpty()) {
                    $query->orWhereIn('punto', $subpuntosZona);
                }
            })
            ->with('solicitudAlta.documentacion')
            ->get();

        return view('supervisor.tiemposExtras', compact('elementos'));
    }

    public function tiemposExtrasForm($id){
        $supervisor = Auth::user();
        $extraHoy = TiemposExtra::where('user_id', $id)
                ->whereDate('fecha', Carbon::now()->toDateString())
                ->first();
        $elemento = User::where('id', $id)
            ->with('solicitudAlta.documentacion')
            ->firstOrFail();

        $foto = optional($elemento->solicitudAlta->documentacion)->arch_foto;
        $foto = $foto ? asset($foto) : null;
        $solicitud = SolicitudAlta::where('id', $elemento->sol_alta_id)->first();

        return view('supervisor.tiemposExtrasForm', compact('supervisor', 'elemento','solicitud', 'foto', 'extraHoy'));
    }

    public function guardarTiempoExtra(Request $request, $id){
        $request->validate([
            'user_id',
            'fecha' => [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        Carbon::parse($value, 'America/Mexico_City');
                    } catch (\Exception $e) {
                        $fail('La fecha no es válida.');
                    }
                }
            ],
            'hora_inicio',
            'hora_fin',
            'observaciones',
        ]);

        $horaInicio = Carbon::createFromFormat('H:i', $request->hora_inicio);
        $horaFin = Carbon::createFromFormat('H:i', $request->hora_fin);

        $diferenciaSegundos = abs($horaFin->floatDiffInSeconds($horaInicio));
        $totalHoras = gmdate('H:i:s', $diferenciaSegundos);

        $tiempoExtra = new TiemposExtra();
        $tiempoExtra->user_id = $request->user_id;
        $tiempoExtra->fecha = $request->fecha;
        $tiempoExtra->hora_inicio = $horaInicio;
        $tiempoExtra->hora_fin = $horaFin;
        $tiempoExtra->total_horas = $totalHoras;
        $tiempoExtra->autorizado_por = Auth::user()->name;
        if($request->observaciones){
            $tiempoExtra->observaciones = $request->observaciones;
        }else{
            $tiempoExtra->observaciones = 'Ninguna';
        }
        $tiempoExtra->save();

        return redirect()->route('sup.tiemposExtras')->with('success', 'Tiempo extra registrado correctamente.');
    }

    public function historialTiemposExtras(){
        $supervisor = Auth::user();
        $tiemposExtras = TiemposExtra::whereHas('user', function ($query) use ($supervisor) {
            $query->where('empresa', $supervisor->empresa)
                ->where('punto', $supervisor->punto);
        })
        ->with('user')
        ->orderBy('fecha', 'desc')
        ->get();

        return view('supervisor.historialTiemposExtras', compact('tiemposExtras'));
    }

public function gestionUsuarios()
{
    $user = Auth::user();
    $puntoUsuarioRaw = $user->punto;
    $subpuntosZona = collect();

    Log::info("Punto del usuario: " . $puntoUsuarioRaw);

    $punto = Punto::where('nombre', $puntoUsuarioRaw)->first();

    if ($punto) {
        Log::info("Es punto principal: " . $punto->nombre);
    } else {
        $subpunto = Subpunto::where('nombre', $puntoUsuarioRaw)->first()
                  ?? Subpunto::where('codigo', $puntoUsuarioRaw)->first();

        if ($subpunto) {
            Log::info("Subpunto encontrado: " . $subpunto->nombre . " | Zona: " . $subpunto->zona);

            if ($subpunto->zona) {
                $subpuntosZona = Subpunto::where('zona', $subpunto->zona)->pluck('nombre');
                Log::info("Subpuntos de la zona: ", $subpuntosZona->toArray());
            }
        } else {
            Log::warning("No se encontró subpunto ni por nombre ni por código.");
        }
    }

    $usuarios = User::where('empresa', $user->empresa)
        ->where('estatus', 'Activo')
        ->where('rol', '!=', 'Supervisor')
        ->where(function ($query) use ($user, $subpuntosZona) {
            $query->where('punto', $user->punto);
            if ($subpuntosZona->isNotEmpty()) {
                $query->orWhereIn('punto', $subpuntosZona);
            }
        })
        ->get();

    Log::info("Usuarios encontrados: " . $usuarios->count());

    return view('supervisor.gestionUsuarios', compact('usuarios'));
}


    public function coberturaTurnoForm($id){
        $elemento = User::find($id);
        $hoy = Carbon::now('America/Mexico_City')->toDateString();
        $solicitud = SolicitudAlta::where('id', $elemento->sol_alta_id)->first();
        $coberturaHoy = CubrirTurno::where('user_id', $id)
            ->where('fecha',$hoy)->first();
        $cobertura = 0;
        $coberturaHoy ? $cobertura = 1 : $cobertura = 0;

        return view('supervisor.coberturaTurnoForm', compact('elemento','solicitud', 'cobertura'));
    }

    public function guardarCoberturaTurno(Request $request, $id){
        $request->validate([
            'user_id',
            'fecha',
            'hora_inicio',
            'hora_fin',
            'punto_procedencia',
            'punto_cobertura',
            'observaciones',
        ]);

        $user_cubre = User::find($id);
        $fecha = Carbon::createFromFormat('Y-m-d', $request->fecha);
        $horaInicio = Carbon::createFromFormat('H:i', $request->hora_inicio);
        $horaFin = Carbon::createFromFormat('H:i', $request->hora_fin);

        $turno = new CubrirTurno();
        $turno->user_id = $user_cubre->id;
        $turno->fecha = $fecha;
        $turno->hora_inicio = $horaInicio;
        $turno->hora_fin = $horaFin;
        $turno->punto_procedencia = $request->punto_procedencia;
        $turno->punto_cobertura = $request->punto_cobertura;
        $turno->autorizado_por = Auth::user()->id;
        $turno->observaciones = $request->observaciones;
        $turno->save();

        return redirect()->route('sup.tiemposExtras')->with('success', 'Cobertura de turno registrada correctamente.');
    }

    public function descargarSolicitudVacaciones($id)
{
    $solicitud = SolicitudVacaciones::with('user')->findOrFail($id);
    $user = $solicitud->user;

    $fechaIngreso = Carbon::parse($user->fecha_ingreso);
    $fechaActual = Carbon::now('America/Mexico_City');
    $mesesLaborados = 0;
    $inicioPeriodo = $fechaIngreso;
    $finPeriodo = $fechaIngreso->copy()->addYear();
    $aniversario = Carbon::createFromDate($fechaActual->year, $fechaIngreso->month, $fechaIngreso->day);

    if ($aniversario->isFuture()) {
        $inicioPeriodo = $aniversario->copy()->subYear();
        $finPeriodo = $aniversario;
    } else {
        $inicioPeriodo = $aniversario;
        $finPeriodo = $aniversario->copy()->addYear();
    }

    $antiguedadAnios = floor($fechaIngreso->floatDiffInYears($fechaActual));

    if ($antiguedadAnios >= 1) {
        $anioTexto = ($antiguedadAnios == 1) ? 'AÑO' : 'AÑOS';
        $antiguedad = $antiguedadAnios . ' ' . $anioTexto;
        $periodo = $antiguedadAnios;
    } else {
        $mesesLaborados = (int) $fechaIngreso->diffInMonths($fechaActual);
        $antiguedad = $mesesLaborados . ' ' . ($mesesLaborados === 1 ? 'MES' : 'MESES');
        $periodo = 1;
    }

    $pdf = Pdf::loadView('pdf.formatoVacaciones', compact(
        'user', 'solicitud', 'inicioPeriodo', 'finPeriodo', 'antiguedad', 'periodo', 'mesesLaborados', 'antiguedadAnios'
    ));

    return $pdf->download('SOLICITUD DE VACACIONES.pdf');
}

    public function subirArchivo(Request $request, $id)
{
    $request->validate([
        'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
    ]);

    try {
        $solicitud = SolicitudVacaciones::findOrFail($id);

        if ($solicitud->archivo_solicitud && Storage::exists($solicitud->archivo_solicitud)) {
            Storage::delete($solicitud->archivo_solicitud);
        }

        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $ruta = 'solicitudesVacaciones/' . $solicitud->id;
            $extension = $archivo->getClientOriginalExtension();

            $nombreArchivo = 'arch_vacaciones.' . $extension;

            $rutaArchivo = $archivo->storeAs($ruta, $nombreArchivo, 'public');

            $solicitud->archivo_solicitud = $rutaArchivo;
            $solicitud->estatus = 'Aceptada';
            $solicitud->observaciones = 'Solicitud de vacaciones aceptada';
            $solicitud->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Archivo subido correctamente',
            'file_path' => Storage::url($rutaArchivo)
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al subir archivo: ' . $e->getMessage()
        ], 500);
    }
}

    public function solicitarVacacionesElemento()
    {
        $user = Auth::user();
        $puntoUsuarioRaw = $user->punto;
        $subpuntosZona = collect();

        $punto = \App\Models\Punto::where('nombre', $puntoUsuarioRaw)->first();
        if (!$punto) {
            $subpunto = \App\Models\Subpunto::where('nombre', $puntoUsuarioRaw)->first();
            if (!$subpunto) {
                $subpunto = \App\Models\Subpunto::where('codigo', $puntoUsuarioRaw)->first();
            }

            if ($subpunto && $subpunto->zona) {
                $subpuntosZona = \App\Models\Subpunto::where('zona', $subpunto->zona)
                    ->pluck('nombre')
                    ->merge(
                        \App\Models\Subpunto::where('zona', $subpunto->zona)->pluck('codigo')
                    );
            }
        }

        $elementos = \App\Models\User::where('empresa', $user->empresa)
            ->where('estatus', 'Activo')
            ->where('rol', '!=', 'Supervisor')
            ->where(function ($query) use ($user, $subpuntosZona) {
                $query->where('punto', $user->punto);
                if ($subpuntosZona->isNotEmpty()) {
                    $query->orWhereIn('punto', $subpuntosZona);
                }
            })
            ->with('solicitudAlta.documentacion')
            ->get();
        return view('supervisor.solicitarVacacionesElemento', compact('elementos'));
    }

    public function vacacionesElementoForm($id){
        $user = User::find($id);
        $fechaIngreso = Carbon::parse($user->fecha_ingreso);
        $fechaActual = now('America/Mexico_City');
        $antiguedad = (int) floor(Carbon::parse($user->fecha_ingreso)->floatDiffInYears(now('America/Mexico_City')));

        if ($antiguedad === 0) {
            $mesesLaborados = (int) $fechaIngreso->diffInMonths($fechaActual);
        } else {
            $mesesLaborados = 0;
        }

        if($antiguedad <2){
            $dias=12;
        }elseif($antiguedad ==2){
            $dias=14;
        }elseif($antiguedad ==3){
            $dias=16;
        }elseif($antiguedad ==4){
            $dias=18;
        }elseif($antiguedad ==5){
            $dias=20;
        }elseif($antiguedad>5 && $antiguedad<=10){
            $dias=22;
        }elseif($antiguedad>10 && $antiguedad<=15){
            $dias=24;
        }elseif($antiguedad>15 && $antiguedad<=20){
            $dias=26;
        }elseif($antiguedad>20 && $antiguedad<=25){
            $dias=28;
        }elseif($antiguedad>25 && $antiguedad<=30){
            $dias=30;
        }else{
            $dias=32;
        }

        $diasDisponibles = $dias;
        $diasUtilizados = 0;
        $fechaIngreso = Carbon::parse($user->fecha_ingreso);
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
            ->where('periodo', '>=', $antiguedad)
            ->where('created_at', '>=', $aniversario)
            ->get();

        foreach ($vacacionesTomadas as $vacacion) {
            $diasDisponibles -= $vacacion->dias_solicitados;
            $diasUtilizados += $vacacion->dias_solicitados;
        }

        $solicitud = SolicitudAlta::where('id', $user->sol_alta_id)->first();
        $documentacion = DocumentacionAltas::where('solicitud_id', $user->sol_alta_id)->first();

        return view('users.solicitarVacacionesForm', compact('user','solicitud', 'documentacion', 'antiguedad','dias', 'diasDisponibles', 'diasUtilizados', 'mesesLaborados'));
    }

}
