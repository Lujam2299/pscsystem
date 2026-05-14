@php
    use App\Models\User;
    use Illuminate\Support\Facades\Auth;
    use App\Models\SolicitudAlta;
    use App\Models\SolicitudBajas;
    use App\Models\SolicitudVacaciones;
    use App\Models\Asistencia;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;

    // --- LÓGICA ORIGINAL INTACTA ---
    $vacacionesAdmin = SolicitudVacaciones::where('estatus', 'En Proceso')
        ->where('observaciones', '!=', 'Solicitud aceptada, falta subir archivo de solicitud.')
        ->whereHas('user', function ($query) {
            $query->where('empresa', 'Montana');
        })
        ->count();
    $conteoBajasJuridico = SolicitudBajas::where('estatus', 'En Proceso')
        ->where('por', '!=', 'Renuncia')
        ->where('fecha_baja', '>=', Carbon::now()->subDays(7))
        ->count();

    $activos = User::where('estatus', 'Activo')->selectRaw('COUNT(DISTINCT UPPER(name)) as total')->first()->total;
    $activosMesActual = User::where('estatus', 'Activo')
        ->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
        ->count();
    $activosMesPasado = $activos - $activosMesActual;

    $inicioMesActual = Carbon::now()->startOfMonth();
    $inicioMesPasado = Carbon::now()->subMonth()->startOfMonth();
    $finMesPasado = Carbon::now()->subMonth()->endOfMonth();

    $conteoAltasAdmin = SolicitudAlta::where('status', 'Aceptada')
        ->whereDate('fecha_ingreso', '>=', $inicioMesActual)
        ->count();

    $altasMesPasado = SolicitudAlta::where('status', 'Aceptada')
        ->whereBetween('fecha_ingreso', [$inicioMesPasado, $finMesPasado])
        ->count();

    $conteoBajasAdmin = SolicitudBajas::where('estatus', 'Aceptada')
        ->whereDate('fecha_baja', '>=', $inicioMesActual)
        ->count();

    $bajasMesPasado = SolicitudBajas::where('estatus', 'Aceptada')
        ->whereBetween('fecha_baja', [$inicioMesPasado, $finMesPasado])
        ->count();

    function calcularVariacion($actual, $anterior)
    {
        if ($anterior == 0) {
            return $actual > 0 ? 100 : 0;
        }
        return round((($actual - $anterior) / $anterior) * 100);
    }

    $variacionActivos = calcularVariacion($activos, $activosMesPasado);
    $variacionAltas = calcularVariacion($conteoAltasAdmin, $altasMesPasado);
    $variacionBajas = calcularVariacion($conteoBajasAdmin, $bajasMesPasado);

    $user = Auth::user();
    $asistenciasHoy = 0;
    $solicitudesAdmin = SolicitudAlta::where('status', 'Aceptada')
        ->whereDate('updated_at', Carbon::today('America/Mexico_City'))
        ->count();

    $supervisores = User::where('rol', 'Supervisor')->where('estatus', 'Activo')->get();
    $supervisoresCount = $supervisores->count();

    $rhSolicitudesAltas = SolicitudAlta::where('status', 'En Proceso')
        ->where('observaciones', '!=', 'Solicitud enviada a Administrador.')
        ->count();
    $rhSolicitudesBajas = SolicitudBajas::where('estatus', 'En Proceso')->where('por', 'Renuncia')->count();
    $rhnotificaciones = $rhSolicitudesAltas + $rhSolicitudesBajas;

    $solicitudesVacaciones = SolicitudVacaciones::where('estatus', 'En Proceso')->count();
    $totalAsistenciasHoy = 0;

    foreach ($supervisores as $supervisor) {
        $asistenciasHoy = Asistencia::where('user_id', $supervisor->id)->whereDate('fecha', Carbon::today())->count();
        $totalAsistenciasHoy += $asistenciasHoy;
    }
    $asistenciasFaltantes = $supervisoresCount - $totalAsistenciasHoy;
    $supNotificaciones = $asistenciasFaltantes + $solicitudesVacaciones;

    $conteoAltasAux = SolicitudAlta::where('status', 'Aceptada')
        ->whereDate('fecha_ingreso', '>=', Carbon::today('America/Mexico_City')->subDays(5))
        ->whereHas('documentacion', function ($q) {
            $q->whereNull('arch_acuse_imss');
        })
        ->count();

    $conteoAltasNominas = SolicitudAlta::where('status', 'Aceptada')
        ->whereDate('fecha_ingreso', '>=', Carbon::today('America/Mexico_City')->subDays(5))
        ->count();
    $conteoBajasNominas = SolicitudBajas::where('estatus', 'Aceptada')
        ->whereDate('fecha_baja', '>=', Carbon::today('America/Mexico_City')->subDays(5))
        ->count();
    $conteoNominas = $conteoAltasNominas + $conteoBajasNominas;

    $cards = array_filter([
        Auth::user()->email == 'gino@spyt.com.mx'
            ? [
                'titulo' => 'Nuevas Altas',
                'ruta' => route('admi.verSolicitudesAltas'),
                'icono' => 'trending-up',
                'color' => 'bg-green-200 dark:bg-green-700',
                'notificaciones' => $solicitudesAdmin,
            ]
            : null,
        [
            'titulo' => 'Mensajes',
            'ruta' => route('mensajes.index'),
            'icono' => 'message',
            'color' => 'bg-purple-300 dark:bg-purple-700',
        ],
        [
            'titulo' => 'Mapa',
            'ruta' => route('monitoreo.mapa'),
            'icono' => 'map',
            'color' => 'bg-green-300 dark:bg-green-700',
        ],
        [
            'titulo' => 'Nóminas',
            'ruta' => route('admin.nominasDashboard'),
            'icono' => 'currency-dollar',
            'color' => 'bg-yellow-200 dark:bg-yellow-700',
            'notificaciones' => $conteoNominas,
        ],
        [
            'titulo' => 'IMSS',
            'ruta' => route('admin.imssDashboard'),
            'icono' => 'pill',
            'color' => 'bg-blue-200 dark:bg-blue-700',
            'notificaciones' => $conteoAltasAux,
        ],
        [
            'titulo' => 'Operaciones',
            'ruta' => route('admin.operacionesDashboard'),
            'icono' => 'activity',
            'color' => 'bg-red-300 dark:bg-red-700',
        ],
        [
            'titulo' => 'Contabilidad',
            'ruta' => route('admin.contDashboard'),
            'icono' => 'credit-card',
            'color' => 'bg-indigo-300 dark:bg-indigo-700',
        ],
        /*[
            'titulo' => 'Jurídico',
            'ruta' => route('admin.juridicoDashboard'),
            'icono' => 'scale',
            'color' => 'bg-red-300 dark:bg-red-700',
            'notificacions' => $conteoBajasJuridico,
        ],*/
        [
            'titulo' => 'Custodios',
            'ruta' => route('admin.custodiosDashboard'),
            'icono' => 'user-shield',
            'color' => 'bg-blue-300 dark:bg-blue-700',
        ],
        [
            'titulo' => 'RRHH',
            'ruta' => route('admin.rhDashboard'),
            'icono' => 'users-group',
            'color' => 'bg-pink-300 dark:bg-pink-700',
            'notificaciones' => $rhnotificaciones,
        ],
        [
            'titulo' => 'Monitoreo',
            'ruta' => route('admin.monitoreoDashboard'),
            'icono' => 'trending-up',
            'color' => 'bg-indigo-300 dark:bg-indigo-700',
        ],
        /*[
            'titulo' => 'Supervisores',
            'ruta' => route('admin.verTableroSupervisores'),
            'icono' => 'users',
            'color' => 'bg-green-300 dark:bg-green-700',
            'notificaciones' => $supNotificaciones,
        ],*/
        [
            'titulo' => 'Vacaciones',
            'ruta' => route('admin.solicitudesVacaciones'),
            'icono' => 'tent',
            'color' => 'bg-yellow-300 dark:bg-yellow-700',
            'notificaciones' => $vacacionesAdmin,
        ],
        [
            'titulo' => 'Gestión de Usuarios',
            'ruta' => route('admin.verUsuarios'),
            'icono' => 'user-code',
            'color' => 'bg-indigo-300 dark:bg-indigo-700',
        ],
        [
            'titulo' => 'Buzón de Quejas y Sugerencias',
            'ruta' => route('admin.verBuzon'),
            'icono' => 'message',
            'color' => 'bg-purple-300 dark:bg-purple-700',
        ],
    ]);
