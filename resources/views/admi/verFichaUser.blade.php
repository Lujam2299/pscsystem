@php
    use Carbon\Carbon;
    $authUser = Auth::user();
    $esOperaciones = in_array($authUser->rol, ['OPERACIONES', 'AUXILIAR OPERACIONES'], true);
@endphp

<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                @if(session('success'))
                    <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded-lg mb-6">
                        <div class="flex">
                            <div>
                                <p class="text-sm">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Información del Usuario
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Detalles completos del empleado y su documentación
                            </p>
                        </div>
                    </div>
                </div>

                @if (!$solicitud)
                    <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded-lg mb-6">
                        <p class="text-sm">No hay información de solicitud disponible para este usuario.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre completo</p>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $solicitud?->nombre }} {{ $solicitud?->apellido_paterno }} {{ $solicitud?->apellido_materno }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">CURP</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->curp }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">NSS</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->nss }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">RFC</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->rfc }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Email</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->email }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Teléfono</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->telefono }}</p>
                                </div>

                                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Domicilio (Comprobante)</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->domicilio_comprobante }}</p>
                                </div>

                                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Dirección Fiscal</p>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $solicitud?->domicilio_calle }}
                                        #{{ $solicitud?->domicilio_numero }},
                                        {{ $solicitud?->domicilio_colonia }},
                                        {{ $solicitud?->domicilio_ciudad }},
                                        {{ $solicitud?->domicilio_estado }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Estado Civil</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->estado_civil }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Puesto</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->rol }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Sueldo Mensual</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->sueldo_mensual }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">SD</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->sd }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">SDI</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->sdi }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Empresa</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->empresa }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Punto</p>
                                    <p class="text-gray-900 dark:text-white">{{ $solicitud?->punto }}</p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de Ingreso</p>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $user?->solicitudAlta->fecha_ingreso ? Carbon::parse($user?->solicitudAlta->fecha_ingreso)->format('d/m/Y') : 'N/D' }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de Nacimiento</p>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $solicitud?->fecha_nacimiento ? Carbon::parse($solicitud?->fecha_nacimiento)->format('d/m/Y') : 'N/D' }}
                                    </p>
                                </div>

                                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Estatus</p>
                                    <div class="flex items-center gap-2">
                                        @if($user->estatus == 'Reingreso')
                                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200 rounded-full">
                                                {{ $user->estatus }}
                                            </span>
                                        @elseif($user->estatus == 'Activo')
                                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200 rounded-full">
                                                {{ $user->estatus }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200 rounded-full">
                                                {{ $user->estatus }}
                                            </span>
                                        @endif

                                        @if($user->solicitudAlta->reingreso ?? null)
                                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200 rounded-full">
                                                Reingreso: {{ $user->solicitudAlta->reingreso }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-center">
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-lg w-full">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 text-center">Foto del solicitante</h3>

                                @if ($documentacion?->arch_foto)
                                    <div class="flex flex-col items-center">
                                        <img src="{{ asset($documentacion->arch_foto) }}" alt="Foto del usuario" class="w-40 h-40 object-cover rounded-full shadow mb-3">
                                        <a href="{{ asset($documentacion->arch_foto) }}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Ver foto completa
                                        </a>
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">No hay foto cargada</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Documentación
                            </h2>
                        </div>

                        @if ($esOperaciones)
                            <div class="bg-gray-100 dark:bg-gray-800/50 border border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a2 2 0 00-2-2H4a2 2 0 00-2 2v6a2 2 0 002 2h10V7z" />
                                </svg>
                                <p class="text-gray-600 dark:text-gray-300 text-sm font-medium">
                                    Sección no disponible
                                </p>
                                <!--<p class="text-gray-500 dark:text-gray-400 text-xs mt-1">
                                    Solo el personal de Recursos Humanos y Administración puede acceder a la documentación.
                                </p>-->
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                                    'arch_carta_rec_laboral' => 'Carta Recomendación Laboral',
                                    'arch_carta_rec_personal' => 'Carta Recomendación Personal',
                                    'arch_cartilla_militar' => 'Cartilla Militar',
                                    'arch_infonavit' => 'Infonavit',
                                    'arch_fonacot' => 'Fonacot',
                                    'arch_licencia_conducir' => 'Licencia de Conducir',
                                    'arch_carta_no_penales' => 'Carta No Penales',
                                    'arch_acuse_imss' => 'Acuse de IMSS',
                                    'arch_retencion_infonavit' => 'Retención de Infonavit',
                                    'arch_foto' => 'Fotografía',
                                    'visa' => 'Visa',
                                    'pasaporte' => 'Pasaporte'
                                ] as $campo => $label)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $label }}</span>
                                            @if($documentacion?->$campo)
                                                <a href="{{ asset($documentacion->$campo) }}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs font-medium">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Ver
                                                </a>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                                    {{ $campo == 'arch_rfc' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                                    No disponible
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
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
                                            Generar Sol. Vacaciones
                                        </a>

                                        <a href="{{ route('rh.descargarFicha', $user->id) }}"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Descargar Ficha
                                        </a>

                                        @if ((Auth::user()->rol == 'admin' || Auth::user()->solicitudAlta->departamento == 'Recursos Humanos' || in_array(Auth::user()->solicitudAlta->rol, ['AUXILIAR RECURSOS HUMANOS', 'AUXILIAR RH', 'AUX RH', 'Auxiliar RH', 'Auxiliar Recursos Humanos', 'Aux RH']) || in_array(Auth::user()->rol, ['AUXILIAR RECURSOS HUMANOS', 'Auxiliar recursos humanos'])) && $user->estatus == 'Activo')
                                            <button onclick="confirmarBaja({{ $user->id }})"
                                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Dar de Baja
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
                                        Regresar
                                    </a>
                                @elseif(in_array(Auth::user()->rol, ['AUXILIAR NOMINAS', 'Auxiliar Nominas']) || in_array(Auth::user()->solicitudAlta->rol ?? '', ['AUXILIAR NOMINAS', 'Auxiliar Nominas', 'Auxiliar nominas']))
                                    @if($documentacion->arch_rfc == null && $user->estatus == 'Activo')
                                        <button onclick="enviarNotificacion({{ $user->id }})"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            Solicitar Const. de Situación Fiscal
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.verUsuarios') }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Regresar
                                    </a>
                                @else
                                    <a href="{{ route('dashboard') }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Regresar
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
                window.location.href = `/admin/baja_usuario/${userId}?fecha=${fecha}&motivo=${encodeURIComponent(motivo)}`;
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
                window.location.href = `/reingreso/${userId}?fecha=${fecha}`;
            }
        });
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
