@php
    use App\Models\User;
    use App\Models\SolicitudAlta;
    use Carbon\Carbon;

    $conteoAltas = SolicitudAlta::where('status', 'Aceptada')
        ->whereDate('fecha_ingreso', '>=', Carbon::today('America/Mexico_City')->subDays(7))
        ->whereNull('sd')
        ->whereNull('sdi')
        ->whereHas('documentacion', function ($q) {
            $q->whereNull('arch_acuse_imss');
        })
        ->count();

    $cards = [
        [
            'titulo' => 'Nuevas Altas',
            'ruta' => route('aux.nuevasAltas'),
            'icono' => 'circle-plus',
            'color' => 'bg-green-100 dark:bg-green-700',
            'notificaciones' => $conteoAltas,
        ],
        [
            'titulo' => 'Actualización de Información',
            'ruta' => route('aux.usuariosList'),
            'icono' => 'folder',
            'color' => 'bg-yellow-100 dark:bg-yellow-700',
        ],
        [
            'titulo' => 'Acuses de Bajas',
            'ruta' => route('aux.acusesbajas'),
            'icono' => 'trash',
            'color' => 'bg-red-100 dark:bg-red-700',

        ],
        [
            'titulo' => 'Confrontas IMSS/INFONAVIT',
            'ruta' => route('aux.confrontas'),
            'icono' => 'folder-open',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Cédulas',
            'ruta' => route('aux.cedulasForm'),
            'icono' => 'file-text',
            'color' => 'bg-yellow-100 dark:bg-yellow-700',
        ],
        [
            'titulo' => 'Sipare',
            'ruta' => route('aux.sipareForm'),
            'icono' => 'folder-open',
            'color' => 'bg-pink-100 dark:bg-pink-700',
        ],
        [
            'titulo' => 'Gráficos',
            'ruta' => route('auxadmin.index'),
            'icono' => 'chart-bar',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Riesgos de Trabajo',
            'ruta' => route('aux.riesgosTrabajo'),
            'icono' => 'alert-triangle',
            'color' => 'bg-yellow-100 dark:bg-yellow-700',
        ],
        [
            'titulo' => 'Incapacidades',
            'ruta' => route('aux.incapacidadesList'),
            'icono' => 'bandage',
            'color' => 'bg-red-100 dark:bg-red-700',
        ],
        [
            'titulo' => 'Solicitar Vacaciones',
            'ruta' => route('user.solicitarVacacionesForm'),
            'icono' => 'confetti',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Ficha Técnica',
            'ruta' => route('user.verFicha', auth()->user()->id),
            'icono' => 'file-description',
            'color' => 'bg-yellow-100 dark:bg-yellow-700',
        ],
        [
            'titulo' => 'Historial de Incapacidades',
            'ruta' => route('aux.historialIncapacidades'),
            'icono' => 'calendar',
            'color' => 'bg-red-100 dark:bg-red-700',
        ],
        [
            'titulo' => 'Mi Historial de Vacaciones',
            'ruta' => route('user.historialVacaciones'),
            'icono' => 'calendar',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Historial de Registros Sipare',
            'ruta' => route('aux.historialSipare'),
            'icono' => 'calendar',
            'color' => 'bg-yellow-100 dark:bg-yellow-700',
            'disabled' => true,
        ],
        [
            'titulo' => 'Historial de Riesgos de Trabajo',
            'ruta' => route('aux.historialRiesgosTrabajo'),
            'icono' => 'calendar',
            'color' => 'bg-red-100 dark:bg-red-700',
        ],
        [
            'titulo' => 'Historial de Acuses de Alta',
            'ruta' => route('aux.historialAcusesAlta'),
            'icono' => 'calendar',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Historial de Cedulas',
            'ruta' => route('aux.historialCedulas'),
            'icono' => 'calendar',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Historial de Acuses de Bajas',
            'ruta' => route('aux.historialAcuseBajas'),
            'icono' => 'calendar',
            'color' => 'bg-red-100 dark:bg-red-700',
        ],

        [
            'titulo' => 'Buzón de Quejas y Sugerencias',
            'ruta' => route('user.buzon'),
            'icono' => 'message',
            'color' => 'bg-purple-100 dark:bg-purple-700',
        ],


    ];