@endphp

<div class="flex h-screen bg-gray-50 dark:bg-gray-900 font-sans overflow-hidden">

    {{-- SIDEBAR MEJORADO --}}
    <aside class="w-72 flex-shrink-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col transition-all duration-300 z-20 shadow-xl">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white tracking-tight">
                Panel <span class="text-blue-600">Admin</span>
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wider">SGI v1.1</p>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 custom-scrollbar">
            @foreach ($cards as $card)
                @php
                    $isDisabled = $card['disabled'] ?? false;
                    // Determinar si está activo de forma segura
                    $isActive = isset($card['ruta']) && request()->fullUrlIs('*'.parse_url($card['ruta'], PHP_URL_PATH).'*');

                    // Mapeo de colores para iconos y badges basado en la clase original
                    $iconColorClass = match(true) {
                        Str::contains($card['color'], 'blue') => 'text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400',
                        Str::contains($card['color'], 'yellow') => 'text-yellow-600 bg-yellow-50 dark:bg-yellow-900/30 dark:text-yellow-400',
                        Str::contains($card['color'], 'indigo') => 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 dark:text-indigo-400',
                        Str::contains($card['color'], 'orange') => 'text-orange-600 bg-orange-50 dark:bg-orange-900/30 dark:text-orange-400',
                        Str::contains($card['color'], 'red') => 'text-red-600 bg-red-50 dark:bg-red-900/30 dark:text-red-400',
                        Str::contains($card['color'], 'green') => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400',
                        Str::contains($card['color'], 'pink') => 'text-pink-600 bg-pink-50 dark:bg-pink-900/30 dark:text-pink-400',
                        Str::contains($card['color'], 'purple') => 'text-purple-600 bg-purple-50 dark:bg-purple-900/30 dark:text-purple-400',
                        default => 'text-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-400',
                    };
                @endphp

                @if ($isDisabled)
                    <div class="group flex items-center justify-between px-3 py-2.5 text-sm font-medium text-gray-400 rounded-lg cursor-not-allowed opacity-60">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg {{ $iconColorClass }} flex items-center justify-center">
                                <i class="ti ti-{{ $card['icono'] }} text-lg"></i>
                            </div>
                            <span>{{ $card['titulo'] }}</span>
                        </div>
                    </div>

                @elseif(isset($card['form']) && $card['form'])
                    <form action="{{ $card['action'] }}" method="POST"
                          class="group flex items-center justify-between px-3 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors relative"
                          onsubmit="return confirm('{{ $card['confirm'] ?? '¿Estás seguro?' }}')">
                        @csrf
                        <div class="flex items-center gap-3 w-full">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg {{ $iconColorClass }} flex items-center justify-center transition-transform group-hover:scale-110">
                                <i class="ti ti-{{ $card['icono'] }} text-lg"></i>
                            </div>
                            <span class="truncate">{{ $card['titulo'] }}</span>
                        </div>
                        @if (isset($card['notificaciones']) && $card['notificaciones'] > 0)
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1 bg-red-500 rounded-full">
                                {{ $card['notificaciones'] }}
                            </span>
                        @endif
                        <button type="submit" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"></button>
                    </form>

                @else
                    <a href="{{ $card['ruta'] ?? '#' }}"
                       @if (isset($card['onclick'])) onclick="{{ $card['onclick'] }}; return false;" @endif
                       @if (in_array($card['titulo'], ['RRHH', 'Nóminas', 'IMSS']))
                           @click.prevent="$dispatch('cambiar-menu', { menu: '{{ strtolower(str_replace(' ', '_', $card['titulo'])) }}' })"
                       @endif
                       id="{{ Str::slug($card['titulo']) }}"
                       class="group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200
                              {{ $isActive
                                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800'
                                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white' }}">

                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg {{ $isActive ? 'bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300' : $iconColorClass }} flex items-center justify-center transition-transform group-hover:scale-110">
                                <i class="ti ti-{{ $card['icono'] }} text-lg"></i>
                            </div>
                            <span class="truncate">{{ $card['titulo'] }}</span>
                        </div>

                        @if (isset($card['notificaciones']) && $card['notificaciones'] > 0)
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full shadow-sm ring-2 ring-white dark:ring-gray-800">
                                {{ $card['notificaciones'] }}
                            </span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ Auth::user()->name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ Auth::user()->rol ?? 'Administrador' }}
                    </p>
                </div>
            </div>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-50 dark:bg-gray-900">

        {{-- TOP SECTION: KPI CARDS --}}
        <div class="flex-1 overflow-y-auto p-6 scroll-smooth">

            {{-- Header Mobile/Tablet Title --}}
            <div class="mb-6 md:hidden">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard General</h1>
            </div>

            <div x-data="{ slide: 1 }" class="relative max-w-7xl mx-auto">

                {{-- Slider Container --}}
                <div class="overflow-hidden rounded-2xl">
                    <div :class="`flex transition-transform duration-500 ease-out transform ${slide === 1 ? 'translate-x-0' : '-translate-x-full md:-translate-x-1/2'}`"
                         style="width: 200%;">

                        {{-- SLIDE 1: KPIs Principales --}}
                        <div class="w-1/2 px-2 md:px-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                {{-- Card: Activos --}}
                                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                                    <div class="relative z-10">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl text-blue-600 dark:text-blue-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5" />
                                                    <circle cx="12" cy="7" r="4" />
                                                </svg>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Personal</span>
                                        </div>
                                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $activos }}</h3>
                                        <div class="flex items-center text-sm {{ $variacionActivos >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                            @if ($variacionActivos > 0) <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                            @elseif($variacionActivos < 0) <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5 5 5-5H5z"/></svg>
                                            @else <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M4 9h12v2H4z"/></svg>
                                            @endif
                                            <span class="font-medium">{{ abs($variacionActivos) }}%</span>
                                            <span class="text-gray-500 dark:text-gray-400 ml-1">vs mes anterior</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card: Altas --}}
                                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                                    <div class="relative z-10">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl text-emerald-600 dark:text-emerald-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                </svg>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Altas Mes</span>
                                        </div>
                                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $conteoAltasAdmin }}</h3>
                                        <div class="flex items-center text-sm {{ $variacionAltas >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                            @if ($variacionAltas > 0) <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                            @elseif($variacionAltas < 0) <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5 5 5-5H5z"/></svg>
                                            @else <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M4 9h12v2H4z"/></svg>
                                            @endif
                                            <span class="font-medium">{{ abs($variacionAltas) }}%</span>
                                            <span class="text-gray-500 dark:text-gray-400 ml-1">vs mes anterior</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card: Bajas --}}
                                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-red-50 dark:bg-red-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                                    <div class="relative z-10">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-xl text-red-600 dark:text-red-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12l-4-4m0 0l-4 4m4-4v12" transform="rotate(180 12 12)" />
                                                    <!-- Icono simplificado de salida -->
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                </svg>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Bajas Mes</span>
                                        </div>
                                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $conteoBajasAdmin }}</h3>
                                        <div class="flex items-center text-sm {{ $variacionBajas <= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                            @if ($variacionBajas > 0) <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                            @elseif($variacionBajas < 0) <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5 5 5-5H5z"/></svg>
                                            @else <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M4 9h12v2H4z"/></svg>
                                            @endif
                                            <span class="font-medium">{{ abs($variacionBajas) }}%</span>
                                            <span class="text-gray-500 dark:text-gray-400 ml-1">vs mes anterior</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Alertas de Sesión (Importación) --}}
                            @if (session('usuarios_no_en_excel'))
                                <div class="mt-6 bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Discrepancias en Importación</h3>
                                            <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                                <p>Usuarios activos en sistema no encontrados en el Excel:</p>
                                                <ul class="list-disc list-inside mt-1 space-y-1 max-h-32 overflow-y-auto">
                                                    @foreach (session('usuarios_no_en_excel') as $usuario)
                                                        <li>{{ $usuario->name }} <span class="text-xs opacity-75">(ID: {{ $usuario->id }})</span></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- SLIDE 2: Livewire Components --}}
                        <div class="w-1/2 px-2 md:px-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-full">
                                @livewire('nominamensual')
                                @livewire('finiquitomensual')
                                @livewire('destajosmensuales')
                            </div>

                            @if (session('resumen'))
                                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-100 dark:border-blue-800">
                                        <h4 class="font-bold text-blue-800 dark:text-blue-300 mb-1">Coincidencias</h4>
                                        <span class="text-xs text-blue-600 dark:text-blue-400">{{ session('resumen.en_excel_y_bd')->count() }} registros</span>
                                    </div>
                                    <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg border border-green-100 dark:border-green-800">
                                        <h4 class="font-bold text-green-800 dark:text-green-300 mb-1">Nuevos (Excel)</h4>
                                        <span class="text-xs text-green-600 dark:text-green-400">{{ session('resumen.en_excel_no_bd')->count() }} registros</span>
                                    </div>
                                    <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg border border-red-100 dark:border-red-800">
                                        <h4 class="font-bold text-red-800 dark:text-red-300 mb-1">Faltantes (Excel)</h4>
                                        <span class="text-xs text-red-600 dark:text-red-400">{{ session('resumen.en_bd_no_excel')->count() }} registros</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Slider Controls --}}
                <div class="flex justify-center items-center mt-6 gap-4">
                    <button type="button" @click="slide = 1"
                            class="p-2 rounded-full bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :class="{ 'opacity-50 cursor-not-allowed': slide === 1 }">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="flex space-x-2">
                        <div class="h-2 w-2 rounded-full transition-colors duration-300" :class="slide === 1 ? 'bg-blue-600 w-6' : 'bg-gray-300 dark:bg-gray-600'"></div>
                        <div class="h-2 w-2 rounded-full transition-colors duration-300" :class="slide === 2 ? 'bg-blue-600 w-6' : 'bg-gray-300 dark:bg-gray-600'"></div>
                    </div>

                    <button type="button" @click="slide = 2"
                            class="p-2 rounded-full bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :class="{ 'opacity-50 cursor-not-allowed': slide === 2 }">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

            </div>

            {{-- BOTTOM SECTION: Carousel Charts --}}
            <div class="max-w-7xl mx-auto mt-8 pb-8">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-1 relative">
                    <div class="overflow-hidden rounded-xl" id="carouselContainer">
                        <div id="carouselSlides" class="flex transition-transform duration-500 ease-in-out">
                            <div class="w-full flex-shrink-0 p-4">
                                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 min-h-[300px]">
                                    @livewire('nominastotales')
                                </div>
                            </div>
                            <div class="w-full flex-shrink-0 p-4">
                                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 min-h-[300px]">
                                    @livewire('destajo-mensual')
                                </div>
                            </div>
                            <div class="w-full flex-shrink-0 p-4">
                                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 min-h-[300px]">
                                    @livewire('graficasfiniquitos')
                                </div>
                            </div>
                            <div class="w-full flex-shrink-0 p-4">
                                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 min-h-[300px]">
                                    @livewire('graficas-altas')
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Carousel Arrows --}}
                    <button onclick="prevSlide()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1/2 bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-200 p-2 rounded-full shadow-lg border border-gray-100 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button onclick="nextSlide()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-200 p-2 rounded-full shadow-lg border border-gray-100 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                {{-- Carousel Indicators --}}
                <div class="flex justify-center mt-4 space-x-2">
                    @for ($i = 0; $i < 4; $i++)
                        <button onclick="goToSlide({{ $i }})"
                                class="indicator-dot w-2.5 h-2.5 rounded-full bg-gray-300 dark:bg-gray-600 hover:bg-blue-400 transition-all duration-300 focus:outline-none"></button>
                    @endfor
                </div>
            </div>

        </div>
    </main>
