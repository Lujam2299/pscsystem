<div class="col-span-full">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
        $tipoSeleccionado='oficina';
            $cards = [
                [
                    'titulo' => 'Solicitudes para Vales de Comida',
                    'ruta' => route('auxcont.valesComida'),
                    'icono' => 'burger',
                    'color' => 'bg-blue-100 dark:bg-blue-700',
                    'mostrar' => (Auth::user()->rol == 'CONTADORA' || Auth::user()->rol == 'admin' || Auth::user()->rol == 'ADMINISTRADOR')
                ],
                [
                    'titulo' => 'Comprobantes de Vales de Comida',
                    'ruta' => route('auxcont.comprobantesVales'),
                    'icono' => 'file-text',
                    'color' => 'bg-yellow-100 dark:bg-yellow-700',
                    'mostrar' => (Auth::user()->rol == 'CONTADORA' || Auth::user()->rol == 'admin' || Auth::user()->rol == 'ADMINISTRADOR')
                ],
                [
                    'titulo' => 'Historial de Solicitudes de Vales de Comida',
                    'ruta' => route('auxcont.historialVales'),
                    'icono' => 'file-text',
                    'color' => 'bg-green-100 dark:bg-green-700',
                    'mostrar' => (Auth::user()->rol == 'CONTADORA' || Auth::user()->rol == 'admin' || Auth::user()->rol == 'ADMINISTRADOR')
                ],
                [
                    'titulo' => 'Finiquitos',
                    'ruta' => route('auxcont.finiquitos'),
                    'icono' => 'file-text',
                    'color' => 'bg-blue-100 dark:bg-blue-700',
                ],
                [
                    'titulo' => 'Historial de Registros de Cheques',
                    'ruta' => route('auxcont.finiquitos.historial'),
                    'icono' => 'clipboard-list',
                    'color' => 'bg-yellow-100 dark:bg-yellow-700',
                ],

                [
                    'titulo' => 'Registros de Eventuales',
                    'ruta' => route('auxcont.eventuales'),
                    'icono' => 'calendar',
                    'color' => 'bg-green-100 dark:bg-green-700',
                    'mostrar' => (Auth::user()->rol == 'CONTADORA' || Auth::user()->rol == 'admin' || Auth::user()->rol == 'ADMINISTRADOR')
                ],
                [
                    'titulo' => 'Solicitar Vacaciones',
                    'ruta' => route('user.solicitarVacacionesForm'),
                    'icono' => 'confetti',
                    'color' => 'bg-blue-100 dark:bg-blue-700'
                ],
                [
                    'titulo' => 'Mi Historial de Vacaciones',
                    'ruta' => route('user.historialVacaciones'),
                    'icono' => 'calendar',
                    'color' => 'bg-yellow-100 dark:bg-yellow-700'
                ],
                [
                    'titulo' => 'Ficha Técnica',
                    'ruta' => route('user.verFicha', auth()->user()->id),
                    'icono' => 'file-description',
                    'color' => 'bg-green-100 dark:bg-green-700',
                ],
                [
                    'titulo' => 'Mensajes',
                    'ruta' => route('mensajes.index'),
                    'icono' => 'message',
                    'color' => 'bg-blue-100 dark:bg-blue-700',
                ],
                [
                    'titulo' => 'Buzón de Quejas y Sugerencias',
                    'ruta' => route('user.buzon'),
                    'icono' => 'message',
                    'color' => 'bg-purple-100 dark:bg-purple-700',
                ],
            ];
        @endphp

        @foreach($cards as $card)
            @if($card['mostrar'] ?? true)
                <div>
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
                                        (Str::contains($card['color'], 'red') ? 'text-red-700' :
                                        (Str::contains($card['color'], 'green') ? 'text-green-700' :
                                        (Str::contains($card['color'], 'purple') ? 'text-purple-700' :
                                        (Str::contains($card['color'], 'gray') ? 'text-gray-700' : 'text-gray-800')))))
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
            @endif
        @endforeach
    </div>
</div>
