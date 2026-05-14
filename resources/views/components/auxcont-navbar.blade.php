@php
    use App\Models\ValesComida;
    use App\Models\SolicitudBajas;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Auth;

    // --- LÓGICA ORIGINAL INTACTA ---
    $conteoSolicitudesVales = ValesComida::where('estatus', 'En Proceso')->count();
    $conteoComprobantesPendientes = ValesComida::where('estatus', 'Comprobación En Revisión')->count();
    $renuncias = SolicitudBajas::where('estatus', 'Aceptada')
            ->where('observaciones', 'Finiquito enviado a RH.')
            ->where('arch_cheque', NULL)
            ->whereDate('fecha_baja', '>=', now()->subDays(30))
            ->orderBy('fecha_baja', 'desc')
            ->count();

    $tipoSeleccionado = 'oficina';

    // Definición de tarjetas con metadatos para estilos dinámicos
    $cards = [
        // --- VALES DE COMIDA (GESTIÓN CONTABLE) ---
        [
            'titulo' => 'Solicitudes de Vales de Comida',
            'ruta' => route('auxcont.valesComida'),
            'icono' => 'ticket',
            'theme' => 'blue',
            'notificaciones' => $conteoSolicitudesVales,
            'desc' => 'Autorización de nuevas solicitudes',
            'mostrar' => in_array(Auth::user()->rol, ['CONTADORA', 'admin', 'ADMINISTRADOR'])
        ],
        [
            'titulo' => 'Revisión de Comprobantes',
            'ruta' => route('auxcont.comprobantesVales'),
            'icono' => 'receipt-2',
            'theme' => 'amber',
            'notificaciones' => $conteoComprobantesPendientes,
            'desc' => 'Validar evidencias de Vales de Comida',
            'mostrar' => in_array(Auth::user()->rol, ['CONTADORA', 'admin', 'ADMINISTRADOR'])
        ],
        [
            'titulo' => 'Historial de Vales',
            'ruta' => route('auxcont.historialVales'),
            'icono' => 'history',
            'theme' => 'blue',
            'variant' => 'soft',
            'desc' => 'Registro histórico de Vales de Comida',
            'mostrar' => in_array(Auth::user()->rol, ['CONTADORA', 'admin', 'ADMINISTRADOR'])
        ],

        // --- FINIQUITOS Y PAGOS ---
        [
            'titulo' => 'Finiquitos Pendientes',
            'ruta' => route('auxcont.finiquitos'),
            'icono' => 'cash-move',
            'theme' => 'rose',
            'notificaciones' => $renuncias,
            'desc' => 'Cheques por entregar a ex-empleados'
        ],
        [
            'titulo' => 'Historial de Cheques',
            'ruta' => route('auxcont.finiquitos.historial'),
            'icono' => 'clipboard-list',
            'theme' => 'rose',
            'variant' => 'soft',
            'desc' => 'Bitácora de pagos de finiquito'
        ],

        // --- EVENTUALES ---
        [
            'titulo' => 'Registros de Eventuales',
            'ruta' => route('auxcont.eventuales'),
            'icono' => 'users-group',
            'theme' => 'indigo',
            'desc' => 'Control de personal temporal',
            'mostrar' => in_array(Auth::user()->rol, ['CONTADORA', 'admin', 'ADMINISTRADOR'])
        ],

        // --- USUARIO Y GENERAL ---
        [
            'titulo' => 'Solicitar Vacaciones',
            'ruta' => route('user.solicitarVacacionesForm'),
            'icono' => 'plane-inflight',
            'theme' => 'teal',
            'desc' => 'Formulario de solicitud'
        ],
        [
            'titulo' => 'Mis Vacaciones',
            'ruta' => route('user.historialVacaciones'),
            'icono' => 'calendar-user',
            'theme' => 'teal',
            'variant' => 'soft',
            'desc' => 'Historial personal'
        ],
        [
            'titulo' => 'Mensajería Interna',
            'ruta' => route('mensajes.index'),
            'icono' => 'mail',
            'theme' => 'violet',
            'desc' => 'Comunicación directa'
        ],
        [
            'titulo' => 'Mi Ficha Técnica',
            'ruta' => route('user.verFicha', auth()->user()->id),
            'icono' => 'id-badge',
            'theme' => 'slate',
            'desc' => 'Datos laborales personales'
        ],
        [
            'titulo' => 'Buzón de Quejas',
            'ruta' => route('user.buzon'),
            'icono' => 'message-report',
            'theme' => 'gray',
            'desc' => 'Sugerencias anónimas'
        ],
    ];

    // Helper para clases de color (Consistente con los otros dashboards)
    function getContThemeClasses($theme, $isHover = false) {
        $colors = [
            'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'border-blue-100 dark:border-blue-800', 'hover' => 'hover:border-blue-300 dark:hover:border-blue-700'],
            'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-100 dark:border-amber-800', 'hover' => 'hover:border-amber-300 dark:hover:border-amber-700'],
            'rose' => ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'text' => 'text-rose-600 dark:text-rose-400', 'border' => 'border-rose-100 dark:border-rose-800', 'hover' => 'hover:border-rose-300 dark:hover:border-rose-700'],
            'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'text' => 'text-indigo-600 dark:text-indigo-400', 'border' => 'border-indigo-100 dark:border-indigo-800', 'hover' => 'hover:border-indigo-300 dark:hover:border-indigo-700'],
            'teal' => ['bg' => 'bg-teal-50 dark:bg-teal-900/20', 'text' => 'text-teal-600 dark:text-teal-400', 'border' => 'border-teal-100 dark:border-teal-800', 'hover' => 'hover:border-teal-300 dark:hover:border-teal-700'],
            'violet' => ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'text' => 'text-violet-600 dark:text-violet-400', 'border' => 'border-violet-100 dark:border-violet-800', 'hover' => 'hover:border-violet-300 dark:hover:border-violet-700'],
            'slate' => ['bg' => 'bg-slate-50 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-400', 'border' => 'border-slate-200 dark:border-slate-700', 'hover' => 'hover:border-slate-300 dark:hover:border-slate-600'],
            'gray' => ['bg' => 'bg-gray-50 dark:bg-gray-800', 'text' => 'text-gray-600 dark:text-gray-400', 'border' => 'border-gray-200 dark:border-gray-700', 'hover' => 'hover:border-gray-300 dark:hover:border-gray-600'],
        ];

        $c = $colors[$theme] ?? $colors['slate'];
        return $isHover ? $c['hover'] : ($c['bg'] . ' ' . $c['text'] . ' ' . $c['border']);
    }
@endphp

<div class="col-span-full">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Módulo de Contabilidad</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Gestión de vales, finiquitos y controles administrativos</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($cards as $card)
            @if($card['mostrar'] ?? true)
                @php
                    $theme = $card['theme'] ?? 'slate';
                    $baseClasses = getContThemeClasses($theme);
                    $hoverClasses = getContThemeClasses($theme, true);

                    // Mapeo de colores para el contenedor del icono
                    $iconContainerClass = match($theme) {
                        'blue' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-300',
                        'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300',
                        'rose' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-300',
                        'indigo' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300',
                        'teal' => 'bg-teal-100 text-teal-600 dark:bg-teal-900/50 dark:text-teal-300',
                        'violet' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-300',
                        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                        'gray' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp

                <div class="group relative">
                    <a href="{{ $card['ruta'] }}" class="block h-full">
                        <div class="h-full bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition-all duration-300 {{ $hoverClasses }} flex flex-col justify-between">

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
                    </a>
                </div>
            @endif
        @endforeach
    </div>
</div>
