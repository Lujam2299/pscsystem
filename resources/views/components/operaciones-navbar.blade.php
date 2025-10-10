<div class="col-span-full">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            $cards = [
                [
                    'titulo' => 'Vales de Comida',
                    'ruta' => route('operaciones.valesComida'),
                    'icono' => 'burger',
                    'color' => 'bg-green-100 dark:bg-green-700'
                ],
                [
                    'titulo' => 'Subir Vales de Comida',
                    'ruta' => route('operaciones.valesPendientes'),
                    'icono' => 'burger',
                    'color' => 'bg-yellow-100 dark:bg-yellow-700'
                ],
                [
                    'titulo' => 'Historial de Solicitudes de Vales de Comida',
                    'ruta' => route('auxcont.historialVales'),
                    'icono' => 'file-text',
                    'color' => 'bg-blue-100 dark:bg-blue-700',
                ],
                [
                    'titulo' => 'Listas de Asistencia',
                    'ruta' => route('sup.listaAsistencia'),
                    'icono' => 'users',
                    'color' => 'bg-green-100 dark:bg-green-700'
                ],
                [
                    'titulo' => 'Registro de Eventuales',
                    'ruta' => route('operaciones.eventuales'),
                    'icono' => 'calendar',
                    'color' => 'bg-yellow-100 dark:bg-yellow-700'
                ],
                [
                    'titulo' => 'Pagos Pendientes de Eventuales',
                    'ruta' => route('operaciones.pagosEventuales'),
                    'icono' => 'calendar',
                    'color' => 'bg-blue-100 dark:bg-blue-700'
                ],
                [
                    'titulo' => 'Historial de Pagos a Eventuales',
                    'ruta' => route('operaciones.historialPagosEventuales'),
                    'icono' => 'calendar',
                    'color' => 'bg-green-100 dark:bg-green-700'
                ],
                [
                    'titulo' => 'Solicitar Vacaciones',
                    'ruta' => route('user.solicitarVacacionesForm'),
                    'icono' => 'confetti',
                    'color' => 'bg-yellow-100 dark:bg-yellow-700'
                ],
                [
                    'titulo' => 'Mi Historial de Vacaciones',
                    'ruta' => route('user.historialVacaciones'),
                    'icono' => 'calendar',
                    'color' => 'bg-blue-100 dark:bg-blue-700'
                ],
                [
                    'titulo' => 'Ficha Técnica',
                    'ruta' => route('user.verFicha', auth()->user()->id),
                    'icono' => 'file-description',
                    'color' => 'bg-green-100 dark:bg-green-700'
                ],
                [
                    'titulo' => 'Buzón de Quejas y Sugerencias',
                    'ruta' => route('user.buzon'),
                    'icono' => 'message',
                    'color' => 'bg-purple-100 dark:bg-purple-700'
                ],
            ];
        @endphp

        @foreach($cards as $card)
            <div class="transition-transform duration-200 hover:scale-105">
                <a href="{{ $card['ruta'] }}" class="block h-full">
                    <div class="p-5 rounded-xl shadow-md {{ $card['color'] }} hover:shadow-lg h-full flex flex-col justify-between transition-all duration-200 transform hover:-translate-y-1">
                        <div class="flex items-center space-x-4">
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
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white leading-tight">
                                {{ $card['titulo'] }}
                            </h3>
                        </div>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">Haz clic para ver más detalles</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
