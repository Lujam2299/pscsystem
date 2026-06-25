@php
    use App\Models\User;
    use App\Models\Asistencia;
    use App\Models\SolicitudVacaciones;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;

    $user = User::find(Auth::id());
    $totalAsistenciasHoy = 0;
    $conteoSupervisores = User::where('rol', 'Supervisor')->count();

    if (Auth::user()->rol == 'admin') {
        $supervisores = User::where('rol', 'Supervisor')->get();
        foreach ($supervisores as $supervisor) {
            $totalAsistenciasHoy += Asistencia::where('user_id', $supervisor->id)
                ->whereDate('fecha', Carbon::today())
                ->count();
        }
    }

    $tieneAsistenciaHoy = Asistencia::where('user_id', $user->id)
        ->whereDate('fecha', Carbon::today())
        ->exists();

    $vacacionesAdmin = SolicitudVacaciones::where('estatus', 'En Proceso')
        ->where('tipo', 'Disfrutadas')
        ->count();

    $vacacionesSup = SolicitudVacaciones::where('supervisor_id', $user->id)
        ->where('estatus', 'En Proceso')
        ->where('tipo', 'Disfrutadas')
        ->count();

    $vacaciones = Auth::user()->rol == 'admin' ? $vacacionesAdmin : $vacacionesSup;
    $asistencia = Auth::user()->rol == 'admin'
        ? max($conteoSupervisores - $totalAsistenciasHoy, 0)
        : ($tieneAsistenciaHoy ? 0 : 1);

    $cards = [
        [
            'titulo' => 'Listas de Asistencia',
            'ruta' => route('sup.listaAsistencia'),
            'icono' => 'clipboard-check',
            'theme' => 'blue',
            'notificaciones' => $asistencia,
            'desc' => 'Registro diario por punto',
        ],
        [
            'titulo' => 'Historial de Asistencias',
            'ruta' => route('sup.verAsistencias', Auth::id()),
            'icono' => 'calendar-stats',
            'theme' => 'blue',
            'variant' => 'soft',
            'desc' => 'Consulta de registros anteriores',
        ],
        [
            'titulo' => 'Solicitudes de Vacaciones',
            'ruta' => route('sup.solicitudesVacaciones'),
            'icono' => 'beach',
            'theme' => 'teal',
            'notificaciones' => $vacaciones,
            'desc' => 'Autorización de descansos',
        ],
        [
            'titulo' => 'Solicitar Vacaciones',
            'ruta' => route('user.solicitarVacacionesForm'),
            'icono' => 'plane-inflight',
            'theme' => 'teal',
            'desc' => 'Solicitud personal',
        ],
        [
            'titulo' => 'Alta de Usuarios',
            'ruta' => route('sup.nuevoUsuarioForm'),
            'icono' => 'user-plus',
            'theme' => 'indigo',
            'desc' => 'Registro de nuevos elementos',
        ],
        [
            'titulo' => 'Historial de Altas',
            'ruta' => route('sup.historial'),
            'icono' => 'archive',
            'theme' => 'indigo',
            'variant' => 'soft',
            'desc' => 'Seguimiento de solicitudes',
        ],
        [
            'titulo' => 'Solicitar Baja de Elemento',
            'ruta' => route('sup.solicitarBajaForm'),
            'icono' => 'user-minus',
            'theme' => 'rose',
            'desc' => 'Captura de bajas',
        ],
        [
            'titulo' => 'Historial de Bajas',
            'ruta' => route('sup.historialBajas'),
            'icono' => 'book',
            'theme' => 'rose',
            'variant' => 'soft',
            'desc' => 'Consulta de bajas previas',
        ],
        [
            'titulo' => 'Tiempos Extras',
            'ruta' => route('sup.tiemposExtras'),
            'icono' => 'clock-hour-2',
            'theme' => 'amber',
            'desc' => 'Registro y autorización',
        ],
        [
            'titulo' => 'Historial de Tiempos Extras',
            'ruta' => route('sup.historialTiemposExtras'),
            'icono' => 'history',
            'theme' => 'amber',
            'variant' => 'soft',
            'desc' => 'Tiempos extras y coberturas',
        ],
        [
            'titulo' => 'Gestión de Usuarios',
            'ruta' => route('sup.gestionUsuarios'),
            'icono' => 'users-group',
            'theme' => 'slate',
            'desc' => 'Personal asignado',
        ],
        [
            'titulo' => 'Mensajes',
            'ruta' => route('mensajes.index'),
            'icono' => 'message',
            'theme' => 'purple',
            'desc' => 'Comunicación interna',
        ],
        [
            'titulo' => 'Mi Historial de Vacaciones',
            'ruta' => route('user.historialVacaciones'),
            'icono' => 'calendar-user',
            'theme' => 'teal',
            'variant' => 'soft',
            'desc' => 'Historial personal',
        ],
        [
            'titulo' => 'Buzón de Quejas y Sugerencias',
            'ruta' => route('user.buzon'),
            'icono' => 'message-report',
            'theme' => 'gray',
            'desc' => 'Comentarios y reportes',
        ],
    ];

    $themeClasses = [
        'blue' => ['icon' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-300', 'hover' => 'hover:border-blue-300 dark:hover:border-blue-700'],
        'teal' => ['icon' => 'bg-teal-100 text-teal-600 dark:bg-teal-900/50 dark:text-teal-300', 'hover' => 'hover:border-teal-300 dark:hover:border-teal-700'],
        'indigo' => ['icon' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300', 'hover' => 'hover:border-indigo-300 dark:hover:border-indigo-700'],
        'rose' => ['icon' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-300', 'hover' => 'hover:border-rose-300 dark:hover:border-rose-700'],
        'amber' => ['icon' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300', 'hover' => 'hover:border-amber-300 dark:hover:border-amber-700'],
        'slate' => ['icon' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300', 'hover' => 'hover:border-slate-300 dark:hover:border-slate-600'],
        'purple' => ['icon' => 'bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-300', 'hover' => 'hover:border-purple-300 dark:hover:border-purple-700'],
        'gray' => ['icon' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', 'hover' => 'hover:border-gray-300 dark:hover:border-gray-600'],
    ];
@endphp

<div class="col-span-full">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Módulo de Supervisores</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Asistencias, vacaciones, tiempos extras y personal asignado</p>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($cards as $card)
            @php
                $isDisabled = $card['disabled'] ?? false;
                $theme = $themeClasses[$card['theme'] ?? 'gray'] ?? $themeClasses['gray'];
            @endphp

            <div class="group relative {{ $isDisabled ? 'cursor-not-allowed opacity-60 grayscale' : '' }}">
                @if(!$isDisabled)
                    <a href="{{ $card['ruta'] }}" class="block h-full">
                @endif

                <div class="flex h-full flex-col justify-between rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 dark:border-gray-700 dark:bg-gray-800 {{ !$isDisabled ? $theme['hover'] . ' hover:shadow-md' : '' }}">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl shadow-sm transition-transform duration-300 group-hover:scale-110 {{ $theme['icon'] }}">
                            <i class="ti ti-{{ $card['icono'] }} text-2xl"></i>
                        </div>

                        @if (!empty($card['notificaciones']) && $card['notificaciones'] > 0)
                            <span class="inline-flex items-center rounded-full border border-red-200 bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:border-red-800 dark:bg-red-900 dark:text-red-200">
                                {{ $card['notificaciones'] }}
                            </span>
                        @endif
                    </div>

                    <div>
                        <h3 class="mb-1 text-lg font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                            {{ $card['titulo'] }}
                        </h3>
                        <p class="line-clamp-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ $card['desc'] ?? 'Acceder al módulo' }}
                        </p>
                    </div>

                    <div class="mt-4 flex items-center border-t border-gray-100 pt-4 text-xs font-medium text-gray-400 transition-colors group-hover:text-gray-600 dark:border-gray-700 dark:group-hover:text-gray-300">
                        <span>Ver detalles</span>
                        <i class="ti ti-arrow-right ml-1 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </div>

                @if(!$isDisabled)
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>
