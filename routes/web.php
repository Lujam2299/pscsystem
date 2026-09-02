<?php

use App\Exports\AltasPorCorteExport;
use App\Exports\AltasSpreadsheetExport;
use App\Exports\BajasSpreadsheetExport;
use App\Exports\VacacionesCortesExport;
use App\Exports\VacacionesSpreadsheetExport;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuxadminController;
use App\Http\Controllers\AuxcontController;
use App\Http\Controllers\BajaAcuseController;
use App\Http\Controllers\CedulaController;
use App\Http\Controllers\ChatWebController;
use App\Http\Controllers\CustodiosController;
use App\Http\Controllers\GpsReportController;
use App\Http\Controllers\GraficosController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\IncapacidadController;
use App\Http\Controllers\IncapacidadReporteController;
use App\Http\Controllers\InspeccionMensajeArchivoController;
use App\Http\Controllers\InspeccionReportePdfController;
use App\Http\Controllers\InspeccionReporteSemanalController;
use App\Http\Controllers\JuridicoController;
use App\Http\Controllers\MonitoreoController;
use App\Http\Controllers\NominasController;
use App\Http\Controllers\OperacionesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RealtimePositionController;
use App\Http\Controllers\ReingresoController;
use App\Http\Controllers\RhController;
use App\Http\Controllers\RiesgoTrabajoController;
use App\Http\Controllers\SipareController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\TraccarMonitoringController;
use App\Http\Controllers\UserController;
use App\Models\Unidades;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'permission:tokens.manage'])->post('/generate-api-token', function (Request $request) {
    $request->validate([
        'token_name' => 'required|string|max:255',
        'current_password' => ['required', 'current_password'],
        'abilities' => ['nullable', 'array'],
        'abilities.*' => [Rule::in(['mobile:read', 'mobile:write', 'messages:read', 'messages:write'])],
    ]);

    $user = auth()->user();

    // Revocar tokens antiguos con el mismo nombre
    $user->tokens()->where('name', $request->token_name)->delete();

    // Crear nuevo token
    $expiresAt = now()->addHours(24);
    $token = $user->createToken(
        $request->token_name,
        $request->input('abilities', ['mobile:read', 'mobile:write']),
        $expiresAt,
    );

    return response()->json([
        'token' => $token->plainTextToken,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
        ],
        'expires_at' => $expiresAt->toIso8601String(),
        'message' => 'Token generado exitosamente. ¡Guárdalo ahora!',
    ]);
});

Route::middleware(['auth', 'permission:tokens.manage'])->post('/my-api-token', function (Request $request) {
    $request->validate([
        'current_password' => ['required', 'current_password'],
    ]);

    $user = auth()->user();
    $expiresAt = now()->addHours(24);

    $token = $user->createToken(
        'mobile_app_token_'.now()->format('Y-m-d_H-i-s'),
        ['mobile:read', 'mobile:write'],
        $expiresAt,
    );

    return response()->json([
        'token' => $token->plainTextToken,
        'user' => $user->only(['id', 'name', 'email']),
        'expires_at' => $expiresAt->toIso8601String(),
    ]);
});

