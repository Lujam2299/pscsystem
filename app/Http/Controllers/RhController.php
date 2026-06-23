<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use setasign\Fpdi\Fpdi;
use Mpdf\Mpdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\SolicitudAlta;
use App\Models\SolicitudVacaciones;
use App\Models\DocumentacionAltas;
use App\Models\SolicitudBajas;
use App\Models\User;
use App\Models\Punto;
use App\Models\Asistencia;
use App\Models\FaltaJustificada;
use App\Models\Retardo;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Hash;

class RhController extends Controller
{
    public function solicitudesAltas(){
        $solicitudes = SolicitudAlta::where('status', 'En Proceso')
            ->where('Observaciones', '!=', 'Solicitud enviada a Administrador.')
            ->get();
        return view('rh.solicitudesAltas', compact('solicitudes'));
    }

    public function detalleSolicitud($id){
        $solicitud = SolicitudAlta::find($id);
        $documentacion = DocumentacionAltas::where('solicitud_id', $id)->first();
        return view('rh.detalleSolicitud', compact('solicitud', 'documentacion'));
    }

    public function aceptarSolicitud($id){
        $solicitud = SolicitudAlta::find($id);

        $solicitud->status = 'Aceptada';
        $solicitud->observaciones = 'Solicitud Aceptada.';
        $solicitud->save();

            $docs = DocumentacionAltas::where('solicitud_id', $id)->first();

            $idDocs = $docs->id;
            $idSol= $solicitud->id;

            $user = new User();
            $user->sol_alta_id = $idSol;
            $user->sol_docs_id = $idDocs;
            $user->name = $solicitud->nombre . " " . $solicitud->apellido_paterno . " " . $solicitud->apellido_materno;
            $user->email = $solicitud->email;
            $user->password = Hash::make($solicitud->rfc);
            $user-> fecha_ingreso = Carbon::now();
            $user->punto = $solicitud->punto;
            $user->rol = $solicitud->rol;
            $user->estatus = 'Activo';
            $user->empresa = $solicitud->empresa;
            $user->save();

        return redirect()->route('rh.solicitudesAltas')->with('success', 'Solicitud respondida correctamente.');
    }

    public function enviarObservacion(Request $request, $id){
        $request->validate([
            'observacion' => 'required|string|max:1000'
        ]);

        $solicitud = SolicitudAlta::find($id);
        $solicitud->observaciones = $request->observacion;
        $solicitud->save();

        return redirect()->route('rh.detalleSolicitud', $id)->with('success', 'Observación enviada correctamente.');
    }

    public function rechazarSolicitud($id){
        $solicitud = SolicitudAlta::find($id);
        $solicitud->status = 'Rechazada';
        $solicitud->observaciones = 'Solicitud no aprobada.';
        $solicitud->save();

        return redirect()->route('rh.solicitudesAltas')->with('success', 'Solicitud rechazada correctamente.');
    }

    public function historialSolicitudesAltas(){
        $solicitudes = SolicitudAlta::all();
        return view('rh.historialSolicitudesAltas', compact('solicitudes'));
    }

