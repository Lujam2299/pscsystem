@php
    use App\Models\SolicitudAlta;
    use App\Models\SolicitudBajas;
    use App\Models\SolicitudVacaciones;

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
@endphp

<div class="col-span-full">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
        $tipoSeleccionado='oficina';
            $cards = [
                [
                    'titulo' => 'Solicitudes de Altas',
                    'ruta' => route('rh.solicitudesAltas'),
                    'icono' => 'trending-up',
                    'color' => 'bg-blue-100 dark:bg-blue-700',
                    'notificaciones' => $altasEnProceso,
                ],
                [
                    'titulo' => 'Solicitudes de Bajas',
                    'ruta' => route('rh.solicitudesBajas'),
                    'icono' => 'trending-down',
                    'color' => 'bg-red-100 dark:bg-red-700',
                    'notificaciones' => $bajasEnProceso,
                ],
                [
                    'titulo' => 'Lista de Asistencia Montana',
                    'ruta' => route('rh.listaAsistencia'),
                    'icono' => 'users',
                    'color' => 'bg-yellow-100 dark:bg-yellow-700',
                    'disabled' => !in_array(
                        strtoupper(Auth::user()?->rol ?? ''),
                        ['JEFA RECURSOS HUMANOS', 'JEFE', 'ADMINISTRADOR', 'ADMIN']
                    ),
                ],
                [
                    'titulo' => 'Historial de Altas',
                    'ruta' => route('rh.historialSolicitudesAltas'),
                    'icono' => 'folder-open',
                    'color' => 'bg-indigo-100 dark:bg-indigo-700'
                ],
                [
                    'titulo' => 'Historial de Bajas',
                    'ruta' => route('rh.historialSolicitudesBajas'),
                    'icono' => 'file-text',
                    'color' => 'bg-pink-100 dark:bg-pink-700'
                ],
                [
                    'titulo' => 'Vacaciones',
                    'ruta' => route('rh.vistaVacaciones'),
                    'icono' => 'confetti',
                    'color' => 'bg-yellow-100 dark:bg-yellow-700',
                    'notificaciones' => $vacacionesEnProceso,
                ],
                [
                    'titulo' => 'Generar Nueva Alta',
                    'ruta' => route('rh.formAlta', $tipoSeleccionado),
                    'icono' => 'trending-up',
                    'color' => 'bg-blue-100 dark:bg-blue-700',
                ],
                [
                    'titulo' => 'Generar Nueva Baja',
                    'ruta' => route('rh.generarNuevaBajaForm'),
                    'icono' => 'trending-down',
                    'color' => 'bg-red-100 dark:bg-red-700',
                ],
                [
                    'titulo' => 'Historial de Vacaciones',
                    'ruta' => route('rh.historialVacaciones'),
                    'icono' => 'file-text',
                    'color' => 'bg-yellow-100 dark:bg-yellow-700',
                ],
                [
                    'titulo' => 'Kárdex de Vacaciones',
                    'ruta' => route('kardex-vacaciones'),
                    'icono' => 'calendar',
                    'color' => 'bg-blue-100 dark:bg-blue-700',
                ],
                [
                    'titulo' => 'Buzón de Quejas y Sugerencias',
                    'ruta' => route('user.buzon'),
                    'icono' => 'message',
                    'color' => 'bg-red-100 dark:bg-red-700',
                ],
                [
                    'titulo' => 'Gestión de Usuarios',
                    'ruta' => route('admin.verUsuarios'),
                    'icono' => 'users',
                    'color' => 'bg-yellow-100 dark:bg-yellow-700'
                ],
                [
                    'titulo' => 'Reingresos',
                    'ruta' => route('rh.reingresos'),
                    'icono' => 'refresh',
                    'color' => 'bg-blue-100 dark:bg-blue-700',
                    'disabled' => false,
                ],
                [
                    'titulo' => 'Archivos',
                    'ruta' => route('rh.archivos'),
                    'icono' => 'folder',
                    'color' => 'bg-green-100 dark:bg-green-700'
                ],
                [
                    'titulo' => 'Mensajes',
                    'ruta' => route('mensajes.index'),
                    'icono' => 'message',
                    'color' => 'bg-purple-300 dark:bg-purple-700',
                ],
            ];
        @endphp

        @foreach($cards as $card)
        <div class="{{ $card['disabled'] ?? false ? 'pointer-events-none opacity-50' : '' }}" style="{{ ($card['disabled'] ?? false) ? 'opacity: 0.5; pointer-events: none; cursor: default;' : '' }}">
            <a href="{{ $card['ruta'] }}" class="transition-transform transform hover:scale-105 block">
                <div class="p-4 rounded-xl shadow-md {{ $card['color'] }} hover:shadow-lg h-full flex flex-col justify-between relative">
                    @if (!empty($card['notificaciones']) && $card['notificaciones'] > 0)
                        <span class="absolute top-2 right-2 bg-red-600 text-white text-xs rounded-full px-2 py-0.5">
                            {{ $card['notificaciones'] }}
                        </span>
                    @endif
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center justify-center mb-1 rounded-full shadow w-14 h-14 bg-white/80">
                            <i class="ti ti-{{ $card['icono'] }} text-3xl {{
                                Str::contains($card['color'], 'blue') ? 'text-blue-700' :
                                (Str::contains($card['color'], 'yellow') ? 'text-yellow-700' :
                                (Str::contains($card['color'], 'pink') ? 'text-pink-700' :
                                (Str::contains($card['color'], 'indigo') ? 'text-indigo-700' :
                                (Str::contains($card['color'], 'red') ? 'text-red-700' :
                                (Str::contains($card['color'], 'green') ? 'text-green-700' :
                                (Str::contains($card['color'], 'purple') ? 'text-purple-700' :
                                (Str::contains($card['color'], 'gray') ? 'text-gray-700' : 'text-gray-800')))))))
                            }}"></i>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                            {{ $card['titulo'] }}
                        </h3>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Haz clic para ver más detalles</p>
                </div>
            </a>
        </div>
        @endforeach

    </div>
</div>
