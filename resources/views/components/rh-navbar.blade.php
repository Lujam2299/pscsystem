@php
    use App\Models\SolicitudAlta;
    use App\Models\SolicitudBajas;
    use App\Models\SolicitudVacaciones;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Str;

    // --- LÓGICA ORIGINAL INTACTA ---
    $altasEnProceso = SolicitudAlta::where('status', 'En Proceso')
                    ->where('observaciones', '!=', 'Solicitud enviada a Administrador.')
                    ->count();
    $bajasEnProceso = SolicitudBajas::with('user.solicitudAlta')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('estatus', 'En Proceso')
                    ->where('por', 'Renuncia');
                })->orWhere(function ($q) {
                    $q->where('estatus', 'Aceptada')
                    ->where('observaciones', 'Finiquito enviado a RH.');
                });
            })
            ->count();
    $vacacionesEnProceso = SolicitudVacaciones::where('estatus', 'En Proceso')
                            ->where('observaciones', 'Solicitud aceptada, falta subir archivo de solicitud.')
                            ->count();

    $tipoSeleccionado = 'oficina';

    // Definición de tarjetas con metadatos para estilos dinámicos
    $cards = [
        /*[
            'titulo' => 'Solicitudes de Altas',
            'ruta' => route('rh.solicitudesAltas'),
            'icono' => 'user-plus',
            'theme' => 'blue',
            'notificaciones' => $altasEnProceso,
            'desc' => 'Gestionar altas pendientes'
        ],
        [
            'titulo' => 'Solicitudes de Bajas',
            'ruta' => route('rh.solicitudesBajas'),
            'icono' => 'user-x',
            'theme' => 'red',
            'notificaciones' => $bajasEnProceso,
            'desc' => 'Renuncias y finiquitos'
        ],
        [
            'titulo' => 'Lista de Asistencia',
            'ruta' => route('rh.listaAsistencia'),
            'icono' => 'clipboard-check',
            'theme' => 'amber',
            'disabled' => !in_array(strtoupper(Auth::user()?->rol ?? ''), ['JEFA RECURSOS HUMANOS', 'JEFE', 'ADMINISTRADOR', 'ADMIN']),
            'desc' => 'Control diario Montana'
        ],*/
        [
            'titulo' => 'Generar Nueva Alta',
            'ruta' => route('rh.formAlta', $tipoSeleccionado),
            'icono' => 'file-upload',
            'theme' => 'indigo',
            'isAction' => true,
            'desc' => 'Ingresar nuevo personal'
        ],
        [
            'titulo' => 'Generar Nueva Baja',
            'ruta' => route('rh.generarNuevaBajaForm'),
            'icono' => 'file-download',
            'theme' => 'rose',
            'isAction' => true,
            'desc' => 'Procesar salida'
        ],
        [
            'titulo' => 'Vacaciones Pendientes',
            'ruta' => route('rh.vistaVacaciones'),
            'icono' => 'plane-inflight',
            'theme' => 'teal',
            'notificaciones' => $vacacionesEnProceso,
            'desc' => 'Aprobaciones requeridas'
        ],
        [
            'titulo' => 'Historial de Altas',
            'ruta' => route('rh.historialSolicitudesAltas'),
            'icono' => 'history',
            'theme' => 'blue',
            'variant' => 'soft',
            'desc' => 'Registro histórico'
        ],
        [
            'titulo' => 'Historial de Bajas',
            'ruta' => route('rh.historialSolicitudesBajas'),
            'icono' => 'archive',
            'theme' => 'red',
            'variant' => 'soft',
            'desc' => 'Registro histórico'
        ],
        [
            'titulo' => 'Kárdex de Vacaciones',
            'ruta' => route('kardex-vacaciones'),
            'icono' => 'calendar-stats',
            'theme' => 'teal',
            'variant' => 'soft',
            'desc' => 'Días disponibles'
        ],
        [
            'titulo' => 'Reingresos',
            'ruta' => route('rh.reingresos'),
            'icono' => 'refresh',
            'theme' => 'cyan',
            'desc' => 'Personal recuperado'
        ],
        [
            'titulo' => 'Archivos RH',
            'ruta' => route('rh.archivos'),
            'icono' => 'folder-open',
            'theme' => 'emerald',
            'desc' => 'Documentación general'
        ],
        [
            'titulo' => 'Buzón de Quejas',
            'ruta' => route('user.buzon'),
            'icono' => 'message-report',
            'theme' => 'orange',
            'desc' => 'Sugerencias y reportes'
        ],
        [
            'titulo' => 'Gestión de Usuarios',
            'ruta' => route('admin.verUsuarios'),
            'icono' => 'user-cog',
            'theme' => 'slate',
            'desc' => 'Administración de acceso'
        ],
        [
            'titulo' => 'Mensajería Interna',
            'ruta' => route('mensajes.index'),
            'icono' => 'mail',
            'theme' => 'violet',
            'desc' => 'Comunicación directa'
        ],
    ];

    // Helper para clases de color basado en el tema
    function getThemeClasses($theme, $isHover = false) {
        $colors = [
            'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'border-blue-100 dark:border-blue-800', 'hover' => 'hover:border-blue-300 dark:hover:border-blue-700'],
            'red' => ['bg' => 'bg-red-50 dark:bg-red-900/20', 'text' => 'text-red-600 dark:text-red-400', 'border' => 'border-red-100 dark:border-red-800', 'hover' => 'hover:border-red-300 dark:hover:border-red-700'],
            'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-100 dark:border-amber-800', 'hover' => 'hover:border-amber-300 dark:hover:border-amber-700'],
            'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'text' => 'text-indigo-600 dark:text-indigo-400', 'border' => 'border-indigo-100 dark:border-indigo-800', 'hover' => 'hover:border-indigo-300 dark:hover:border-indigo-700'],
            'rose' => ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'text' => 'text-rose-600 dark:text-rose-400', 'border' => 'border-rose-100 dark:border-rose-800', 'hover' => 'hover:border-rose-300 dark:hover:border-rose-700'],
            'teal' => ['bg' => 'bg-teal-50 dark:bg-teal-900/20', 'text' => 'text-teal-600 dark:text-teal-400', 'border' => 'border-teal-100 dark:border-teal-800', 'hover' => 'hover:border-teal-300 dark:hover:border-teal-700'],
            'cyan' => ['bg' => 'bg-cyan-50 dark:bg-cyan-900/20', 'text' => 'text-cyan-600 dark:text-cyan-400', 'border' => 'border-cyan-100 dark:border-cyan-800', 'hover' => 'hover:border-cyan-300 dark:hover:border-cyan-700'],
            'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-100 dark:border-emerald-800', 'hover' => 'hover:border-emerald-300 dark:hover:border-emerald-700'],
            'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-900/20', 'text' => 'text-orange-600 dark:text-orange-400', 'border' => 'border-orange-100 dark:border-orange-800', 'hover' => 'hover:border-orange-300 dark:hover:border-orange-700'],
            'slate' => ['bg' => 'bg-slate-50 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-400', 'border' => 'border-slate-200 dark:border-slate-700', 'hover' => 'hover:border-slate-300 dark:hover:border-slate-600'],
            'violet' => ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'text' => 'text-violet-600 dark:text-violet-400', 'border' => 'border-violet-100 dark:border-violet-800', 'hover' => 'hover:border-violet-300 dark:hover:border-violet-700'],
        ];

        $c = $colors[$theme] ?? $colors['slate'];
        return $isHover ? $c['hover'] : ($c['bg'] . ' ' . $c['text'] . ' ' . $c['border']);
    }
@endphp

<div class="col-span-full">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Panel de Recursos Humanos</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Gestión de personal</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($cards as $card)
            @php
                $isDisabled = $card['disabled'] ?? false;
                $theme = $card['theme'] ?? 'slate';
                $baseClasses = getThemeClasses($theme);
                $hoverClasses = getThemeClasses($theme, true);
                $iconContainerClass = match($theme) {
                    'blue' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-300',
                    'red' => 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-300',
                    'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300',
                    'indigo' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300',
                    'rose' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-300',
                    'teal' => 'bg-teal-100 text-teal-600 dark:bg-teal-900/50 dark:text-teal-300',
                    'cyan' => 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-300',
                    'emerald' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300',
                    'orange' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/50 dark:text-orange-300',
                    'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                    'violet' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-300',
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
