@php
    use Carbon\Carbon;
    $authUser = Auth::user();
    $esOperaciones = in_array($authUser->rol, ['OPERACIONES', 'AUXILIAR OPERACIONES'], true);
@endphp

<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">

                <!-- Encabezado Principal -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-700 dark:to-blue-900 px-6 py-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="bg-white/20 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-white">Ficha Técnica del Usuario</h1>
                            <p class="mt-1 text-blue-100">Detalles completos del empleado y su documentación</p>
                        </div>
                    </div>
                </div>

                <!-- Alertas -->
                @if(session('success'))
                    <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 dark:border-green-400 text-green-700 dark:text-green-200 p-4 mx-6 mt-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Contenido Principal -->
                @if (!$solicitud)
                    <div class="p-6">
                        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl p-6 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-red-400 dark:text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="text-lg font-medium text-red-800 dark:text-red-200">Información no disponible</h3>
                            <p class="mt-2 text-red-700 dark:text-red-400">No hay datos de solicitud asociados a este usuario.</p>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 p-6">

                        <!-- Columna Izquierda - Imagen y Resumen -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Foto del usuario -->
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 text-center border-b border-gray-200 dark:border-gray-600 pb-2">Foto del Solicitante</h3>

                                @if ($documentacion?->arch_foto)
                                    <div class="flex flex-col items-center">
                                        <img src="{{ asset($documentacion->arch_foto) }}" alt="Foto del usuario" class="w-32 h-32 object-cover rounded-full shadow-lg border-4 border-white dark:border-gray-600">
                                        <a href="{{ asset($documentacion->arch_foto) }}" target="_blank" class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Ver imagen
                                        </a>
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">No hay foto cargada</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Estado y Fechas -->
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-600 pb-2">Estado y Fechas</h3>
                                <ul class="space-y-3">
                                    <li class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Estatus:</span>
                                        <span class="text-sm font-medium">
                                            @if($user->estatus == 'Reingreso')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200">Reingreso</span>
                                            @elseif($user->estatus == 'Activo')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">Activo</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">Inactivo</span>
                                            @endif
                                        </span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Ingreso:</span>
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $user?->solicitudAlta->fecha_ingreso ? Carbon::parse($user?->solicitudAlta->fecha_ingreso)->format('d/m/Y') : 'N/D' }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Nacimiento:</span>
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $solicitud?->fecha_nacimiento ? Carbon::parse($solicitud?->fecha_nacimiento)->format('d/m/Y') : 'N/D' }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Columna Derecha - Información Detallada -->
                        <div class="lg:col-span-3 space-y-6">

                            <!-- Información Personal -->
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-600 pb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Información Personal
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre completo</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->nombre }} {{ $solicitud?->apellido_paterno }} {{ $solicitud?->apellido_materno }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">CURP</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->curp }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">RFC</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->rfc }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">NSS</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->nss }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado Civil</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->estado_civil ?: 'N/D' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->telefono ?: 'N/D' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Información Fiscal -->
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-600 pb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Información Fiscal y Domicilio
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->email ?: 'N/D' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Liga RFC</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->liga_rfc ?: 'N/D' }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Domicilio Fiscal</p>
                                        <p class="text-gray-900 dark:text-white">
                                            {{ $solicitud?->domicilio_calle }}
                                            #{{ $solicitud?->domicilio_numero }},
                                            {{ $solicitud?->domicilio_colonia }},
                                            {{ $solicitud?->domicilio_ciudad }},
                                            {{ $solicitud?->domicilio_estado }},
                                            CP {{ $solicitud?->cp_fiscal }}
                                        </p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Domicilio de Comprobante</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->domicilio_comprobante ?: 'N/D' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Información Laboral y Bancaria -->
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-600 pb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Información Laboral y Bancaria
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Puesto</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->rol ?: 'N/D' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Departamento</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->departamento ?: 'N/D' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Empresa</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->empresa ?: 'N/D' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Punto</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->punto ?: 'N/D' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sueldo Mensual</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->sueldo_mensual ?: 'N/D' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">SD</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->sd ?: 'N/D' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">SDI</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->sdi ?: 'N/D' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Reingreso</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->reingreso ?: 'N/D' }}</p>
                                    </div>

                                    <!-- Nuevos Campos Bancarios -->
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de Pago</p>
                                        <p class="text-gray-900 dark:text-white">{{ $solicitud?->tipo_periodo_formatted ?? 'No Disponible' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Banco</p>
                                        <p class="text-gray-900 dark:text-white flex items-center">
                                            @if($solicitud?->banco)
                                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                </svg>
                                                {{ $solicitud?->banco }}
                                            @else
                                                N/D
                                            @endif
                                        </p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cuenta Bancaria / CLABE</p>
                                        <p class="text-gray-900 dark:text-white font-mono tracking-wide">{{ $solicitud?->cuenta_bancaria ?: 'N/D' }}</p>
                                    </div>
                                    <!-- Fin Nuevos Campos -->
                                </div>
                            </div>

                            <!-- Documentación -->
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-600 pb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Documentación
                                </h3>

                                @if ($esOperaciones)
                                    <div class="text-center py-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a2 2 0 00-2-2H4a2 2 0 00-2 2v6a2 2 0 002 2h10V7z" />
                                        </svg>
                                        <p class="text-gray-600 dark:text-gray-300 text-sm font-medium">Sección no disponible</p>
                                        <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">Solo el personal autorizado puede ver la documentación.</p>
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                        @foreach([
                                            'arch_solicitud_empleo' => 'Solicitud de Empleo',
                                            'arch_acta_nacimiento' => 'Acta de Nacimiento',
                                            'arch_curp' => 'CURP',
                                            'arch_ine' => 'INE',
                                            'arch_comprobante_domicilio' => 'Comprobante de Domicilio',
                                            'arch_rfc' => 'RFC',
                                            'arch_comprobante_estudios' => 'Comprobante de Estudios',
                                            'arch_nss' => 'NSS',
                                            'arch_contrato' => 'Contrato',
                                            'arch_carta_rec_laboral' => 'Carta Rec. Laboral',
                                            'arch_carta_rec_personal' => 'Carta Rec. Personal',
                                            'arch_cartilla_militar' => 'Cartilla Militar',
                                            'arch_infonavit' => 'Infonavit',
                                            'arch_fonacot' => 'Fonacot',
                                            'arch_licencia_conducir' => 'Licencia de Conducir',
                                            'arch_carta_no_penales' => 'Carta No Penales',
                                            'arch_acuse_imss' => 'Acuse de IMSS',
                                            'arch_retencion_infonavit' => 'Retención Infonavit',
                                            'arch_foto' => 'Fotografía',
                                            'visa' => 'Visa',
                                            'pasaporte' => 'Pasaporte'
                                        ] as $campo => $label)
                                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ $label }}</span>
                                                    @if($documentacion?->$campo)
                                                        <a href="{{ asset($documentacion->$campo) }}" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                        </a>
                                                    @else
                                                        <span class="inline-flex items-center px-1.5 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200">
                                                            N/A
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 px-6 py-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap justify-center gap-3">
                            @if ($esOperaciones)
                                <a href="{{ route('dashboard') }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Regresar
                                </a>
                            @else
                                @if($user->estatus != 'Inactivo')
                                    @if(Auth::user()->rol == 'admin' || Auth::user()->solicitudAlta->departamento == 'Recursos Humanos' || in_array(Auth::user()->solicitudAlta->rol, ['AUXILIAR RECURSOS HUMANOS', 'AUXILIAR RH', 'AUX RH', 'Auxiliar RH', 'Auxiliar Recursos Humanos', 'Aux RH']) || in_array(Auth::user()->rol, ['AUXILIAR RECURSOS HUMANOS', 'Auxiliar recursos humanos']))
                                        <a href="{{ route('admin.editarUsuarioForm', $user->id) }}"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Editar
                                        </a>

                                        <a href="{{ route('sup.solicitarVacacionesElementoForm', $user->id) }}"
                                            class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Vacaciones
                                        </a>

                                        <a href="{{ route('rh.descargarFicha', $user->id) }}"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Descargar
                                        </a>

                                        @if ((Auth::user()->rol == 'admin' || Auth::user()->solicitudAlta->departamento == 'Recursos Humanos' || in_array(Auth::user()->solicitudAlta->rol, ['AUXILIAR RECURSOS HUMANOS', 'AUXILIAR RH', 'AUX RH', 'Auxiliar RH', 'Auxiliar Recursos Humanos', 'Aux RH']) || in_array(Auth::user()->rol, ['AUXILIAR RECURSOS HUMANOS', 'Auxiliar recursos humanos'])) && $user->estatus == 'Activo')
                                            <button onclick="confirmarBaja({{ $user->id }})"
                                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Baja
                                            </button>
                                        @endif
                                    @endif
                                @elseif($user->estatus == 'Inactivo')
                                    @if(Auth::user()->rol == 'admin' || Auth::user()->solicitudAlta->departamento == 'Recursos Humanos' || in_array(Auth::user()->solicitudAlta->rol, ['AUXILIAR RECURSOS HUMANOS', 'AUXILIAR RH', 'AUX RH', 'Auxiliar RH', 'Auxiliar Recursos Humanos', 'Aux RH']) || in_array(Auth::user()->rol, ['AUXILIAR RECURSOS HUMANOS', 'Auxiliar recursos humanos']))
                                        <a href="{{ route('admin.editarUsuarioForm', $user->id) }}"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Editar
                                        </a>
                                        <button onclick="confirmarReingreso({{ $user->id }})"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Reingreso
                                        </button>
                                    @endif
                                @endif

                                @if (Auth::user()->rol == 'admin')
                                    <a href="{{ route('admin.verUsuarios') }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Usuarios
                                    </a>
                                @elseif(in_array(Auth::user()->rol, ['AUXILIAR NOMINAS', 'Auxiliar Nominas']) || in_array(Auth::user()->solicitudAlta->rol ?? '', ['AUXILIAR NOMINAS', 'Auxiliar Nominas', 'Auxiliar nominas']))
                                    @if($documentacion->arch_rfc == null && $user->estatus == 'Activo')
                                        <button onclick="enviarNotificacion({{ $user->id }})"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            Const. Fiscal
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.verUsuarios') }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Usuarios
                                    </a>
                                @else
                                    <a href="{{ route('dashboard') }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Dashboard
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    // Tus funciones JS anteriores permanecen iguales
    function confirmarBaja(userId) {
        Swal.fire({
            title: '¿Estás seguro?',
            html: `
                <p class="mb-2">Esto cambiará el estatus del usuario a 'Inactivo'.</p>
                <label for="fechaBaja" class="block mb-1 text-sm text-left">Fecha de baja:</label>
                <input type="date" id="fechaBaja" class="swal2-input" style="width: auto;">

                <label for="motivoBaja" class="block mt-3 mb-1 text-sm text-left">Motivo:</label>
                <select id="motivoBaja" class="swal2-input" style="width: auto;">
                    <option value="">Seleccione un motivo</option>
                    <option value="Renuncia">Renuncia</option>
                    <option value="Ausentismo">Ausentismo</option>
                    <option value="Separación voluntaria">Separación voluntaria</option>
                </select>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, dar de baja',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const fecha = document.getElementById('fechaBaja').value;
                const motivo = document.getElementById('motivoBaja').value;

                if (!fecha) {
                    Swal.showValidationMessage('Debes ingresar una fecha de baja');
                    return false;
                }
                if (!motivo) {
                    Swal.showValidationMessage('Debes seleccionar un motivo');
                    return false;
                }

                return { fecha, motivo };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const { fecha, motivo } = result.value;
                enviarAccionPost(`/admin/baja_usuario/${userId}`, { fecha, motivo });
            }
        });
    }

    function confirmarReingreso(userId) {
        Swal.fire({
            title: '¿Confirmas generar el reingreso?',
            html: `
                <p class="mb-2">Esto añadirá un nuevo reingreso para el usuario.</p>
                <label for="fechaReingreso" class="block mb-1 text-sm text-left">Fecha de reingreso:</label>
                <input type="date" id="fechaReingreso" class="swal2-input" style="width: auto;">
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, generar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const fecha = document.getElementById('fechaReingreso').value;
                if (!fecha) {
                    Swal.showValidationMessage('Debes ingresar una fecha de reingreso');
                }
                return fecha;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const fecha = result.value;
                enviarAccionPost(`/reingreso/${userId}`, { fecha });
            }
        });
    }

    function enviarAccionPost(url, data) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        const values = { _token: '{{ csrf_token() }}', ...data };
        Object.entries(values).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }

    function enviarNotificacion(userId) {
        fetch("{{ route('solicitar.constancia') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(res => res.json())
        .then(data => {
            if(data.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Solicitud enviada',
                    text: 'Tu solicitud fue enviada a Recursos Humanos.'
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al enviar tu solicitud.'
            });
        });
    }
</script>
