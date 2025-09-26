@php
    use Carbon\Carbon;

    $hoy = Carbon::now('America/Mexico_City');
    $inicioAnio = now()->startOfYear();

    $fechaIngreso = Carbon::parse($user->fecha_ingreso);
    $ultimaAsistencia = Carbon::parse($solicitud->ultima_asistencia);
    $fechaBaja = Carbon::parse($solicitud->fecha_baja ?? now());

    $inicioMes = $fechaBaja->copy()->startOfMonth();
    $quincena = $fechaBaja->copy()->day(15);

    if ($fechaBaja->lessThan($quincena)) {
        $diasQuincena = $inicioMes->diffInDays($fechaBaja);
    } else {
        $diasQuincena = $quincena->diffInDays($fechaBaja);
    }

    $diasTrabajadosAnio = $fechaIngreso->diffInDays($fechaBaja) + 1;
    $diasNoLaborados = $ultimaAsistencia->diffInDays($fechaBaja);
    $diasNoPagados = $diasQuincena * $solicitudAlta->sd;

    $descuentoNoLaborados = $diasNoLaborados * $solicitudAlta->sd;

    $factorVacaciones = $diasDisponibles / 365;
    $diasVacaciones = $diasTrabajadosAnio * $factorVacaciones;
    $montoVacaciones = $diasVacaciones * $solicitudAlta->sd;
    $primaVacacional = $montoVacaciones * 0.25;

    $factorAguinaldo = 15 / 365;

    $descuentoNoEntregados = $solicitud->descuento;

    if ($fechaIngreso->greaterThanOrEqualTo($inicioAnio)) {
        $diasTrabajAnio = $fechaIngreso->diffInDays($ultimaAsistencia) + 1;
    } else {
        $diasTrabajAnio = $inicioAnio->diffInDays($ultimaAsistencia) + 1;
    }
    $diasAguinaldo = $diasTrabajAnio * $factorAguinaldo;
    $montoAguinaldo = $diasAguinaldo * $solicitudAlta->sd;
    $primaAguinaldo = $montoAguinaldo * 0.25;
@endphp

<style>
    .swal2-popup.custom-modal-width {
        width: 900px !important;
        max-width: 95vw;
    }

    #finiquitoContenido table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.875rem;
    }

    #finiquitoContenido th,
    #finiquitoContenido td {
        padding: 8px 12px;
        text-align: left;
        vertical-align: middle;
        border: 1px solid #d1d5db;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    #finiquitoContenido thead th {
        background-color: #f3f4f6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
    }

    .card-section {
        transition: all 0.3s ease;
    }

    .card-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .status-en-proceso {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }

    .status-aceptada {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #22c55e;
    }

    .status-rechazada {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }

    .document-link {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background-color: #eff6ff;
        color: #1d4ed8;
        border-radius: 0.5rem;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        border: 1px solid #bfdbfe;
    }

    .document-link:hover {
        background-color: #dbeafe;
        transform: translateY(-1px);
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background-color: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background-color: #2563eb;
        transform: translateY(-1px);
    }

    .btn-success {
        background-color: #10b981;
        color: white;
    }

    .btn-success:hover {
        background-color: #059669;
        transform: translateY(-1px);
    }

    .btn-danger {
        background-color: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background-color: #dc2626;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background-color: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #4b5563;
        transform: translateY(-1px);
    }

    .btn-disabled {
        background-color: #9ca3af;
        color: #4b5563;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .calculation-card {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #bae6fd;
    }

    .calculation-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0369a1;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }

    .calculation-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #bae6fd;
    }

    .calculation-item:last-child {
        border-bottom: none;
    }

    .calculation-label {
        font-weight: 500;
        color: #0c4a6e;
    }

    .calculation-value {
        font-weight: 600;
        color: #0369a1;
    }

    .total-row {
        background-color: #0ea5e9;
        color: white;
        font-weight: 700;
        border-radius: 0.375rem;
        margin-top: 0.5rem;
    }
</style>