@endphp
<div class="col-span-full">
    @if(session('success'))
                <div class="bg-green-100 border-t-4 border-green-500 rounded-b text-green-900 px-4 py-3 shadow-md" role="alert">
                    <div class="flex">
                        <div>
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @elseif (session('error'))
                    <div class="bg-red-100 border-t-4 border-red-500 rounded-b text-red-900 px-4 py-3 shadow-md" role="alert">
                    <div class="flex">
                        <div>
                            <p class="text-sm">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($cards as $card)
            @php
                $isDisabled = $card['disabled'] ?? false;
            @endphp

            @if($isDisabled)
                <div class="transition-transform transform opacity-50 cursor-not-allowed" style="pointer-events: none;">
                    <div class="p-4 rounded-xl shadow-md {{ $card['color'] }} h-full flex flex-col justify-between">
                        <div class="flex items-center space-x-3">
                            @if (!empty($card['notificaciones']) && $card['notificaciones'] > 0)
                                <span class="absolute top-2 right-2 bg-red-600 text-white text-xs rounded-full px-2 py-0.5">
                                    {{ $card['notificaciones'] }}
                                </span>
                            @endif
                            <div class="flex items-center justify-center mb-1 rounded-full shadow w-14 h-14 bg-white/80">
                                <i class="ti ti-{{ $card['icono'] }} text-3xl {{
                                    Str::contains($card['color'], 'blue') ? 'text-blue-700' :
                                    (Str::contains($card['color'], 'yellow') ? 'text-yellow-700' :
                                    (Str::contains($card['color'], 'red') ? 'text-red-700' :
                                    (Str::contains($card['color'], 'green') ? 'text-green-700' :
                                    (Str::contains($card['color'], 'purple') ? 'text-purple-700' :
                                    (Str::contains($card['color'], 'pink') ? 'text-pink-700' :
                                    (Str::contains($card['color'], 'gray') ? 'text-gray-700' : 'text-gray-800'))))))
                                }}"></i>
                            </div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                                {{ $card['titulo'] }}
                            </h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Opción no disponible</p>
                    </div>
                </div>
            @else
                <a href="{{ $card['ruta'] }}" class="transition-transform transform hover:scale-105">
                    <div class="p-4 rounded-xl shadow-md {{ $card['color'] }} hover:shadow-lg h-full flex flex-col justify-between">
                        <div class="flex items-center space-x-3">
                            @if (!empty($card['notificaciones']) && $card['notificaciones'] > 0)
                                <span class="absolute top-2 right-2 bg-red-600 text-white text-xs rounded-full px-2 py-0.5">
                                    {{ $card['notificaciones'] }}
                                </span>
                            @endif
                            <div class="flex items-center justify-center mb-1 rounded-full shadow w-14 h-14 bg-white/80">
                                <i class="ti ti-{{ $card['icono'] }} text-3xl {{
                                    Str::contains($card['color'], 'blue') ? 'text-blue-700' :
                                    (Str::contains($card['color'], 'yellow') ? 'text-yellow-700' :
                                    (Str::contains($card['color'], 'red') ? 'text-red-700' :
                                    (Str::contains($card['color'], 'green') ? 'text-green-700' :
                                    (Str::contains($card['color'], 'purple') ? 'text-purple-700' :
                                    (Str::contains($card['color'], 'pink') ? 'text-pink-700' :
                                    (Str::contains($card['color'], 'gray') ? 'text-gray-700' : 'text-gray-800'))))))
                                }}"></i>
                            </div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                                {{ $card['titulo'] }}
                            </h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Haz clic para ver más detalles</p>
                    </div>
                </a>
            @endif
        @endforeach
    </div>
</div>