    public function solicitudesBajas() {
        $solicitudes = SolicitudBajas::with('user.solicitudAlta')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('estatus', 'En Proceso')
                    /*->where('por', 'Renuncia')*/;
                })->orWhere(function ($q) {
                    $q->where('estatus', 'Aceptada')
                    ->where('observaciones', 'Finiquito enviado a RH.');
                });
            })
            ->get();

        return view('rh.solicitudesBajas', compact('solicitudes'));
    }

    public function historialSolicitudesBajas(){
        $solicitudes = SolicitudBajas::with('user.solicitudAlta')
        ->where('por', 'Renuncia')
        ->orderBy('fecha_solicitud', 'desc')
        ->get();

        return view('rh.historialSolicitudesBajas', compact('solicitudes'));
    }

    public function detalleSolicitudBaja($id, \App\Services\FiniquitoCalculator $calculator)
{
    $solicitud = SolicitudBajas::with('user.solicitudAlta')->findOrFail($id);
    $user = $solicitud->user;

    $solicitudAlta = $user->solicitudAlta;
    $documentacion = DocumentacionAltas::where('solicitud_id', $user->sol_alta_id)->first();
    $calculoFiniquito = null;
    $errorCalculoFiniquito = null;
    if ($solicitud->estatus === 'Aceptada' && $solicitud->por === 'Renuncia') {
        try {
            $calculoFiniquito = $calculator->calculate($solicitud);
        } catch (\DomainException $e) {
            $errorCalculoFiniquito = $e->getMessage();
        }
    }

    // Compatibilidad con las tarjetas existentes, usando el mismo resultado central.
    $dias = $calculoFiniquito['vacation']['vested_entitlement_days'] ?? 0;
    $diasDisponibles = $calculoFiniquito['vacation']['payable_days'] ?? 0;
    $diasTrabajadosAnio = $calculoFiniquito['vacation']['elapsed_days'] ?? 0;
    $diasVacacionesProporcionales = $calculoFiniquito['vacation']['proportional_days'] ?? 0;
    $aguinaldoProporcional = $calculoFiniquito['aguinaldo']['proportional_days'] ?? 0;
    $primaVacacional = $calculoFiniquito['vacation']['premium_amount'] ?? 0;

    return view('rh.detalleSolicitudBaja', compact(
        'solicitud',
        'user',
        'documentacion',
        'solicitudAlta',
        'dias',
        'diasDisponibles',
        'diasTrabajadosAnio',
        'diasVacacionesProporcionales',
        'aguinaldoProporcional',
        'primaVacacional',
        'calculoFiniquito',
        'errorCalculoFiniquito'
    ));
}


    public function rechazarBaja($id){
        $solicitud = SolicitudBajas::find($id);
        $solicitud->estatus = 'Rechazada';
        $solicitud->observaciones = 'Solicitud no aprobada.';
        $solicitud->save();
        return redirect()->route('rh.historialSolicitudesBajas')->with('success', 'Solicitud rechazada correctamente.');
    }

    public function aceptarBaja($id){
        $solicitud = SolicitudBajas::find($id);
        $solicitud->estatus = 'Aceptada';
        $solicitud->observaciones = 'Baja de elemento Aprobada.';
        $solicitud->fecha_baja = Carbon::now();
        $solicitud->save();

        $userId = $solicitud->user_id;
        $user = User::find($userId);
        $user->estatus = 'Inactivo';
        $user->save();

        return redirect()->route('rh.historialSolicitudesBajas')->with('success', 'Solicitud respondida correctamente.');
    }

    public function generarNuevaAltaForm(){
        $puntos = Punto::with('subpuntos')->get();
        return view('rh.generarAlta', compact('puntos'));
    }
    public function formAlta(Request $request)
    {
        $puntos = Punto::with('subpuntos')->get();
        $tipo = $request->get('tipo', 'oficina');
        return view('rh.generarAlta', compact('tipo', 'puntos'));
    }


    public function guardarAlta(Request $request){
    try {
        $validated = $request->validate([
            'tipo' => 'required|in:oficina,armado,noarmado',
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

            // --- NUEVOS CAMPOS ---
            'tipo_periodo' => 'nullable|in:semanal,quincenal',
            'banco' => 'nullable|string|max:255',
            'cuenta_bancaria' => 'nullable|string|max:255',
        ]);

        $tipoSeleccionado = $request->get('tipo', 'oficina');

        $solicitud = new SolicitudAlta();
        $solicitud->solicitante = auth()->user()->name;
        $solicitud->nombre = $request->name;
        $solicitud->apellido_paterno = $request->apellido_paterno;
        $solicitud->apellido_materno = $request->apellido_materno;
        $solicitud->fecha_nacimiento = $request->fecha_nacimiento;
        $solicitud->tipo_empleado = $request->get('tipo', 'oficina');
        $solicitud->curp = $request->curp;
        $solicitud->nss = $request->nss;
        $solicitud->estado_civil = $request->edo_civil;
        $solicitud->rfc = $request->rfc;
        $solicitud->telefono = $request->telefono;
        $solicitud->domicilio_calle = $request->calle;
        $solicitud->domicilio_numero = $request->num_ext;
        $solicitud->domicilio_colonia = $request->colonia;
        $solicitud->cp_fiscal = $request->cp_fiscal;
        $solicitud->domicilio_ciudad = $request->ciudad;
        $solicitud->peso = $request->peso;
        $solicitud->estatura = $request->estatura;
        $solicitud->liga_rfc = $request->liga_rfc;
        $solicitud->domicilio_estado = $request->estado;
        $solicitud->infonavit = $request->infonavit;
        $solicitud->fonacot = $request->fonacot;
        $solicitud->domicilio_comprobante = $request->domicilio_comprobante;
        $solicitud->rol = $request->rol;
        $solicitud->punto = $request->punto;
        $solicitud->reingreso = $request->reingreso;
        $solicitud->empresa = $request->empresa;
        $solicitud->fecha_ingreso = $request->fecha_ingreso;
        $solicitud->sueldo_mensual = $request->sueldo_mensual;
        $solicitud->email = $request->email;

        // --- ASIGNACIÓN DE NUEVOS CAMPOS ---
        $solicitud->tipo_periodo = $request->tipo_periodo;
        $solicitud->banco = $request->banco;
        $solicitud->cuenta_bancaria = $request->cuenta_bancaria;

        $solicitud->status = 'Aceptada';
        $solicitud->observaciones = 'Solicitud Aceptada.';
        $solicitud->created_at = Carbon::now('America/Mexico_City');
        $solicitud->updated_at = Carbon::now('America/Mexico_City');

        $solicitud->save();

        return redirect()->route('rh.subirArchivosAltaForm', ['id' => $solicitud->id, 'tipo' => $tipoSeleccionado]);

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error al guardar la solicitud, intente nuevamente.'. $e->getMessage());
    }
}

    public function cancelarSolicitud($id): RedirectResponse
    {
        $solicitud = SolicitudVacaciones::findOrFail($id);

        // Opcional: solo permitir cancelar si está en "En Proceso"
        if ($solicitud->estatus !== 'En Proceso') {
            return back()->with('error', 'Solo se pueden cancelar solicitudes en estado "En Proceso".');
        }

        $solicitud->estatus = 'Cancelada';
        $solicitud->observaciones = 'Solicitud de vacaciones Cancelada';
        $solicitud->save();

        return back()->with('success', 'La solicitud ha sido cancelada correctamente.');
    }

    public function subirArchivosAltaForm($id){
        $tipo = request('tipo');
        $solicitud = SolicitudAlta::find($id);
        $fecha_ingreso = request('fecha_ingreso');
        return view('rh.subirArchivosAlta', compact('solicitud', 'tipo', 'fecha_ingreso'));
    }