Route::middleware('auth')->group(function () {

    // Broadcast::routes(['middleware' => ['web', 'auth']]);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Usuario Admnistrador
    Route::get('/users', [ProfileController::class, 'mostrarUsuarios'])->name('admin.verUsuarios');
    Route::get('/users/registrarUsuario', [UserController::class, 'crearUsuario'])->name('admin.crearUsuarioForm');
    Route::post('/guardarUsuario', [UserController::class, 'registrarUsuario'])->name('registrarUsuario');
    Route::get('/editar_usuario/{id}', [AdminController::class, 'editarUsuario'])->name('admin.editarUsuarioForm');
    Route::post('/actualizar_usuario/{id}', [SupervisorController::class, 'editarInformacionSolicitud'])
        ->name('admin.actualizarUsuario');
    Route::post('/actualizar_documentacion_usuario/{id}', [SupervisorController::class, 'subirArchivosEditados'])
        ->name('admin.actualizarDocumentacionUsuario');
    Route::get('/ver_usuarios', [AdminController::class, 'verUsuarios'])->name('admin.verUsuarios');
    Route::get('/tablero_supervisores', [AdminController::class, 'tableroSupervisores'])
        ->middleware(['permission:supervisors.access', 'module.enabled:erp_supervisores'])
        ->name('admin.verTableroSupervisores');
    Route::get('/admin_solicitudes_altas', [AdminController::class, 'verSolicitudesAltas'])->name('admi.verSolicitudesAltas');
    Route::post('/admin/baja_usuario/{id}', [AdminController::class, 'bajaUsuario'])->name('admin.darDeBajaUsuario');
    Route::get('/ver_buzon', [AdminController::class, 'verBuzon'])->name('admin.verBuzon');
    Route::post('/importar-excel', [ImportController::class, 'updateDestajos'])->name('updateDestajos');
    Route::post('/importar-excel2', [ImportController::class, 'importarVacaciones'])->name('importar.excel');
    Route::post('/importar-personal-activo', [ImportController::class, 'importarPersonalActivo'])->name('importar.personal.activo');
    Route::post('/reingreso/{id}', [AdminController::class, 'darReingreso'])->name('admin.reingreso');
    Route::get('/auditoria', \App\Livewire\AuditLogIndex::class)
        ->middleware('permission:audit.view')
        ->name('admin.audit.index');
    Route::get('/tablero_nominas', [AdminController::class, 'tableroNominas'])->name('admin.nominasDashboard');
    Route::get('/tablero_operaciones', [AdminController::class, 'tableroOperaciones'])->name('admin.operacionesDashboard');
    Route::get('/tablero_imss', [AdminController::class, 'tableroImss'])->name('admin.imssDashboard');
    Route::get('/tablero_rh', [AdminController::class, 'tableroRh'])->name('admin.rhDashboard');
    Route::get('/tablero_contabilidad', [AdminController::class, 'tableroAuxCont'])->name('admin.contDashboard');
    Route::get('tablero_monitoreo', [AdminController::class, 'tableroMonitoreo'])->name('admin.monitoreoDashboard');
    Route::get('tablero_juridico', [AdminController::class, 'tableroJuridico'])->name('admin.juridicoDashboard');
    Route::get('/tablero_custodios', [AdminController::class, 'tableroCustodios'])
        ->middleware(['permission:custodians.access', 'custodios.role', 'module.enabled:erp_custodios'])
        ->name('admin.custodiosDashboard');
    Route::get('/admin_vacaciones', [AdminController::class, 'solicitudesVacaciones'])->name('admin.solicitudesVacaciones');
    Route::post('/admin/vacaciones/{id}/aceptar', [SupervisorController::class, 'aceptarSolicitudVacaciones'])
        ->middleware('permission:vacations.review')->name('admin.vacaciones.aceptar');
    Route::post('/admin/vacaciones/{id}/rechazar', [SupervisorController::class, 'rechazarSolicitudVacaciones'])
        ->middleware('permission:vacations.review')->name('admin.vacaciones.rechazar');
    Route::get('/registrar_nominas', [AdminController::class, 'registrarNominas'])->name('registrarNominas');
    Route::post('/registrar_finiquitos', [AdminController::class, 'registrarFiniquitos'])->name('registrarFiniquitos');
    Route::post('/admin/import/unify-duplicates', [ImportController::class, 'unifyDuplicates'])->name('admin.import.unify-duplicates');

    // Usuario Supervisor - módulo ERP deshabilitado, conservando datos históricos.
    Route::middleware(['permission:supervisors.access', 'module.enabled:erp_supervisores'])->group(function () {
        Route::get('/nuevoUsuario', [SupervisorController::class, 'nuevoUsuarioForm'])->name('sup.nuevoUsuarioForm');
        Route::post('/infoUsuario', [SupervisorController::class, 'guardarInfo'])->name('sup.guardarInfo');
        Route::get('/subir-archivos/{id}', [SupervisorController::class, 'subirArchivosForm'])->name('sup.subirArchivosForm');
        Route::post('/subir-archivos/{id}', [SupervisorController::class, 'guardarArchivos'])->name('sup.guardarArchivos');
        Route::get('/historial_solicitudes', [SupervisorController::class, 'historialSolicitudes'])->name('sup.historial');
        Route::get('/historial_solicitudes/{id}', [SupervisorController::class, 'detalleSolicitud'])->name('sup.solicitud.detalle');
        Route::get('/editar_solicitud/{id}', [SupervisorController::class, 'editarSolicitudForm'])->name('sup.editarSolicitudForm');
        Route::post('/editar_informacion_solicitud/{id}', [SupervisorController::class, 'editarInformacionSolicitud'])->name('sup.editarInformacionSolicitud');
        Route::post('/subir_archivos_editados/{id}', [SupervisorController::class, 'subirArchivosEditados'])->name('sup.guardarArchivosEditados');
        Route::get('/sup_solicitar_baja', [SupervisorController::class, 'solicitarBajaForm'])->name('sup.solicitarBajaForm');
        Route::get('/sup_solicitar_baja/{id}', [SupervisorController::class, 'solicitarBajaVista'])->name('sup.validarSolicitudBaja');
        Route::post('/nueva_guardar_baja/{id}', [SupervisorController::class, 'guardarBajaNueva'])->name('sup.guardarBajaNueva');
        Route::get('/historial_bajas', [SupervisorController::class, 'historialBajas'])->name('sup.historialBajas');
        Route::get('/lista_asistencia', [SupervisorController::class, 'listaAsistencia'])->name('sup.listaAsistencia');
        Route::post('/guardar_asistencias', [SupervisorController::class, 'guardarAsistencias'])->name('sup.guardarAsistencias');
        Route::get('/ver_asistencias/{id}', [SupervisorController::class, 'verAsistencias'])->name('sup.verAsistencias');
        Route::get('/supervisor/ver_fecha_sistencias', [SupervisorController::class, 'verFechaAsistencias'])->name('sup.verFechaAsistencias');
        Route::get('/detalle_asistencia/{id}', [SupervisorController::class, 'detalleAsistencia'])->name('sup.detalleAsistencia');
        Route::get('/solicitudes_vacaciones', [SupervisorController::class, 'solicitudesVacaciones'])->name('sup.solicitudesVacaciones');
        Route::post('/aceptar_solicitud_vacaciones/{id}', [SupervisorController::class, 'aceptarSolicitudVacaciones'])->name('sup.aceptarSolicitudVacaciones');
        Route::post('/rechazar_solicitud_vacaciones/{id}', [SupervisorController::class, 'rechazarSolicitudVacaciones'])->name('sup.rechazarSolicitudVacaciones');
        Route::get('/ver_solicitud_baja/{id}', [SupervisorController::class, 'verSolicitudBaja'])->name('sup.verSolicitudBaja');
        Route::get('/tiempos_extras', [SupervisorController::class, 'tiemposExtras'])->name('sup.tiemposExtras');
        Route::get('/tiempos_extras/{id}', [SupervisorController::class, 'tiemposExtrasForm'])->name('sup.tiemposExtrasForm');
        Route::post('/guardar_tiempo_extra/{id}', [SupervisorController::class, 'guardarTiempoExtra'])->name('sup.guardarTiempoExtra');
        Route::get('/cobertura_turno_form/{id}', [SupervisorController::class, 'coberturaTurnoForm'])->name('sup.coberturaTurnoForm');
        Route::post('/guardar_cobertura_turno/{id}', [SupervisorController::class, 'guardarCoberturaTurno'])->name('sup.guardarCoberturaTurno');
        Route::get('/historial_tiempos_extras', [SupervisorController::class, 'historialTiemposExtras'])->name('sup.historialTiemposExtras');
        Route::get('/gestion_usuarios', [SupervisorController::class, 'gestionUsuarios'])->name('sup.gestionUsuarios');
        Route::get('/descargar_formato_vacaciones/{id}', [SupervisorController::class, 'descargarSolicitudVacaciones'])->name('sup.descargarSolicitudVacaciones');
        Route::post('/solicitud-vacaciones/{id}/subir-archivo', [SupervisorController::class, 'subirArchivo'])->name('solicitud-vacaciones.subir-archivo');
        Route::get('/vacaciones_elemento', [SupervisorController::class, 'solicitarVacacionesElemento'])->name('sup.solicitarVacacionesElemento');
        Route::get('/vacaciones_elemento/{id}', [SupervisorController::class, 'vacacionesElementoForm'])->name('sup.solicitarVacacionesElementoForm');
        Route::get('/asistencias/confirmar-faltas', [SupervisorController::class, 'confirmarFaltas'])->name('asistencias.confirmarFaltas');
        Route::post('/asistencias/finalizar', [SupervisorController::class, 'finalizarAsistencia'])->name('asistencias.finalizar');
        Route::get('/sup-alta-usuario', [SupervisorController::class, 'formAlta'])->name('sup.formAlta');
    });

    // usuario Recursos Humanos
    Route::get('/solicitudes_altas', [RhController::class, 'solicitudesAltas'])->name('rh.solicitudesAltas');
    Route::get('/solicitudes_altas/{id}', [RhController::class, 'detalleSolicitud'])->name('rh.detalleSolicitud');
    Route::post('/aceptar_solicitud/{id}', [RhController::class, 'aceptarSolicitud'])->name('rh.aceptarSolicitud');
    Route::post('/enviar_observacion/{id}', [RhController::class, 'enviarObservacion'])->name('rh.observacion_solicitud');
    Route::post('/rechazar_solicitud/{id}', [RhController::class, 'rechazarSolicitud'])->name('rh.rechazarSolicitud');
    Route::get('/historial_solicitudes_altas', [RhController::class, 'historialSolicitudesAltas'])->name('rh.historialSolicitudesAltas');
    Route::get('/solicitudes_bajas', [RhController::class, 'solicitudesBajas'])->name('rh.solicitudesBajas');
    Route::get('/historial_solicitudes_bajas', [RhController::class, 'historialSolicitudesBajas'])->name('rh.historialSolicitudesBajas');
    Route::get('/detalle_solicitud_baja/{id}', [RhController::class, 'detalleSolicitudBaja'])->name('rh.detalleSolicitudBaja');
    Route::post('/rechzar_baja/{id}', [RhController::class, 'rechazarBaja'])->name('rh.rechazarBaja');
    Route::post('/aceptar_baja/{id}', [RhController::class, 'aceptarBaja'])->name('rh.aceptarBaja');
    Route::get('/generar_nueva_alta', [RhController::class, 'generarNuevaAltaForm'])->name('rh.generarNuevaAltaForm');
    Route::post('/guardar_alta', [RhController::class, 'guardarAlta'])->name('rh.guardarAlta');
    Route::get('/subir_archivos_alta/{id}', [RhController::class, 'subirArchivosAltaForm'])->name('rh.subirArchivosAltaForm');
    Route::post('/guardar_archivos_alta/{id}', [RhController::class, 'guardarArchivosAlta'])->name('rh.guardarArchivosAlta');
    Route::get('/generar_nueva_baja', [RhController::class, 'generarNuevaBajaForm'])->name('rh.generarNuevaBajaForm');
    Route::get('llenar_baja/{id}', [RhController::class, 'llenarBaja'])->name('rh.llenarBaja');
    Route::post('almacenar_baja/{id}', [RhController::class, 'almacenarBaja'])->name('rh.almacenarBajaNueva');
    Route::get('/archivos', [RhController::class, 'verArchivos'])->name('rh.archivos');
    Route::get('/vista_vacaciones', [RhController::class, 'vistaVacaciones'])->name('rh.vistaVacaciones');
    Route::post('/solicitud-vacaciones/{id}/cancelar', [RhController::class, 'cancelarSolicitud'])->name('rh.cancelarSolicitud');
    Route::get('/historial_vacaciones', [RhController::class, 'historialVacaciones'])->name('rh.historialVacaciones');
    Route::get('/alta-usuario', [RhController::class, 'formAlta'])->name('rh.formAlta');
    Route::get('/descargar_ficha/{id}', [RhController::class, 'exportFichaTecnica'])->name('rh.descargarFicha');
    Route::put('/solicitudes/baja/{id}', [RhController::class, 'actualizar'])->name('solicitudes.baja.actualizar');
    Route::get('/listaAsistenciaMontana', [RhController::class, 'listaAsistenciaMontana'])->name('rh.listaAsistencia');
    Route::get('reingresos', [RhController::class, 'reingresos'])->name('rh.reingresos');
    Route::get('/kardex-vacaciones', [RhController::class, 'kardexVacaciones'])->name('kardex-vacaciones');
    Route::post('/rh/asistencia-montana/guardar', [RhController::class, 'guardarAsistenciaMontana'])->name('rh.guardarAsistenciaMontana');
    Route::get('/api/usuarios-vacaciones/{fecha}', [RHController::class, 'obtenerUsuariosEnVacaciones'])->name('api.usuarios.vacaciones');
    Route::get('/api/usuarios-permisos/{fecha}', [RHController::class, 'obtenerUsuariosConPermisos'])->name('api.usuarios.permisos');
    Route::get('/api/verificar-asistencia/{fecha}', [RHController::class, 'verificarAsistenciaExistente'])
        ->name('api.verificar.asistencia');

    Route::get('/kardex-vacaciones-pdf', function () {
        return view('kardex.vacaciones');
    })->name('kardex.vacaciones');

    Route::get('/descargar-bajas', function () {
        return (new BajasSpreadsheetExport)->generateFile();
    })->name('exportar.bajas');

    Route::get('/descargar-altas', function () {
        return (new AltasSpreadsheetExport)->generateFile();
    })->name('exportar.altas');

    Route::get('/descargar-vacaciones', function () {
        return (new VacacionesSpreadsheetExport)->generateFile();
    })->name('exportar.vacaciones');

    Route::get('/descargar-vacaciones-cortes', function () {
        $inicio = request()->query('inicio');
        $fin = request()->query('fin');

        return (new VacacionesCortesExport)->generateFile($inicio, $fin);
    })->name('exportar.vacacionesCortes');

    Route::get('/exportar-asistencias', function () {
        return (new \App\Exports\AsistenciasSpreadsheetExport(
            request('punto'),
            request('fecha_inicio'),
            request('fecha_fin')
        ))->generateFile();
    })->name('exportar.asistencias');

    Route::post('/admin/procesar-reingresos', [ReingresoController::class, 'procesarReingresos'])->name('reingresos.procesar');

    // Usuario 'User'
    Route::get('/solicitar_baja', [UserController::class, 'solicitarBajaForm'])->name('user.solicitarBajaForm');
    Route::post('/registrar_solicitud_baja/{id}', [UserController::class, 'solicitarBaja'])->name('user.registrarSolicitudBaja');
    Route::get('/solicitar_vacaciones_form', [UserController::class, 'solicitarVacacionesForm'])->name('user.solicitarVacacionesForm');
    Route::post('/solicitar_vacaciones/{id}', [UserController::class, 'solicitarVacaciones'])->name('user.solicitarVacaciones');
    Route::get('/historial_solicitudes_vacaciones', [UserController::class, 'historialVacaciones'])->name('user.historialVacaciones');
    Route::get('/ver_ficha/{id}', [UserController::class, 'verFicha'])->name('user.verFicha');
    Route::get('/buzon', [UserController::class, 'buzon'])->name('user.buzon');
    Route::post('/enviar_sugerencia/{id}', [UserController::class, 'enviarSugerencia'])->name('user.enviarSugerencia');

    Route::get('/api/dias-utilizados/{userId}/{periodo}', function ($userId, $periodo) {
        $diasUtilizados = \App\Models\SolicitudVacaciones::where('user_id', $userId)
            ->where('periodo', $periodo)
            ->sum('dias_solicitados');

        return response()->json(['dias_utilizados' => $diasUtilizados]);
    })->name('api.dias.utilizados');

    Route::post('/api/usuarios/buscar', [UserController::class, 'buscarUsuarios'])
        ->name('api.usuarios.buscar');

    /*
     * MONITORISTA
     */
    Route::get('/ver_deducciones', [MonitoreoController::class, 'verDeducciones'])->name('monitoreo.deducciones');
    Route::get('/mapa', [MonitoreoController::class, 'mapa'])->name('monitoreo.mapa');
    Route::prefix('/monitoreo/unidades-gps')
        ->name('monitoreo.unidades-gps.')
        ->middleware('can:view-traccar-monitoring')
        ->group(function () {
            Route::get('/', [TraccarMonitoringController::class, 'index'])->name('index');
            Route::get('/data', [TraccarMonitoringController::class, 'data'])
                ->middleware('throttle:30,1')
                ->name('data');
            Route::post('/socket-token', [TraccarMonitoringController::class, 'socketToken'])
                ->middleware('throttle:10,1')
                ->name('socket-token');
            Route::get('/history', [TraccarMonitoringController::class, 'history'])
                ->middleware('throttle:20,1')
                ->name('history');
            Route::get('/address', [TraccarMonitoringController::class, 'address'])
                ->middleware('throttle:30,1')
                ->name('address');
            Route::get('/geofences', [TraccarMonitoringController::class, 'geofences'])
                ->middleware('throttle:30,1')
                ->name('geofences');
            Route::get('/alerts', [TraccarMonitoringController::class, 'alerts'])
                ->middleware('throttle:10,1')
                ->name('alerts');
            Route::post('/alerts/read', [TraccarMonitoringController::class, 'readAlerts'])
                ->middleware('throttle:30,1')
                ->name('alerts.read');
            Route::get('/reports', [GpsReportController::class, 'show'])->middleware('throttle:10,1')->name('reports');
            Route::get('/reports/xlsx', [GpsReportController::class, 'xlsx'])->middleware('throttle:5,1')->name('reports.xlsx');
            Route::get('/reports/pdf', [GpsReportController::class, 'pdf'])->middleware('throttle:5,1')->name('reports.pdf');
            Route::get('/speed-limits', [TraccarMonitoringController::class, 'speedLimits'])->name('speed-limits');
            Route::middleware('can:manage-traccar-monitoring')->group(function () {
                Route::put('/speed-limits/{deviceId}', [TraccarMonitoringController::class, 'saveSpeedLimit'])->name('speed-limits.save');
                Route::post('/geofences', [TraccarMonitoringController::class, 'createGeofence'])->name('geofences.create');
                Route::put('/geofences/{geofenceId}', [TraccarMonitoringController::class, 'updateGeofence'])->name('geofences.update');
                Route::delete('/geofences/{geofenceId}', [TraccarMonitoringController::class, 'deleteGeofence'])->name('geofences.delete');
            });
        });
    Route::get('/vehiculos', function () {
        return view('vehiculos.crud');
    })->name('vehiculos.index');
    // Detalle de Vehículo (vista Blade con Livewire)
    Route::get('/vehiculos/{id}', function ($id) {
        return view('vehiculos.detalle', ['id' => $id]);
    })->name('vehiculos.detalle');

    // Inspecciones manuales de entrega, recepción y cambio de turno.
    Route::get('/inspecciones', \App\Livewire\InspeccionesUnidades::class)->name('inspecciones.index');
    Route::get('/inspecciones/{inspeccion}', \App\Livewire\InspeccionDetalle::class)->name('inspecciones.detalle');
    Route::get('/inspecciones-evidencias/{evidencia}', [\App\Http\Controllers\InspeccionEvidenciaController::class, 'show'])
        ->name('inspecciones.evidencias.show');
    Route::get('/inspecciones-recepcion', \App\Livewire\InspeccionRecepcionBandeja::class)
        ->name('inspecciones.recepcion.index');
    Route::get('/inspecciones-recepcion/{caso}', \App\Livewire\InspeccionRevisionDetalle::class)
        ->name('inspecciones.recepcion.detalle');
    Route::get('/inspecciones-recepcion-archivos/{archivo}', [InspeccionMensajeArchivoController::class, 'show'])
        ->name('inspecciones.recepcion.archivos.show');
    Route::get('/inspecciones-reportes/semanal', \App\Livewire\InspeccionReporteSemanal::class)
        ->name('inspecciones.reportes.semanal');
    Route::get('/inspecciones-reportes/semanal.xlsx', [InspeccionReporteSemanalController::class, 'xlsx'])
        ->name('inspecciones.reportes.semanal.xlsx');
    Route::get('/inspecciones-reportes/semanal-ejecutivo.pdf', [InspeccionReportePdfController::class, 'ejecutivo'])
        ->name('inspecciones.reportes.semanal.pdf.ejecutivo');
    Route::get('/inspecciones-reportes/semanal-incidencias.pdf', [InspeccionReportePdfController::class, 'incidencias'])
        ->name('inspecciones.reportes.semanal.pdf.incidencias');
    Route::get('/inspecciones/{inspeccion}/expediente.pdf', [InspeccionReportePdfController::class, 'expediente'])
        ->name('inspecciones.reportes.expediente.pdf');

    // Servicios CRUD (Livewire)
    Route::get('/servicios', \App\Livewire\ServiciosCrud::class)->name('servicios.index');
    // Detalle de Servicio (Livewire)
    Route::get('/servicios/{id}', \App\Livewire\ServicioDetalle::class)->name('servicio.detalle');

    // Siniestros CRUD (Livewire)
    Route::get('/siniestros', \App\Livewire\SiniestrosCrud::class)->name('siniestros.index');
    // Detalle del Siniestro (Livewire)
    Route::get('/siniestros/{id}', \App\Livewire\SiniestroDetalle::class)->name('siniestros.detalle');

    // Gastos CRUD (Livewire)
    Route::get('/gastos', \App\Livewire\GastosCrud::class)->name('gastos.index');
    // Detalle del Gasto (Livewire)
    Route::get('/gastos/{id}', \App\Livewire\GastoDetalle::class)->name('gastos.detalle');

    Route::get('/gasolinas', [MonitoreoController::class, 'gasolinasIndex'])->name('gasolinas.index');
    Route::get('/api/placas', function (\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        $placas = Unidades::where('placas', 'like', "%{$q}%")
            ->limit(10)
            ->pluck('placas')
            ->toArray();

        return response()->json(['placas' => $placas]);
    })->middleware('permission:map.view');

    Route::get('/api/users', function (\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        $users = User::where('name', 'like', "%{$q}%")
            ->where('estatus', 'Activo')
            ->limit(10)
            ->select('id', 'name')
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
            ->toArray();

        return response()->json(['users' => $users]);
    })->middleware('permission:map.view');

    // Compras CRUD (Livewire)
    Route::get('/compras', \App\Livewire\ComprasCrud::class)->name('compras.index');
    // Detalle de Compra (Livewire)
    Route::get('/compras/{id}', \App\Livewire\CompraDetalle::class)->name('compras.detalle');

    Route::get('/mapa-geocercas', [CustodiosController::class, 'mostrarMapaGeocercas'])
        ->middleware(['permission:custodians.access', 'custodios.role', 'module.enabled:erp_custodios'])
        ->name('admin.mapaGeocercas');
    Route::get('/api/custodios/geocercas-activas', [CustodiosController::class, 'geocercasActivasRealtime'])
        ->middleware(['permission:custodians.access', 'custodios.role', 'module.enabled:erp_custodios'])
        ->name('admin.geocercasActivasRealtime');
    Route::get('/detalle-mision/{mision}', [CustodiosController::class, 'verDetalleMision'])
        ->middleware(['permission:custodians.access', 'custodios.role', 'module.enabled:erp_custodios'])
        ->name('admin.detalleMision');

    // Usuario Aux Admin
    Route::get('/nuevas_altas_elementos', [AuxadminController::class, 'nuevasAltas'])->name('aux.nuevasAltas');
    Route::post('/subida_documentacion/{id}', [AuxadminController::class, 'guardarAcuses'])->name('documentacion.subir');
    Route::get('/listado_usuarios', [AuxadminController::class, 'listadoUsuarios'])->name('aux.usuariosList');
    Route::post('/actualizacion_documentacion/{id}', [AuxadminController::class, 'actualizarAcuses'])->name('documentacion.actualizar');
    Route::get('/confrontas', [AuxadminController::class, 'confrontasForm'])->name('aux.confrontas');
    Route::post('/confrontas-upload', [AuxadminController::class, 'confrontasUpload'])->name('confrontas.upload');

    Route::get('/sipare', [SipareController::class, 'form'])->name('aux.sipareForm');
    Route::post('/sipare/upload', [SipareController::class, 'upload'])->name('aux.sipareUpload');

    Route::get('/cedulas', [CedulaController::class, 'form'])->name('aux.cedulasForm');
    Route::post('/cedulas/upload/{tipo}', [CedulaController::class, 'upload'])->name('aux.cedulasupload');

    Route::get('/acuses-bajas', [BajaAcuseController::class, 'index'])->name('aux.acusesbajas');
    Route::post('/acuses-bajas/{solicitudBaja}', [BajaAcuseController::class, 'upload'])->name('aux.acusesbajasupload');
    Route::get('/acuses-bajas-historial', [BajaAcuseController::class, 'historialAcusesBajas'])->name('aux.historialAcuseBajas');

    Route::get('/historial_cedulas', [AuxadminController::class, 'historialCedulas'])->name('aux.historialCedulas');
    Route::get('/historial_sipare', [AuxadminController::class, 'historialSipare'])->name('aux.historialSipare');
    Route::get('/historial_acuses_altas', [AuxadminController::class, 'historialAcusesAlta'])->name('aux.historialAcusesAlta');

    Route::post('/riesgos-trabajo/actualizar', [RiesgoTrabajoController::class, 'actualizar'])->name('riesgos-trabajo.actualizar');

    // nuevass rutas
    Route::get('/riesgos-trabajo', [RiesgoTrabajoController::class, 'index'])->name('aux.riesgosTrabajo');
    Route::get('/riesgos-trabajo/generar/{user}', [RiesgoTrabajoController::class, 'create'])->name('aux.generarRiesgoForm');
    Route::post('/riesgos-trabajo/guardar', [RiesgoTrabajoController::class, 'store'])->name('aux.guardarRiesgo');
    // nuevas rutas incapacidades
    Route::get('/incapacidades', [IncapacidadController::class, 'index'])->name('aux.incapacidadesList');
    Route::get('/incapacidades/generar/{user}', [IncapacidadController::class, 'create'])->name('aux.generarIncapacidadForm');
    Route::post('/incapacidades/guardar', [IncapacidadController::class, 'store'])->name('aux.guardarIncapacidad');
    // historial incapacidades
    Route::get('/aux/historial-incapacidades', [IncapacidadController::class, 'showIncapacidadesHistory'])->name('aux.historialIncapacidades');
    Route::get('/aux/historial-riesgos-trabajo', [RiesgoTrabajoController::class, 'showHistorialRiesgosTrabajo'])->name('aux.historialRiesgosTrabajo');
    Route::get('/reporte/incapacidades', [IncapacidadReporteController::class, 'generarPdf'])->name('reporte.incapacidades.pdf');
    // graficas
    Route::get('/graficos', [GraficosController::class, 'index'])->name('auxadmin.index');

    // Usuario nominas
    Route::get('/antiguedades', [NominasController::class, 'antiguedades'])->name('nominas.usersAntiguedades');
    Route::get('/finiquitos', [NominasController::class, 'verBajas'])->name('nominas.verBajas');
    Route::get('/nuevas_altas', [NominasController::class, 'nuevasAltas'])->name('nominas.nuevasAltas');
    Route::post('/guardar-calculo-finiquito', [NominasController::class, 'guardarCalculoFiniquito'])->name('guardar.calculo.finiquito');
    Route::post('/guardar_finiquito/{id}', [NominasController::class, 'guardarFiniquitoManual'])->name('guardarFiniquitoManual');
    Route::get('/finiquitos/{solicitud}/archivo', [NominasController::class, 'descargarFiniquito'])->name('finiquitos.archivo');
    Route::get('/asistencias_nominas', [NominasController::class, 'asistenciasNominas'])->name('nominas.asistencias');
    Route::get('/vacaciones_nominas', [NominasController::class, 'vacacionesNominas'])->name('nominas.vacaciones');
    Route::get('/nominas_vacaciones', [NominasController::class, 'vacacionesIndex'])->name('nominas.vacaciones');
    Route::get('/nominas', [NominasController::class, 'vistaNominas'])->name('vistaNominas');
    Route::get('/calculos_nominas', [NominasController::class, 'calculosNominas'])->name('nominas.calculos');
    Route::get('/graficas_estadisticas', [NominasController::class, 'graficas'])->name('nominas.graficas');
    Route::get('/deducciones', [NominasController::class, 'deduccionesIndex'])->name('nominas.deducciones');
    Route::get('/nueva_deduccion', [NominasController::class, 'nuevaDeduccionForm'])->name('crearDeduccion');
    Route::post('/guardar_deduccion', [NominasController::class, 'guardarDeduccion'])->name('guardarDeduccion');
    Route::post('/asginar_num_empleado', [NominasController::class, 'asignarNumEmpleado'])->name('nominas.asignarNumeroEmpleado');
    Route::post('/solicitar-constancia', [NominasController::class, 'solicitarConstancia'])->name('solicitar.constancia');
    Route::get('/destajos', [NominasController::class, 'destajos'])->name('nominas.destajos');
    Route::get('/nominas_subidas_archivos', [NominasController::class, 'subidasArchivosForm'])->name('nominas.subidaArchivos');
    Route::post('/nominas_subir_archivos', [NominasController::class, 'subirArchivosNominas'])->name('nominas.guardarArchivos');
    Route::get('/registros_nominas', [NominasController::class, 'registros'])->name('nominas.registros');
    Route::get('/calculo_destajos', [NominasController::class, 'calculoDestajos'])->name('nominas.calculoDestajos');
    Route::get('/nominas/semanal', [NominasController::class, 'formularioSemanal'])->name('nominas.semanal');
    Route::get('/historial-deducciones', [NominasController::class, 'historialDeducciones'])->name('nominas.historialDeducciones');
    Route::post('/nominas/semanal/guardar', [NominasController::class, 'guardarSemanal'])->name('nominas.guardarSemanal');
    Route::get('/historial-finiquitos-bajas', [NominasController::class, 'historialFiniquitos'])->name('nominas.historialFiniquitos');
    Route::get('/exportar-destajos', [NominasController::class, 'exportar'])->name('exportar.destajos');

    Route::get('/exportar-altas-por-corte', function (Request $request) {
        $request->validate([
            'inicio' => 'required|date',
            'fin' => 'required|date|after_or_equal:inicio',
        ]);

        return (new AltasPorCorteExport($request->inicio, $request->fin))->generateFile();
    })->name('exportar.altas.corte');

    Route::post('/notificaciones/leidas', function () {
        $user = Auth::user();

        if ($user) {
            \App\Models\ToastNotificationLog::markReadFor($user);
        }

        return response()->json(['ok' => true]);
    })->name('notificaciones.leer');

    // Custodios
    Route::middleware(['permission:custodians.access', 'custodios.role', 'module.enabled:erp_custodios'])->group(function () {
        Route::get('/nueva_mision', [CustodiosController::class, 'nuevaMisionForm'])->name('custodios.nuevaMisionForm');
        Route::post('/agentes-disponibles', [CustodiosController::class, 'obtenerAgentesDisponibles'])
            ->name('custodios.agentesDisponibles');
        Route::post('/guardarMision', [CustodiosController::class, 'guardarMision'])->name('misiones.store');
        Route::get('/misiones', [CustodiosController::class, 'misionesIndex'])->name('custodios.misiones');
        Route::get('/custodios', [CustodiosController::class, 'custodiosIndex'])->name('custodios.elementos');
        Route::get('/historial_misiones', [CustodiosController::class, 'historialMisiones'])->name('custodios.historialMisiones');
        Route::get('/misiones_terminadas', [CustodiosController::class, 'misionesTerminadas'])->name('custodios.misionesTerminadas');
        Route::get('/misiones/{mision}/editar', [CustodiosController::class, 'edit'])->name('misiones.edit');
        Route::put('/misiones/{mision}', [CustodiosController::class, 'update'])->name('misiones.update');
        Route::patch('/misiones/{mision}/estado', [CustodiosController::class, 'updateStatus'])
            ->name('misiones.estado.update');
        Route::patch('/misiones/{mision}/revision', [CustodiosController::class, 'updateRevision'])
            ->name('misiones.revision.update');
        Route::prefix('misiones/{mision}/itinerarios')->name('misiones.itinerarios.')->group(function () {
            Route::get('/', [CustodiosController::class, 'mostrarItinerarios'])->name('show');
            Route::get('/pdf', [CustodiosController::class, 'downloadItinerarios'])->name('pdf');
        });
        Route::prefix('misiones/{mision}/gastos')->name('misiones.gastos.')->group(function () {
            Route::get('/', [CustodiosController::class, 'mostrarGastos'])->name('show');
            Route::get('/pdf', [CustodiosController::class, 'downloadGastos'])->name('pdf');
        });
        Route::get('/misiones/{mision}/cierres-operativos', [CustodiosController::class, 'mostrarCierresOperativos'])
            ->name('misiones.cierres-operativos.show');
        Route::prefix('misiones/{mision}/reporte-operativo')->name('misiones.reporte-operativo.')->group(function () {
            Route::get('/', [CustodiosController::class, 'mostrarReporteOperativo'])->name('show');
            Route::get('/pdf', [CustodiosController::class, 'downloadReporteOperativo'])->name('pdf');
        });
    });
    // Route::get('mensajes', [CustodiosController::class,'mensajesIndex'])->name('custodios.mensajes');

    // Usuario Auxiliar Contabilidad
    Route::get('/Lista_finiquitos', [AuxContController::class, 'listaFiniquitos'])->name('auxcont.finiquitos');
    Route::post('/subir-cheque/{id}', [AuxcontController::class, 'subirCheque'])->name('subir.cheque');
    Route::put('/solicitud_bajas/{id}/actualizar_cheque', [AuxcontController::class, 'actualizarCheque'])
        ->middleware('permission:severance-checks.manage');
    Route::get('historial_cheques', [AuxcontController::class, 'historialCheques'])->name('auxcont.finiquitos.historial');
    Route::get('/eventuales', [AuxcontController::class, 'eventualesList'])->name('auxcont.eventuales');
    Route::get('/vales-comida', [AuxcontController::class, 'valesComida'])->name('auxcont.valesComida');
    Route::post('/vales-comida/{id}/aceptar', [AuxcontController::class, 'aceptarSolicitudVales'])->name('vales.comida.aceptar');
    Route::post('/vales-comida/{id}/rechazar', [AuxcontController::class, 'rechazarSolicitudVales'])->name('vales.comida.rechazar');
    Route::get('/vales-comida/ver-comprobantes', [AuxcontController::class, 'verComprobantes'])->name('auxcont.comprobantesVales');
    Route::post('/vales-comida/{id}/aprobar-comprobacion', [AuxcontController::class, 'aprobarComprobacion'])->name('vales.comprobacion.aprobar');
    Route::post('/vales-comida/{id}/rechazar-comprobacion', [AuxcontController::class, 'rechazarComprobacion'])->name('vales.comprobacion.rechazar');
    Route::get('/historial_vales_comida', [AuxcontController::class, 'historialValesComida'])->name('auxcont.historialVales');
    Route::get('/api/vales-comida/{id}/comprobantes', [AuxcontController::class, 'obtenerComprobantes'])->name('vales.comprobantes.api');
    Route::get('/exportar-vales-comida', [AuxcontController::class, 'exportarValesComida'])->name('vales.comida.exportar');
    Route::get('/exportar-registros-eventuales', [AuxcontController::class, 'exportarRegistrosEventuales'])->name('registros.eventuales.exportar');

    // Usuario Juridico
    Route::get('lista_nuevasBajas', [JuridicoController::class, 'listaNuevasBajas'])->name('juridico.nuevasBajas');
    Route::post('/actualizar-motivo-baja', [JuridicoController::class, 'actualizarMotivoBaja'])->name('actualizar.motivo.baja');

    // Usuario Operaciones
    Route::get('/eventuales_list', [OperacionesController::class, 'eventualesList'])->name('operaciones.eventuales');
    Route::post('/operaciones/registrar-eventual', [OperacionesController::class, 'storeRegistroEventual'])->name('operaciones.registrar.eventual');
    Route::get('/vales', [OperacionesController::class, 'valesIndex'])->name('operaciones.valesComida');
    Route::get('/pagos_eventuales', [OperacionesController::class, 'pagosEventuales'])->name('operaciones.pagosEventuales');
    Route::post('/operaciones/subir-pago-eventual/{id}', [OperacionesController::class, 'subirPagoEventual'])->name('operaciones.subir.pago.eventual');
    Route::get('/historial-pagos-eventuales', [OperacionesController::class, 'historialPagosEventuales'])->name('operaciones.historialPagosEventuales');
    Route::get('/vales-comida/crear', [OperacionesController::class, 'createValeComida'])->name('vales.comida.crear');
    Route::get('/vales_pendientes', [operacionesController::class, 'valesPendientes'])->name('operaciones.valesPendientes');

    Route::get('/vales-comida/{id}/comprobantes', [OperacionesController::class, 'mostrarFormularioComprobantes'])->name('vales.comprobantes.formulario');
    Route::post('/vales-comida/{id}/comprobantes', [OperacionesController::class, 'subirComprobantes'])->name('vales.comprobantes.subir');
    Route::get('/eventuales/{id}/detalles', [OperacionesController::class, 'show'])->name('eventuales.detalles');
    Route::get('/asistencia_diaria', [OperacionesController::class, 'asistenciaDiaria'])->name('operaciones.asistenciaDiaria');

    Route::get('/operaciones/asistencia/punto/{punto}', [OperacionesController::class, 'listaAsistencia'])->name('operaciones.listaAsistencia');
    Route::post('/operaciones/asistencia/guardar', [OperacionesController::class, 'guardarAsistencias'])->name('operaciones.guardarAsistencias');

    Route::get('/operaciones/asistencia/confirmar-faltas', [OperacionesController::class, 'confirmarFaltas'])->name('operaciones.confirmarFaltas');

    Route::post('/operaciones/asistencia/finalizar', [OperacionesController::class, 'finalizarAsistencia'])->name('operaciones.finalizarAsistencia');

    Route::get('/operaciones/faltas-justificar', [OperacionesController::class, 'faltasJustificar'])->name('operaciones.faltasJustificar');
    Route::post('/operaciones/faltas-justificar', [OperacionesController::class, 'guardarFaltaJustificada'])->name('operaciones.guardarFaltaJustificada');
    Route::get('/historial-faltas-justificadas', [OperacionesController::class, 'historialFaltasJustificadas'])->name('operaciones.historialFaltasJustificadas');

    Route::get('/operaciones/permisos', [OperacionesController::class, 'permisosIndex'])->name('operaciones.permisosIndex');
    Route::get('/operaciones/permisos/crear', [OperacionesController::class, 'crearPermiso'])->name('operaciones.crearPermiso');
    Route::post('/operaciones/permisos', [OperacionesController::class, 'guardarPermiso'])->name('operaciones.guardarPermiso');

    Route::get('/api/empleados/buscar', [OperacionesController::class, 'buscarEmpleados'])->name('api.empleados.buscar');

    // Mensajería
    Route::get('/mensajes/nuevo', [ChatWebController::class, 'crear'])->name('mensajes.crearChat');
    Route::post('/mensajes/nuevo', [ChatWebController::class, 'storeConversacion'])->name('mensajes.nueva');
    Route::get('/mensajes', [ChatWebController::class, 'index'])->name('mensajes.index');
    Route::get('/mensajes/{conversation}', [ChatWebController::class, 'show'])->name('mensajes.show');
    Route::post('/mensajes/enviar', [ChatWebController::class, 'storeMensaje'])->name('mensajes.store');

    // Ubicaciones en tiempo real
    Route::get('/api/realtime-position/user/{id}/recent', [RealtimePositionController::class, 'getUserRecentPositions'])
        ->middleware('permission:map.view');
});

require __DIR__.'/auth.php';