<x-app-layout>
    <x-navbar />

    <div class="py-6 px-4 sm:px-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header con foto y título -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl overflow-hidden mb-8">
                <div class="p-6 flex flex-col md:flex-row items-center">
                    <div class="mb-4 md:mb-0 md:mr-6">
                        @if($documentacion && $documentacion->arch_foto)
    <img src="{{ asset($documentacion->arch_foto) }}"
         alt="Foto del solicitante"
         class="w-24 h-24 rounded-full border-4 border-white/20 shadow-lg object-cover">
@else
    <div class="w-24 h-24 rounded-full border-4 border-white/20 shadow-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
    </div>
@endif
                    </div>
                    <div class="text-center md:text-left text-white">
                        <h1 class="text-3xl font-bold mb-2">{{ $user->name }}</h1>
                        <p class="text-blue-100 text-lg">Solicitud de Baja - {{ $solicitud->por ?? 'No especificado' }}</p>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Fecha de Baja: {{ \Carbon\Carbon::parse($solicitud->fecha_baja)->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información General -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Datos del Empleado -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg card-section">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Información del Empleado
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Empresa</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $user->empresa }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Punto</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $user->punto }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Fecha de Ingreso</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($user->fecha_ingreso)->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">NSS</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $solicitudAlta->nss }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Última Asistencia</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($solicitud->ultima_asistencia)->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Incapacidad</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $solicitud->incapacidad ?? 'No' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Fecha de Baja</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($solicitud->fecha_baja)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado y Documentos -->
                <div class="space-y-6">
                    <!-- Estado -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg card-section">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Estado
                            </h2>
                        </div>
                        <div class="p-6 text-center">
                            @if ($solicitud->estatus == 'En Proceso')
                                <span class="status-badge status-en-proceso">
                                    ⚙️ {{ $solicitud->estatus }}
                                </span>
                            @elseif($solicitud->estatus == 'Aceptada')
                                <span class="status-badge status-aceptada">
                                    ✅ {{ $solicitud->estatus }}
                                </span>
                            @elseif($solicitud->estatus == 'Rechazada')
                                <span class="status-badge status-rechazada">
                                    ❌ {{ $solicitud->estatus }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Documentos -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg card-section">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Documentos
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            @if ($solicitud->archivo_baja)
                                <a href="{{ asset('storage/' . $solicitud->archivo_baja) }}" target="_blank" class="document-link w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Archivo de Baja
                                </a>
                            @else
                                <p class="text-sm text-red-500">Archivo de baja no disponible</p>
                            @endif

                                @if ($solicitud->arch_renuncia)
                                    <a href="{{ asset('storage/' . $solicitud->arch_renuncia) }}" target="_blank" class="document-link w-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Renuncia Firmada
                                    </a>
                                @else
                                    <p class="text-sm text-red-500">Renuncia firmada no disponible</p>
                                @endif

                            @if ($solicitud->arch_equipo_entregado)
                                <a href="{{ asset('storage/' . $solicitud->arch_equipo_entregado) }}" target="_blank" class="document-link w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Equipo Entregado
                                </a>
                            @else
                                <p class="text-sm text-red-500">Documento de equipo entregado no disponible</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles de la Baja -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg mb-8 card-section">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Detalles de la Baja
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Motivo de Baja</p>
                            <p class="font-medium text-gray-900 dark:text-white text-lg">{{ $solicitud->por ?? 'No especificado' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Descuento por equipo/material</p>
                            <p class="font-medium text-gray-900 dark:text-white text-lg">
                                {{ $solicitud->descuento ? '$' . number_format($solicitud->descuento, 2) : 'No se ha aplicado descuento' }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Motivo (opcional)</p>
                            <p class="font-medium text-gray-900 dark:text-white whitespace-pre-line bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                {{ $solicitud->motivo ?? 'Sin detalles adicionales' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg card-section">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Acciones
                    </h2>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-4 justify-center">
                        @if (Auth::user()->rol == 'admin' ||
                                in_array(Auth::user()->rol, ['AUXILIAR NOMINAS', 'Auxiliar Nominas']) ||
                                (isset(Auth::user()->solicitudAlta) &&
                                    in_array(Auth::user()->solicitudAlta->rol, ['AUXILIAR NOMINAS', 'Auxiliar Nominas'])))
                            <button type="button" onclick="mostrarFiniquito({{ $solicitud->id }})"
                                    class="action-button btn-primary" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Ver Cálculo de Finiquito
                            </button>

                            <button type="button" onclick="openFiniquitoModal({{ $solicitud->id }})"
                                    class="action-button btn-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Añadir Finiquito
                            </button>
                        @endif
                        @if(in_array(Auth::user()->rol, ['AUXILIAR RECURSOS HUMANOS', 'AUXILIAR RH', 'AUX RH', 'Auxiliar RH', 'Auxiliar Recursos Humanos', 'Aux RH', 'admin', 'Admin']))
                            <a href="{{ route('rh.aceptarBaja', $solicitud->id) }}"
                               class="action-button btn-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Aceptar
                            </a>
                            <a href="{{ route('rh.rechazarBaja', $solicitud->id) }}"
                               class="action-button btn-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Rechazar
                            </a>
                        @endif
                        @if (
                            ($solicitud->estatus == 'En Proceso' && $solicitud->por == 'Renuncia') ||
                                ($solicitud->estatus == 'En Proceso' &&
                                    $solicitud->por == 'Separación Voluntaria' &&
                                    Auth::user()->rol == 'admin'))
                            <a href="{{ route('rh.aceptarBaja', $solicitud->id) }}"
                               class="action-button btn-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Aceptar
                            </a>
                            <a href="{{ route('rh.rechazarBaja', $solicitud->id) }}"
                               class="action-button btn-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Rechazar
                            </a>
                        @endif
                        @if (Auth::user()->rol == 'JURIDICO')
                            <button type="button" onclick="mostrarModalCambiarMotivo({{ $solicitud->id }})"
                                    class="action-button" style="background-color: #8b5cf6; color: white;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Cambiar Motivo de Baja
                            </button>
                        @endif

                        <a href="{{ route('dashboard') }}"
                           class="action-button btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Modal para añadir finiquito -->
<div id="finiquitoModal_{{ $solicitud->id }}"
     class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Añadir Finiquito
            </h3>
        </div>

        <form id="finiquitoForm_{{ $solicitud->id }}"
              action="{{ route('guardarFiniquitoManual', $solicitud->id) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            <div class="p-6">
                <div class="mb-6">
                    <label for="finiquito_archivo_{{ $solicitud->id }}"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Seleccionar archivo (PDF o imagen):
                    </label>
                    <input type="file"
                           id="finiquito_archivo_{{ $solicitud->id }}"
                           name="finiquito_archivo"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 p-3"
                           required>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        PDF o imágenes (máx. 5MB)
                    </p>
                </div>
            </div>

            <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                <button type="button"
                        onclick="closeFiniquitoModal({{ $solicitud->id }})"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2.5 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none transition-colors">
                    Guardar Finiquito
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
    const tieneRenuncia = {{ $solicitud->arch_renuncia ? 'true' : 'false' }};

    function mostrarFiniquito(solicitudId) {
        Swal.fire({
            title: 'Resumen de Finiquito',
            customClass: {
                popup: 'custom-modal-width'
            },
            html: `
            <div id="finiquitoContenido" class="text-left text-sm font-sans leading-5" style="width: 750px !important; max-width: 95vw; min-height: 300px; overflow: visible;">
                <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #3b82f6;">
                    <h2 style="font-size: 1.5rem; font-weight: bold; color: #1e40af; margin-bottom: 10px;">CÁLCULO DE FINIQUITO</h2>
                    <p><strong>Nombre:</strong> {{ $user->name }}</p>
                    <p><strong>FECHA DE INGRESO:</strong> {{ \Carbon\Carbon::parse($user->fecha_ingreso)->format('d-m-Y') }}</p>
                    <p><strong>FECHA DE BAJA:</strong> {{ \Carbon\Carbon::parse($solicitud->fecha_baja)->format('d-m-Y') }}</p>
                    <p><strong>SALARIO DIARIO:</strong> ${{ number_format($user->solicitudAlta->sd, 2) }}</p>
                </div>

                <table style="width:100%; border-collapse: collapse; margin-bottom: 20px;" border="1">
                    <thead style="background-color: #3b82f6; color: white;">
                        <tr>
                            <th style="padding: 12px; text-align: center;">Concepto</th>
                            <th style="padding: 12px; text-align: center;">Factor</th>
                            <th style="padding: 12px; text-align: center;">Días Trab.</th>
                            <th style="padding: 12px; text-align: center;">Días</th>
                            <th style="padding: 12px; text-align: center;">Salario</th>
                            <th style="padding: 12px; text-align: center;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td style="padding: 8px;">Días trabajados</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">{{ number_format($diasQuincena) }}</td><td style="padding: 8px; text-align: center;">${{ number_format($user->solicitudAlta->sd, 2) }}</td><td style="padding: 8px; text-align: center;">${{ number_format($diasNoPagados, 2) }}</td></tr>
                        <tr><td style="padding: 8px;">Extras</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td></tr>
                        <tr><td style="padding: 8px;">Festivo</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td></tr>
                        <tr><td style="padding: 8px;">Vacaciones 2025-2026</td><td style="padding: 8px; text-align: center;">{{ number_format($factorVacaciones, 9) }}</td><td style="padding: 8px; text-align: center;">{{ $diasTrabajadosAnio }}</td><td style="padding: 8px; text-align: center;">{{ number_format($diasVacaciones, 2) }}</td><td style="padding: 8px; text-align: center;">${{ number_format($user->solicitudAlta->sd, 2) }}</td><td style="padding: 8px; text-align: center;">${{ number_format($montoVacaciones, 2) }}</td></tr>
                        <tr><td style="padding: 8px;">Prima vacacional 2025-2026</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">25%</td><td style="padding: 8px; text-align: center;">${{ number_format($primaVacacional, 2) }}</td></tr>
                        <tr><td style="padding: 8px;">Aguinaldo 2025</td><td style="padding: 8px; text-align: center;">{{ number_format($factorAguinaldo, 8) }}</td><td style="padding: 8px; text-align: center;">{{ $diasTrabajAnio }}</td><td style="padding: 8px; text-align: center;">{{ number_format($diasAguinaldo, 2) }}</td><td style="padding: 8px; text-align: center;">${{ number_format($user->solicitudAlta->sd, 2) }}</td><td style="padding: 8px; text-align: center;">${{ number_format($montoAguinaldo, 2) }}</td></tr>
                        <tr style="background-color: #dbeafe;"><td colspan="5" style="padding: 12px; text-align: right; font-weight: bold;">SUBTOTAL</td><td style="padding: 12px; text-align: center; font-weight: bold;">${{ number_format($diasNoPagados + $montoVacaciones + $montoAguinaldo + $primaVacacional, 2) }}</td></tr>
                        <tr><td style="padding: 8px;">Días pagados no laborados</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">{{ number_format($diasNoLaborados, 2) }}</td><td style="padding: 8px; text-align: center;">${{ number_format($user->solicitudAlta->sd, 2) }}</td><td style="padding: 8px; text-align: center;">${{ number_format($descuentoNoLaborados, 2) }}</td></tr>
                        <tr><td style="padding: 8px;">Deducción general</td><td colspan="4" style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">${{ number_format($descuentoNoEntregados, 2) }}</td></tr>
                        <tr><td style="padding: 8px;">Adelanto de Nómina</td><td colspan="4" style="padding: 8px; text-align: center;">-</td><td style="padding: 8px; text-align: center;">-</td></tr>
                        <tr style="background-color: #10b981; color: white;"><td colspan="5" style="padding: 12px; text-align: right; font-weight: bold;">TOTAL</td><td style="padding: 12px; text-align: center; font-weight: bold;">${{ number_format($diasNoPagados + $montoVacaciones + $montoAguinaldo + $primaVacacional - $descuentoNoLaborados - $descuentoNoEntregados, 2) }}</td></tr>
                    </tbody>
                </table>
                ${!tieneRenuncia ? '<div style="background-color: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-top: 15px; border: 1px solid #fecaca;"><strong>⚠️ Advertencia:</strong> No se puede enviar el finiquito porque falta el archivo de renuncia firmada.</div>' : ''}
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: 'Guardar cálculo',
            cancelButtonText: 'Cerrar',
            confirmButtonColor: tieneRenuncia ? '#10b981' : '#9ca3af',
            didOpen: () => {
                if (!tieneRenuncia) {
                    const confirmBtn = Swal.getConfirmButton();
                    confirmBtn.disabled = true;
                }
            },
            preConfirm: () => {
                return new Promise((resolve) => {
                    const contenido = document.getElementById('finiquitoContenido');
                    contenido.style.overflow = 'visible';
                    contenido.style.maxHeight = 'none';
                    setTimeout(() => {
                        html2canvas(contenido, {
                            scrollY: -window.scrollY,
                            scale: 2,
                            useCORS: true,
                            backgroundColor: null
                        }).then(canvas => {
                            const imagenBase64 = canvas.toDataURL("image/png");

                            fetch("{{ route('guardar.calculo.finiquito') }}", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                    },
                                    body: JSON.stringify({
                                        solicitud_id: solicitudId,
                                        imagen: imagenBase64
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Guardado',
                                            'El cálculo de finiquito se guardó correctamente.',
                                            'success');
                                    } else {
                                        Swal.fire('Error', data.error ||
                                            'Hubo un error al guardar.',
                                            'error');
                                    }
                                    resolve();
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire('Error',
                                        'Error al procesar la solicitud.',
                                        'error');
                                    resolve();
                                });
                        });
                    }, 1000);
                });
            }
        });
    }

    function openFiniquitoModal(solicitudId) {
        document.getElementById('finiquitoModal_' + solicitudId).classList.remove('hidden');
    }

    function closeFiniquitoModal(solicitudId) {
        document.getElementById('finiquitoModal_' + solicitudId).classList.add('hidden');
    }

    function mostrarModalCambiarMotivo(solicitudId) {
        Swal.fire({
            title: 'Cambiar Motivo de Baja',
            html: `
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selecciona el nuevo motivo:</label>
                    <select id="motivoBaja" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="" disabled>-- Seleccionar --</option>
                        <option value="Renuncia" {{ ($solicitud->por == 'Renuncia') ? 'selected' : '' }}>Renuncia</option>
                        <option value="Ausentismo" {{ ($solicitud->por == 'Ausentismo') ? 'selected' : '' }}>Ausentismo</option>
                        <option value="Separación Voluntaria" {{ ($solicitud->por == 'Separación Voluntaria') ? 'selected' : '' }}>Separación Voluntaria</option>
                    </select>
                    <p class="mt-2 text-sm text-gray-500">Motivo actual: <strong>{{ $solicitud->por ?? 'No especificado' }}</strong></p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#8b5cf6',
            cancelButtonColor: '#6b7280',
            preConfirm: () => {
                const nuevoMotivo = document.getElementById('motivoBaja').value;

                if (!nuevoMotivo) {
                    Swal.showValidationMessage('Por favor selecciona un motivo');
                    return false;
                }

                return fetch("{{ route('actualizar.motivo.baja') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        solicitud_id: solicitudId,
                        nuevo_motivo: nuevoMotivo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: 'El motivo de baja se ha actualizado correctamente.',
                            icon: 'success',
                            confirmButtonColor: '#8b5cf6'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.error || 'No se pudo actualizar el motivo de baja.',
                            icon: 'error',
                            confirmButtonColor: '#8b5cf6'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al procesar la solicitud.',
                        icon: 'error',
                        confirmButtonColor: '#8b5cf6'
                    });
                });
            }
        });
    }

    // Cerrar modal al hacer clic fuera
    document.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('[id^="finiquitoModal_"]');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });
</script>