public function guardarArchivosAlta(Request $request, $id)
{
    try {
        $request->validate([
            'arch_acta_nacimiento' => 'nullable|file',
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
            'arch_solicitud_empleo' => 'nullable|file',
            'arch_antidoping' => 'nullable|file',
            'arch_nss' => 'nullable|file',
            'arch_contrato' => 'nullable|file',
            'arch_foto' => 'nullable|file',
            'visa' => 'nullable|file',
            'pasaporte' => 'nullable|file',
        ]);

        $solicitud = SolicitudAlta::findOrFail($id);
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
            'arch_nss',
            'arch_contrato',
            'arch_antidoping',
            'arch_solicitud_empleo',
            'visa',
            'pasaporte',
        ];

        foreach ($archivos as $campo) {
            if ($request->hasFile($campo)) {
                try {
                    $archivo = $request->file($campo);
                    $nombreArchivo = $campo . '.' . $archivo->getClientOriginalExtension();
                    $ruta = $archivo->storeAs($carpeta, $nombreArchivo, 'public');
                    $documentacion->$campo = 'storage/' . $ruta;
                } catch (\Exception $e) {
                    Log::error("Error al guardar el archivo {$campo}: " . $e->getMessage());
                }
            }
        }

        $documentacion->solicitud_id = $solicitudId;
        $documentacion->save();

        $solicitud->status = 'Aceptada';
        $solicitud->observaciones = 'Solicitud Aceptada.';
        $solicitud->save();

        if(Auth::user()->rol != 'Supervisor' && Auth::user()->rol != 'SUPERVISOR'){
            $user = new User();
            $user->sol_alta_id = $solicitudId;
            $user->sol_docs_id = $documentacion->id;
            $user->name = $solicitud->nombre . " " . $solicitud->apellido_paterno . " " . $solicitud->apellido_materno;
            $user->email = $solicitud->email;
            if(!empty($solicitud->rfc))
                $user->password = Hash::make($solicitud->rfc);
            else
                $user->password = Hash::make($solicitud->curp);

            $user->fecha_ingreso = $solicitud->fecha_ingreso;
            $user->punto = $solicitud->punto;
            $user->rol = $solicitud->rol;
            $user->estatus = 'Activo';
            $user->empresa = $solicitud->empresa;

            $user->save();
        }

        return redirect()->route('dashboard')->with('success', 'Documentación subida correctamente');
    } catch (\Throwable $e) {
        Log::error("Error general en guardarArchivosAlta: " . $e->getMessage());
        return redirect()->back()->with('error', 'Ocurrió un error al guardar los archivos. Verifica el log para más detalles.');
    }
}


    public function generarNuevaBajaForm(Request $request){
        $busqueda = $request->input('busqueda');

        $usuarios = User::when($busqueda, function ($query, $busqueda) {
            return $query->where('name', 'like', "%{$busqueda}%");
        })->orderBy('name')->paginate(10);

        return view('rh.generarBaja', compact('usuarios'));
    }

    public function llenarBaja($id){
        $user = User::find($id);
        $solicitud = SolicitudAlta::find($user->sol_alta_id);
        $solicitudpendiente = SolicitudBajas::where('user_id', $user->id)->where('estatus', 'En Proceso')->first();

        return view('rh.llenarBaja', compact('user','solicitud','solicitudpendiente'));
    }

    public function almacenarBaja(Request $request, $id)
{
    $request->validate([
        'fecha_baja' => 'required|date',
        'incapacidad' => 'nullable|string|max:255',
        'por' => 'required|in:Ausentismo,Separación Voluntaria,Renuncia,Otro',
        'ultima_asistencia' => 'nullable|date',
        'motivo' => 'nullable|string',
        'adelanto_nomina' => 'nullable|string',
        'descuento' => 'nullable|string',
        'archivo_baja' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'arch_equipo_entregado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'arch_renuncia' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ]);

    $user = User::findOrFail($id);

    $solicitud = new SolicitudBajas();
    $solicitud->user_id = $user->id;
    $solicitud->fecha_solicitud = $request->fecha_baja;
    $solicitud->fecha_baja = $request->fecha_baja;
    $solicitud->motivo = $request->motivo;
    $solicitud->adelanto_nomina = $request->adelanto_nomina;
    $solicitud->descuento = $request->descuento;
    $solicitud->incapacidad = $request->incapacidad;
    $solicitud->por = $request->por;
    $solicitud->ultima_asistencia = $request->ultima_asistencia;
    if((Auth::user()->rol=='admin' || Auth::user()->rol == 'AUXILIAR RECURSOS HUMANOS' || Auth::user()->rol == 'AUXILIAR RH' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RH' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RECURSOS HUMANOS')){
        $solicitud->estatus = 'Aceptada';
        $solicitud->observaciones = 'Baja de elemento Aprobada.';
        $solicitud->fecha_baja = $request->fecha_baja;
        $solicitud->save();

        $userId = $solicitud->user_id;
        $user = User::find($userId);
        $user->estatus = 'Inactivo';
        $user->save();
    }else{
        $solicitud->estatus = 'En Proceso';
        $solicitud->observaciones = 'Solicitud en revisión';
        $solicitud->fecha_baja = $request->fecha_baja;
    }

    try {
        $solicitud->save();
    } catch (\Exception $e) {
        return redirect()->route('dashboard')->with('error', 'Error al enviar la solicitud.');
    }

    $carpeta = 'solicitudesBajas/' . $solicitud->id;
    Storage::disk('public')->makeDirectory($carpeta);

    $archivos = [
        'archivo_baja',
        'arch_equipo_entregado',
        'arch_renuncia'
    ];

    foreach ($archivos as $campo) {
        if ($request->hasFile($campo)) {
            $archivo = $request->file($campo);
            $nombre = $campo . '_' . time() . '.' . $archivo->getClientOriginalExtension();
            $ruta = $archivo->storeAs($carpeta, $nombre, 'public');
            $solicitud->$campo = $ruta;
        }
    }

    if ($solicitud->arch_renuncia !== null) {
        $user->estatus = 'Inactivo';
        $user->save();
    }

    $solicitud->save();

    return redirect()->route('dashboard')->with('success', 'Baja de usuario realizada correctamente.');
}

    public function actualizar(Request $request, $id)
    {
        // Validación (igual que en el registro, pero con archivos opcionales)
        $request->validate([
            'fecha_baja' => 'required|date',
            'incapacidad' => 'nullable|string|max:255',
            'por' => 'required|in:Ausentismo,Separación Voluntaria,Renuncia,Otro',
            'ultima_asistencia' => 'required|date',
            'motivo' => 'nullable|string',
            'descuento' => 'nullable|string',
            'archivo_baja' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'arch_equipo_entregado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'arch_renuncia' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $solicitud = SolicitudBajas::findOrFail($id);

        // Actualizar campos básicos
        $solicitud->fecha_baja = $request->fecha_baja;
        $solicitud->ultima_asistencia = $request->ultima_asistencia;
        $solicitud->incapacidad = $request->incapacidad;
        $solicitud->por = $request->por;
        $solicitud->motivo = $request->motivo;
        $solicitud->descuento = $request->descuento;

        // Guardar primero para asegurar el ID (por si es nuevo, aunque no debería serlo)
        $solicitud->save();

        // Ruta de la carpeta del registro
        $carpeta = 'solicitudesBajas/' . $solicitud->id;
        Storage::disk('public')->makeDirectory($carpeta);

        // Archivos a manejar
        $camposArchivos = [
            'archivo_baja',
            'arch_equipo_entregado',
            'arch_renuncia'
        ];

        foreach ($camposArchivos as $campo) {
            if ($request->hasFile($campo)) {
                // 1. Eliminar archivo anterior si existe
                if ($solicitud->$campo) {
                    Storage::disk('public')->delete($solicitud->$campo);
                }

                // 2. Subir nuevo archivo
                $archivo = $request->file($campo);
                $nombre = $campo . '_' . time() . '.' . $archivo->getClientOriginalExtension();
                $ruta = $archivo->storeAs($carpeta, $nombre, 'public');
                $solicitud->$campo = $ruta;
            }
        }

        // Guardar cambios finales
        $solicitud->save();

        return redirect()->back()->with('success', 'Solicitud de baja actualizada correctamente.');
    }


    public function verArchivos(){
        return view('rh.archivos');
    }

    public function vistaVacaciones(){
        $solicitudes = SolicitudVacaciones::where('estatus', 'En Proceso')
            ->where('observaciones', 'Solicitud aceptada, falta subir archivo de solicitud.')
            ->paginate(10);
        return view('rh.vistaVacaciones', compact('solicitudes'));
    }

    public function historialVacaciones(){
        $solicitudes = SolicitudVacaciones::orderBy('fecha_inicio', 'desc')
            ->paginate(10);
        return view('rh.historialVacaciones', compact('solicitudes'));
    }

    public function exportFichaTecnica($id)
    {
        $user = User::findOrFail($id);
        $docs = DocumentacionAltas::where('solicitud_id', $user->sol_alta_id)->first();

    $tipo = $user->solicitudAlta->tipo_empleado ?? 'oficina';

        if ($tipo === 'armado') {
            $documentosObligatorios = [
                ['label' => 'Solicitud/CV', 'name' => 'arch_solicitud_empleo'],
                ['label' => 'INE', 'name' => 'arch_ine'],
                ['label' => 'NSS', 'name' => 'arch_nss'],
                ['label' => 'CURP', 'name' => 'arch_curp'],
                ['label' => 'RFC', 'name' => 'arch_rfc'],
                ['label' => 'Acta de Nacimiento', 'name' => 'arch_acta_nacimiento'],
                ['label' => 'Comprobante de Estudios', 'name' => 'arch_comprobante_estudios'],
                ['label' => 'Comprobante de Domicilio', 'name' => 'arch_comprobante_domicilio'],
                ['label' => 'Carta de Recomendación Laboral', 'name' => 'arch_carta_rec_laboral'],
                ['label' => 'Carta de Recomendación Personal', 'name' => 'arch_carta_rec_personal'],
                ['label' => 'Cartilla Militar', 'name' => 'arch_cartilla_militar'],
                ['label' => 'Antidoping', 'name' => 'arch_antidoping'],
                ['label' => 'Carta de No Antecedentes Penales', 'name' => 'arch_carta_no_penales'],
                ['label' => 'Contrato', 'name' => 'arch_contrato'],
                ['label' => 'Fotografía (Reciente)', 'name' => 'arch_foto'],
            ];
        } else {
            $documentosObligatorios = [
                ['label' => 'Solicitud/CV', 'name' => 'arch_solicitud_empleo'],
                ['label' => 'INE', 'name' => 'arch_ine'],
                ['label' => 'NSS', 'name' => 'arch_nss'],
                ['label' => 'CURP', 'name' => 'arch_curp'],
                ['label' => 'RFC', 'name' => 'arch_rfc'],
                ['label' => 'Acta de Nacimiento', 'name' => 'arch_acta_nacimiento'],
                ['label' => 'Comprobante de Estudios', 'name' => 'arch_comprobante_estudios'],
                ['label' => 'Comprobante de Domicilio', 'name' => 'arch_comprobante_domicilio'],
                ['label' => 'Carta de Recomendación Laboral', 'name' => 'arch_carta_rec_laboral'],
                ['label' => 'Carta de Recomendación Personal', 'name' => 'arch_carta_rec_personal'],
                ['label' => 'Contrato', 'name' => 'arch_contrato'],
                ['label' => 'Fotografía (Reciente)', 'name' => 'arch_foto'],
            ];
        }
        $mpdf = new Mpdf();
        $html = view('pdf.fichaTecnica', compact('user', 'docs', 'documentosObligatorios'))->render();
        $mpdf->WriteHTML($html);

        $tempFile = tempnam(sys_get_temp_dir(), 'ficha_') . '.pdf';
        $mpdf->Output($tempFile);

        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($tempFile);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }

        foreach ($documentosObligatorios as $doc) {
            $archivo = $docs ? ($docs->{$doc['name']} ?? null) : null;

            if ($archivo && file_exists(public_path($archivo))) {
                $rutaArchivo = public_path($archivo);
                $ext = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));

                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $pdf->AddPage();
                    $pdf->Image($rutaArchivo, 10, 10, 190);
                } elseif ($ext === 'pdf') {
                    $docPageCount = $pdf->setSourceFile($rutaArchivo);
                    for ($i = 1; $i <= $docPageCount; $i++) {
                        $tplId = $pdf->importPage($i);
                        $size = $pdf->getTemplateSize($tplId);

                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $pdf->useTemplate($tplId);
                    }
                }
            }
        }

        unlink($tempFile);

        return response($pdf->Output('ficha_tecnica_' . $user->id . '.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="ficha_tecnica_'.$user->id.'.pdf"');
    }

    public function listaAsistenciaMontana(){
        $usuarios = User::where('empresa', 'Montana')
            ->where('estatus', 'Activo')
            ->orderBy('name')
            ->get();

        $fechaActual = now()->toDateString();
        $yaRegistrado = \App\Models\Asistencia::where('punto', 'OFICINA')
            ->whereDate('fecha', $fechaActual)
            ->exists();

        return view('rh.listaAsistenciaMontana', compact('usuarios', 'yaRegistrado'));
    }

    public function guardarAsistenciaMontana(Request $request)
{
    $validated = $request->validate([
        'fecha_registro' => 'required|date|date_format:Y-m-d',
        'asistio' => 'nullable|array',
        'asistio.*' => 'integer|exists:users,id',
        'falto' => 'nullable|array',
        'falto.*' => 'integer|exists:users,id',
        'retraso_minutos' => 'nullable|array',
        'retraso_minutos.*' => 'nullable|integer|min:1|max:599',
        'tipo_falta' => 'nullable|array', // Nuevo
        'tipo_falta.*' => 'string|in:justificada,injustificada', // Nuevo
        'observaciones' => 'nullable|string|max:500',
    ]);

    $user = Auth::user();
    $fechaRegistro = $request->input('fecha_registro');
    $horaRegistro = now('America/Mexico_City')->toTimeString();

    $asistencias = $request->input('asistio', []);
    $faltas = $request->input('falto', []);
    $retrasos = $request->input('retraso_minutos', []);
    $tiposFalta = $request->input('tipo_falta', []); // Nuevo

    $asistencia = Asistencia::create([
        'user_id' => $user->id,
        'fecha' => $fechaRegistro,
        'hora_asistencia' => $horaRegistro,
        'elementos_enlistados' => json_encode($asistencias),
        'faltas' => json_encode($faltas),
        'descansos' => json_encode([]),
        'observaciones' => $request->input('observaciones') ?: 'Ninguna',
        'punto' => 'OFICINA', // Cambié a OFICINA como dijiste
        'empresa' => 'Montana',
    ]);

    // Guardar faltas justificadas
    foreach ($tiposFalta as $userId => $tipo) {
        if ($tipo === 'justificada' && in_array($userId, $faltas)) {
            \App\Models\FaltaJustificada::create([
                'asistencia_id' => $asistencia->id,
                'user_id' => $userId,
                'fecha' => $fechaRegistro,
                'tipo' => 'justificada',
                'motivo' => 'Falta justificada por RH',
                'registrado_por' => $user->id,
            ]);
        }
    }

    // Guardar retardos
    foreach ($retrasos as $userId => $minutos) {
        if ($minutos && in_array($userId, $asistencias)) {
            \App\Models\Retardo::create([
                'user_id' => $userId,
                'asistencia_id' => $asistencia->id,
                'fecha' => $fechaRegistro,
                'minutos_retardo' => $minutos,
                'registrado_por' => $user->id,
            ]);
        }
    }

    return redirect()->route('rh.listaAsistencia')
        ->with('success', 'Asistencias guardadas correctamente.');
}

    public function obtenerUsuariosEnVacaciones($fecha)
    {
        $usuariosEnVacaciones = \DB::table('solicitud_vacaciones')
            ->join('users', 'solicitud_vacaciones.user_id', '=', 'users.id')
            ->where('users.empresa', 'Montana')
            ->where('solicitud_vacaciones.estatus', 'Aceptada')
            ->whereDate('solicitud_vacaciones.fecha_inicio', '<=', $fecha)
            ->whereDate('solicitud_vacaciones.fecha_fin', '>=', $fecha)
            ->pluck('user_id')
            ->toArray();

        return response()->json(['usuarios' => $usuariosEnVacaciones]);
    }

    public function obtenerUsuariosConPermisos($fecha)
    {
        $usuariosConPermisos = \App\Models\PermisoEspecial::where('estatus', 'Aprobado')
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->whereDate('fecha_fin', '>=', $fecha)
            ->pluck('user_id')
            ->toArray();

        return response()->json(['usuarios' => $usuariosConPermisos]);
    }

    public function verificarAsistenciaExistente($fecha)
    {
        $existe = \App\Models\Asistencia::where('punto', 'OFICINA')
            ->whereDate('fecha', $fecha)
            ->exists();

        return response()->json([
            'existe' => $existe,
            'fecha_formateada' => \Carbon\Carbon::parse($fecha)->format('d/m/Y'),
        ]);
    }

    public function reingresos(){
        $reingresos = SolicitudAlta::where('reingreso', '!=' , null)
            ->orWhere('reingreso', '!=', 'NO')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('rh.reingresos', compact('reingresos'));
    }

    public function kardexVacaciones(){
        return view('rh.kardexVacaciones');
    }
}