</div>

@push('scripts')
    <script>
        function actualizarDestajos() {
            if (confirm('¿Estás seguro de actualizar todos los destajos? Este proceso puede tardar varios minutos.')) {
                const boton = document.getElementById('registrar-datos');
                if (boton) {
                    const span = boton.querySelector('span.font-medium');
                    if (span) {
                        span.textContent = 'Procesando...';
                    }
                    boton.classList.add('opacity-75', 'cursor-wait');
                    boton.onclick = null;
                }
                procesarLotes(0);
            }
        }

        function procesarLotes(offset) {
            fetch('{{ route('updateDestajos') }}?offset=' + offset, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data.message);
                    if (data.continuar) {
                        setTimeout(() => procesarLotes(data.siguiente_offset), 1000);
                    } else {
                        alert(`Proceso completado. ${data.actualizados} registros actualizados.`);
                        if (typeof Livewire !== 'undefined') {
                            Livewire.dispatch('refreshTable');
                        }
                        const boton = document.getElementById('registrar-datos');
                        if (boton) {
                            const span = boton.querySelector('span.font-medium');
                            if (span) {
                                span.textContent = 'Registrar Datos';
                            }
                            boton.classList.remove('opacity-75', 'cursor-wait');
                            boton.onclick = actualizarDestajos;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error durante el proceso.');
                    const boton = document.getElementById('registrar-datos');
                    if (boton) {
                        const span = boton.querySelector('span.font-medium');
                        if (span) {
                            span.textContent = 'Registrar Datos';
                        }
                        boton.classList.remove('opacity-75', 'cursor-wait');
                        boton.onclick = actualizarDestajos;
                    }
                });
        }
    </script>
    <script>
        let currentSlide = 0;
        const totalSlides = 4;
        const carousel = document.getElementById('carouselSlides');
        const indicators = document.querySelectorAll('.indicator-dot');

        function updateCarousel() {
            if(carousel) {
                carousel.style.transform = `translateX(-${currentSlide * 100}%)`;
            }

            indicators.forEach((dot, index) => {
                if (index === currentSlide) {
                    dot.classList.remove('bg-gray-300', 'dark:bg-gray-600', 'w-2.5', 'h-2.5');
                    dot.classList.add('bg-blue-600', 'w-6', 'h-2.5');
                } else {
                    dot.classList.remove('bg-blue-600', 'w-6', 'h-2.5');
                    dot.classList.add('bg-gray-300', 'dark:bg-gray-600', 'w-2.5', 'h-2.5');
                }
            });

            if (window.nominaChart) {
                setTimeout(() => {
                    window.nominaChart.update();
                }, 300);
            }
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateCarousel();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateCarousel();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateCarousel();
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateCarousel();
        });
    </script>
@endpush
