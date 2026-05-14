@php
    use App\Models\User;
    use App\Models\SolicitudAlta;
    use App\Models\SolicitudBajas;
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Auth;

    // --- LÓGICA ORIGINAL INTACTA ---
    $conteoAltas = SolicitudAlta::where('status', 'Aceptada')
        ->whereDate('fecha_ingreso', '>=', Carbon::today('America/Mexico_City')->subDays(10))
        ->count();
    $conteoBajas = SolicitudBajas::where('estatus', 'Aceptada')
        ->where('por', 'Renuncia')
        ->whereDate('fecha_baja', '>=', Carbon::today('America/Mexico_City')->subDays(10))
        ->count();

    // Definición de tarjetas con metadatos para estilos dinámicos
    $cards = [
        // --- GESTIÓN PRINCIPAL ---
        [
            'titulo' => 'Nóminas Quincenales',
            'ruta' => route('vistaNominas'),
            'icono' => 'currency-dollar',
            'theme' => 'blue',
            'desc' => 'Cálculo de nómina'
        ],
        [
            'titulo' => 'Destajos',
            'ruta' => route('nominas.destajos'),
            'icono' => 'tools',
            'theme' => 'blue',
            'desc' => 'Pagos por destajo'
        ],
        [
            'titulo' => 'Subida de Archivos',
            'ruta' => route('nominas.subidaArchivos'),
            'icono' => 'upload',
            'theme' => 'indigo',
            'desc' => 'Cargar archivos'
        ],

        // --- ALTAS Y BAJAS (MOVIMIENTOS RECIENTES) ---
        [
            'titulo' => 'Nuevas Altas',
            'ruta' => route('nominas.nuevasAltas'),
            'icono' => 'user-plus',
            'theme' => 'emerald',
            'notificaciones' => $conteoAltas,
            'desc' => 'Personal ingresado recientemente'
        ],
        [
            'titulo' => 'Finiquitos/Bajas',
            'ruta' => route('nominas.verBajas'),
            'icono' => 'user-x',
            'theme' => 'rose',
            'notificaciones' => $conteoBajas,
            'desc' => 'Procesar bajas y finiquitos'
        ],

        // --- DEDUCCIONES Y VALES ---
        [
            'titulo' => 'Deducciones Activas',
            'ruta' => route('nominas.deducciones'),
            'icono' => 'receipt-2',
            'theme' => 'red',
            'desc' => 'Préstamos y descuentos'
        ],
        [
            'titulo' => 'Historial Deducciones',
            'ruta' => route('nominas.historialDeducciones'),
            'icono' => 'history',
            'theme' => 'red',
            'variant' => 'soft',
            'desc' => 'Registro de deducciones'
        ],
        [
            'titulo' => 'Vales de Comida',
            'ruta' => route('auxcont.historialVales'),
            'icono' => 'ticket',
            'theme' => 'orange',
            'desc' => 'Solicitudes e historial'
        ],

        // --- VACACIONES Y PRESTACIONES ---
        [
            'titulo' => 'Gestión Vacaciones',
            'ruta' => route('nominas.vacaciones'),
            'icono' => 'plane-inflight',
            'theme' => 'teal',
            'desc' => 'Registros generales de vacaciones'
        ],
        [
            'titulo' => 'Kárdex Vacacional',
            'ruta' => route('kardex-vacaciones'),
            'icono' => 'calendar-stats',
            'theme' => 'teal',
            'variant' => 'soft',
            'desc' => 'Días disponibles por empleado'
        ],
        [
            'titulo' => 'Primas Vacacionales',
            'ruta' => route('nominas.usersAntiguedades'),
            'icono' => 'coin',
            'theme' => 'amber',
            'desc' => 'Cálculo por antigüedad'
        ],
        [
            'titulo' => 'Solicitar Vacaciones',
            'ruta' => route('user.solicitarVacacionesForm'),
            'icono' => 'calendar',
            'theme' => 'cyan',
            'desc' => 'Formulario para personal'
        ],

        // --- HISTORIAL Y REGISTROS ---
        [
            'titulo' => 'Asistencias',
            'ruta' => route('nominas.asistencias'),
            'icono' => 'clock-check',
            'theme' => 'blue',
            'variant' => 'soft',
            'desc' => 'Registro diario'
        ],
        [
            'titulo' => 'Eventuales',
            'ruta' => route('auxcont.eventuales'),
            'icono' => 'users-group',
            'theme' => 'violet',
            'desc' => 'Personal temporal'
        ],
        [
            'titulo' => 'Cédulas EMA/EBA',
            'ruta' => route('aux.historialCedulas'),
            'icono' => 'file-certificate',
            'theme' => 'yellow',
            'desc' => 'Histórico de cédulas'
        ],
        [
            'titulo' => 'Archivos de Nómina',
            'ruta' => route('nominas.registros'),
            'icono' => 'folder-open',
            'theme' => 'slate',
            'desc' => 'Historial de archivos de nómina subidos'
        ],

        // --- ADMINISTRACIÓN Y USUARIO ---
        [
            'titulo' => 'Mi Ficha Técnica',
            'ruta' => route('user.verFicha', auth()->user()->id),
            'icono' => 'id-badge',
            'theme' => 'gray',
            'desc' => 'Datos personales y laborales'
        ],
        [
            'titulo' => 'Mis Vacaciones',
            'ruta' => route('user.historialVacaciones'),
            'icono' => 'calendar-user',
            'theme' => 'cyan',
            'variant' => 'soft',
            'desc' => 'Historial personal'
        ],
        [
            'titulo' => 'Archivos RH',
            'ruta' => route('rh.archivos'),
            'icono' => 'archive',
            'theme' => 'emerald',
            'variant' => 'soft',
            'desc' => 'Documentación general'
        ],
        [
            'titulo' => 'Gestión de Usuarios',
            'ruta' => route('admin.verUsuarios'),
            'icono' => 'user-cog',
            'theme' => 'slate',
            'desc' => 'Administración de acceso'
        ],
        [
            'titulo' => 'Buzón de Quejas',
            'ruta' => route('user.buzon'),
            'icono' => 'message-report',
            'theme' => 'orange',
            'variant' => 'soft',
            'desc' => 'Sugerencias anónimas'
        ],
    ];

    // Helper para clases de color (Mismo helper que en RRHH para consistencia)
    function getNominaThemeClasses($theme, $isHover = false) {
        $colors = [
            'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'border-blue-100 dark:border-blue-800', 'hover' => 'hover:border-blue-300 dark:hover:border-blue-700'],
            'red' => ['bg' => 'bg-red-50 dark:bg-red-900/20', 'text' => 'text-red-600 dark:text-red-400', 'border' => 'border-red-100 dark:border-red-800', 'hover' => 'hover:border-red-300 dark:hover:border-red-700'],
            'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-100 dark:border-emerald-800', 'hover' => 'hover:border-emerald-300 dark:hover:border-emerald-700'],
            'rose' => ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'text' => 'text-rose-600 dark:text-rose-400', 'border' => 'border-rose-100 dark:border-rose-800', 'hover' => 'hover:border-rose-300 dark:hover:border-rose-700'],
            'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'text' => 'text-indigo-600 dark:text-indigo-400', 'border' => 'border-indigo-100 dark:border-indigo-800', 'hover' => 'hover:border-indigo-300 dark:hover:border-indigo-700'],
            'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-100 dark:border-amber-800', 'hover' => 'hover:border-amber-300 dark:hover:border-amber-700'],
            'teal' => ['bg' => 'bg-teal-50 dark:bg-teal-900/20', 'text' => 'text-teal-600 dark:text-teal-400', 'border' => 'border-teal-100 dark:border-teal-800', 'hover' => 'hover:border-teal-300 dark:hover:border-teal-700'],
            'cyan' => ['bg' => 'bg-cyan-50 dark:bg-cyan-900/20', 'text' => 'text-cyan-600 dark:text-cyan-400', 'border' => 'border-cyan-100 dark:border-cyan-800', 'hover' => 'hover:border-cyan-300 dark:hover:border-cyan-700'],
            'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-900/20', 'text' => 'text-orange-600 dark:text-orange-400', 'border' => 'border-orange-100 dark:border-orange-800', 'hover' => 'hover:border-orange-300 dark:hover:border-orange-700'],
            'violet' => ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'text' => 'text-violet-600 dark:text-violet-400', 'border' => 'border-violet-100 dark:border-violet-800', 'hover' => 'hover:border-violet-300 dark:hover:border-violet-700'],
            'yellow' => ['bg' => 'bg-yellow-50 dark:bg-yellow-900/20', 'text' => 'text-yellow-600 dark:text-yellow-400', 'border' => 'border-yellow-100 dark:border-yellow-800', 'hover' => 'hover:border-yellow-300 dark:hover:border-yellow-700'],
            'slate' => ['bg' => 'bg-slate-50 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-400', 'border' => 'border-slate-200 dark:border-slate-700', 'hover' => 'hover:border-slate-300 dark:hover:border-slate-600'],
            'gray' => ['bg' => 'bg-gray-50 dark:bg-gray-800', 'text' => 'text-gray-600 dark:text-gray-400', 'border' => 'border-gray-200 dark:border-gray-700', 'hover' => 'hover:border-gray-300 dark:hover:border-gray-600'],
        ];

        $c = $colors[$theme] ?? $colors['slate'];
        return $isHover ? $c['hover'] : ($c['bg'] . ' ' . $c['text'] . ' ' . $c['border']);
    }
