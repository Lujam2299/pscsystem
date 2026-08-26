<style>
    [x-cloak] { display: none !important; }
</style>

<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                @if(session('success'))
                    <div class="bg-green-100 border-t-4 border-green-500 rounded-b text-green-900 px-4 py-3 shadow-md" role="alert">
                        <div class="flex">
                            <div>
                                <p class="text-sm">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                    @else
                @endif
                <div class="space-y-4">
                    <p class="text-gray-900 text-2xl dark:text-gray-100 text-2xl">
                        Información del Solicitante
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><strong>Nombre:</strong> {{ $solicitud->nombre ?? 'No disponible' }} {{ $solicitud->apellido_paterno ?? '' }} {{ $solicitud->apellido_materno ?? '' }}</div>
                                <div><strong>CURP:</strong> {{ $solicitud->curp ?? 'No disponible' }}</div>
                                <div><strong>NSS:</strong> {{ $solicitud->nss ?? 'No disponible' }}</div>
                                <div><strong>RFC:</strong> {{ $solicitud->rfc ?? 'No disponible' }}</div>
                                <div><strong>Email:</strong> {{ $solicitud->email ?? 'No disponible' }}</div>
                                <div><strong>Teléfono:</strong> {{ $solicitud->telefono ?? 'No disponible' }}</div>
                                <div><strong>Dirección:</strong>
                                    {{ $solicitud->domicilio_calle ?? '' }} #{{ $solicitud->domicilio_numero ?? '' }},
                                    {{ $solicitud->domicilio_colonia ?? '' }},
                                    {{ $solicitud->domicilio_ciudad ?? '' }},
                                    {{ $solicitud->domicilio_estado ?? '' }}
                                </div>
                                <div><strong>Estado Civil:</strong> {{ $solicitud->estado_civil ?? 'No disponible' }}</div>
                                <div><strong>Puesto Solicitado:</strong> {{ $solicitud->rol ?? 'No disponible' }}</div>
                                <div><strong>Empresa:</strong> {{ $solicitud->empresa ?? 'No disponible' }}</div>
                                <div><strong>Punto:</strong> {{ $solicitud->punto ?? 'No disponible' }}</div>
                                <div><strong>Fecha de Nacimiento:</strong> {{ $solicitud->fecha_nacimiento ?? 'No disponible' }}</div>
                                <div><strong>Fecha de la Solicitud:</strong> {{ optional($solicitud->created_at)->format('d-m-Y H:i') ?? 'No disponible' }}</div>
                            <div><strong>Estado de la Solicitud:</strong>
                                @if($solicitud->status == 'En Proceso')
                                    <span class="inline-flex items-center px-2 py-1 text-sm text-gray-800 bg-yellow-300 rounded-full">
                                        {{ $solicitud->status }}
                                    </span>
                                @elseif($solicitud->status == 'Aceptada')
                                    <span class="inline-flex items-center px-2 py-1 text-sm text-gray-800 bg-green-300 rounded-full">
                                        {{ $solicitud->status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 text-sm text-gry-900 bg-red-300 rounded-full">
                                        {{ $solicitud->status }}
                                    </span>
                                @endif
                            </div>
                            <div><strong>Observaciones:</strong>
                                @if($solicitud->observaciones)
                                    <span class="inline-flex items-center px-2 py-1 text-sm text-gray-900 bg-gray-200 rounded-full">
                                        {{ $solicitud->observaciones }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500">Sin observaciones</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-center justify-start text-center space-y-2">
                            @if ($documentacion && $documentacion->arch_foto)
                                <p class="font-semibold">Foto del solicitante:</p>
                                <img src="{{ asset($documentacion->arch_foto) }}" alt="Foto del usuario" class="w-40 h-40 object-cover rounded shadow">
                                <a href="{{ asset($documentacion->arch_foto) }}" target="_blank" class="text-blue-500 underline text-sm">Ver completa</a>
                            @else
                                <p class="text-sm text-gray-500">No hay foto cargada.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6 mt-4">
                <h3 class="text-lg font-semibold mb-4">Documentación</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                    @foreach([
                        'arch_acta_nacimiento' => 'Acta de Nacimiento',
                        'arch_curp' => 'CURP',
                        'arch_ine' => 'INE',
                        'arch_comprobante_domicilio' => 'Comprobante de Domicilio',
                        'arch_rfc' => 'RFC',
                        'arch_comprobante_estudios' => 'Comprobante de Estudios',
                        'arch_carta_rec_laboral' => 'Carta Recomendación Laboral',
                        'arch_carta_rec_personal' => 'Carta Recomendación Personal',
                        'arch_cartilla_militar' => 'Cartilla Militar',
                        'arch_infonavit' => 'Infonavit',
                        'arch_fonacot' => 'Fonacot',
                        'arch_licencia_conducir' => 'Licencia de Conducir',
                        'arch_carta_no_penales' => 'Carta No Penales',
                        'arch_foto' => 'Fotografía',
                        'visa' => 'Visa',
                        'pasaporte' => 'Pasaporte'
                    ] as $campo => $label)
                        @if($documentacion->$campo)
                            <div class="flex items-center justify-between border rounded p-2 bg-gray-50">
                                <span>{{ $label }}</span>
                                <a href="{{ asset($documentacion->$campo) }}" target="_blank" class="text-blue-600 hover:underline text-sm">
                                    Ver Archivo
                                </a>
                            </div>
                        @else
                            <div class="flex items-center justify-between border rounded p-2 bg-red-50 text-red-700">
                                <span>{{ $label }}</span>
                                <span class="text-xs">No disponible</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="flex flex-wrap justify-center gap-4 mt-4">
                @if($solicitud->status == 'En Proceso')
                    @if((Auth::user()->rol == 'admin' && $solicitud->observaciones == 'Solicitud enviada a Administrador.')||((Auth::user()->rol == 'Recursos Humanos' || Auth::user()->rol == 'AUXILIAR RECURSOS HUMANOS' || Auth::user()->solicitudAlta->rol == 'AUXILIAR RH' || Auth::user()->solicitudAlta->rol == 'Auxiliar Recursos Humanos') && $solicitud->observaciones != 'Solicitud enviada a Administrador.'))
                        <form method="POST" action="{{ route('rh.aceptarSolicitud', $solicitud->id) }}" onsubmit="return confirm('¿Aceptar esta solicitud de alta?')">@csrf
                            <button class="inline-block bg-green-300 text-gray-800 py-2 px-4 rounded-md hover:bg-green-400 transition">Aceptar</button>
                        </form>

                        <form method="POST" action="{{ route('rh.rechazarSolicitud', $solicitud->id) }}" onsubmit="return confirm('¿Rechazar esta solicitud de alta?')">@csrf
                            <button class="inline-block bg-red-300 text-gray-800 py-2 px-4 rounded-md hover:bg-red-400 transition">Rechazar</button>
                        </form>

                        <div x-data="{ open: false }" class="relative">
                            <a href="#" role="button" @click.prevent="open = true"
                                class="inline-block bg-yellow-300 text-gray-800 py-2 px-4 rounded-md hover:bg-yellow-400 transition">
                                Observaciones
                            </a>

                            <div x-show="open" x-cloak
                                    class="absolute z-10 mt-2 w-80 bg-white border border-gray-300 rounded p-4 shadow">
                                <form action="{{ route('rh.observacion_solicitud', $solicitud->id) }}" method="POST">
                                    @csrf

                                    <label for="observacion" class="block text-sm font-medium text-gray-700 mb-1">
                                        Escribe una observación:
                                    </label>
                                    <textarea name="observacion" id="observacion" rows="4" class="w-full border border-gray-300 rounded p-2 mb-3 focus:ring focus:ring-blue-200"required></textarea>

                                    <div class="flex justify-end gap-2">
                                        <button type="submit"
                                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                            Enviar
                                        </button>
                                        <a href="#" @click.prevent="open = false"
                                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                            Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                            @endif
                            <a href="{{ route('dashboard') }}" class="inline-block bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400 transition">
                                Regresar
                            </a>
                        </div>
                @else
                <a href="{{ route('dashboard') }}" class="inline-block bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400 transition">
                    Regresar
                </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