@endphp

<div class="col-span-full">

    {{-- Alertas de Sesión --}}
    @if (session('success'))
        <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm flex items-center">
            <svg class="h-5 w-5 text-emerald-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
        </div>
    @elseif (session('error'))
        <div class="mb-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm flex items-center">
            <svg class="h-5 w-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Módulo de Nóminas</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Gestión de pagos y movimientos de personal</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($cards as $card)
            @php
                $isDisabled = $card['disabled'] ?? false;
                $theme = $card['theme'] ?? 'slate';
                $baseClasses = getNominaThemeClasses($theme);
                $hoverClasses = getNominaThemeClasses($theme, true);

                // Mapeo de colores para el contenedor del icono
                $iconContainerClass = match($theme) {
                    'blue' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-300',
                    'red' => 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-300',
                    'emerald' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300',
                    'rose' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-300',
                    'indigo' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300',
                    'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300',
                    'teal' => 'bg-teal-100 text-teal-600 dark:bg-teal-900/50 dark:text-teal-300',
                    'cyan' => 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-300',
                    'orange' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/50 dark:text-orange-300',
                    'violet' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-300',
                    'yellow' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/50 dark:text-yellow-300',
                    'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                    'gray' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                    default => 'bg-gray-100 text-gray-600',
                };
            @endphp

            <div class="group relative {{ $isDisabled ? 'opacity-60 grayscale cursor-not-allowed' : '' }}">
                @if(!$isDisabled)
                    <a href="{{ $card['ruta'] }}" class="block h-full">
                @endif

                    <div class="h-full bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition-all duration-300 {{ !$isDisabled ? $hoverClasses : '' }} flex flex-col justify-between">

                        {{-- Header: Icono y Badge --}}
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-xl {{ $iconContainerClass }} flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <i class="ti ti-{{ $card['icono'] }} text-2xl"></i>
                            </div>

                            @if (!empty($card['notificaciones']) && $card['notificaciones'] > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border border-red-200 dark:border-red-800 animate-pulse">
                                    {{ $card['notificaciones'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Content: Título y Descripción --}}
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ $card['titulo'] }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                {{ $card['desc'] ?? 'Acceder al módulo' }}
                            </p>
                        </div>

                        {{-- Footer: Indicador visual --}}
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center text-xs font-medium text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                            <span>Ver detalles</span>
                            <i class="ti ti-arrow-right ml-1 transform group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>

                @if(!$isDisabled)
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>
