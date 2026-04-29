@php
    use App\Models\User;
    use App\Models\SolicitudAlta;
    use App\Models\SolicitudBajas;
    use Carbon\Carbon;

    $conteoAltas = SolicitudAlta::where('status', 'Aceptada')
        ->whereDate('fecha_ingreso', '>=', Carbon::today('America/Mexico_City')->subDays(10)) //si se requiere respetar a toda la quincena
        ->count();
    $conteoBajas = SolicitudBajas::where('estatus', 'Aceptada')
        ->where('por', 'Renuncia')
        ->whereDate('fecha_baja', '>=', Carbon::today('America/Mexico_City')->subDays(10))
        ->count();

    $cards = [
        [
            'titulo' => 'Subida de Archivos',
            'ruta' => route('nominas.subidaArchivos'),
            'icono' => 'upload',
            'color' => 'bg-blue-100 dark:bg-blue-700',
            'disabled' => false,
        ],
        [
            'titulo' => 'Nuevas Altas',
            'ruta' => route('nominas.nuevasAltas'),
            'icono' => 'circle-plus',
            'color' => 'bg-green-100 dark:bg-green-700',
            'notificaciones' => $conteoAltas,
        ],
        [
            'titulo' => 'Finiquitos',
            'ruta' => route('nominas.verBajas'),
            'icono' => 'moneybag',
            'color' => 'bg-red-100 dark:bg-red-700',
            'notificaciones' => $conteoBajas,
        ],
        [
            'titulo' => 'Nóminas',
            'ruta' => route('vistaNominas'),
            'icono' => 'currency-dollar',
            'color' => 'bg-blue-100 dark:bg-blue-700',
        ],
        [
            'titulo' => 'Destajos',
            'ruta' => route('nominas.destajos'),
            'icono' => 'currency-dollar',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Deducciones',
            'ruta' => route('nominas.deducciones'),
            'icono' => 'receipt',
            'color' => 'bg-red-100 dark:bg-red-700',
        ],
        [
            'titulo' => 'Historial de Solicitudes de Vales de Comida',
            'ruta' => route('auxcont.historialVales'),
            'icono' => 'file-text',
            'color' => 'bg-blue-100 dark:bg-blue-700',
        ],
        [
            'titulo' => 'Registros de Eventuales',
            'ruta' => route('auxcont.eventuales'),
            'icono' => 'calendar',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Historial de Cedulas EMA/EBA',
            'ruta' => route('aux.historialCedulas'),
            'icono' => 'calendar',
            'color' => 'bg-yellow-100 dark:bg-yellow-700',
        ],
        [
            'titulo' => 'Historial de Archivos de Nóminas/Destajos/Pagos Semanales',
            'ruta' => route('nominas.registros'),
            'icono' => 'folder',
            'color' => 'bg-blue-100 dark:bg-blue-700',
        ],
        [
            'titulo' => 'Vacaciones',
            'ruta' => route('nominas.vacaciones'),
            'icono' => 'confetti',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Kárdex de Vacaciones',
            'ruta' => route('kardex-vacaciones'),
            'icono' => 'calendar',
            'color' => 'bg-yellow-100 dark:bg-yellow-700',
        ],
        [
            'titulo' => 'Asistencias',
            'ruta' => route('nominas.asistencias'),
            'icono' => 'file-description',
            'color' => 'bg-blue-100 dark:bg-blue-700',
        ],
        [
            'titulo' => 'Archivos',
            'ruta' => route('rh.archivos'),
            'icono' => 'folder',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Primas Vacacionales',
            'ruta' => route('nominas.usersAntiguedades'),
            'icono' => 'calendar',
            'color' => 'bg-yellow-100 dark:bg-yellow-700',
        ],
        [
            'titulo' => 'Solicitar Vacaciones',
            'ruta' => route('user.solicitarVacacionesForm'),
            'icono' => 'confetti',
            'color' => 'bg-blue-100 dark:bg-blue-700',
        ],
        [
            'titulo' => 'Mi Historial de Vacaciones',
            'ruta' => route('user.historialVacaciones'),
            'icono' => 'calendar',
            'color' => 'bg-green-100 dark:bg-green-700',
        ],
        [
            'titulo' => 'Ficha Técnica',
            'ruta' => route('user.verFicha', auth()->user()->id),
            'icono' => 'file-description',
            'color' => 'bg-yellow-100 dark:bg-yellow-700',
        ],
        [
            'titulo' => 'Gestión de Usuarios',
            'ruta' => route('admin.verUsuarios'),
            'icono' => 'user-cog',
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
<div class="col-span-full">
    @if (session('success'))
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
        @foreach ($cards as $card)
            @php
                $isDisabled = $card['disabled'] ?? false;
            @endphp

            @if ($isDisabled)
                <div class="transition-transform transform opacity-50 cursor-not-allowed" style="pointer-events: none;">
                    <div class="p-4 rounded-xl shadow-md {{ $card['color'] }} h-full flex flex-col justify-between">
                        <div class="flex items-center space-x-3">
                            @if (!empty($card['notificaciones']) && $card['notificaciones'] > 0)
                                <span
                                    class="absolute top-2 right-2 bg-red-600 text-white text-xs rounded-full px-2 py-0.5">
                                    {{ $card['notificaciones'] }}
                                </span>
                            @endif
                            <div
                                class="flex items-center justify-center mb-1 rounded-full shadow w-14 h-14 bg-white/80">
                                <i
                                    class="ti ti-{{ $card['icono'] }} text-3xl {{ Str::contains($card['color'], 'blue')
                                        ? 'text-blue-700'
                                        : (Str::contains($card['color'], 'yellow')
                                            ? 'text-yellow-700'
                                            : (Str::contains($card['color'], 'red')
                                                ? 'text-red-700'
                                                : (Str::contains($card['color'], 'green')
                                                    ? 'text-green-700'
                                                    : (Str::contains($card['color'], 'purple')
                                                        ? 'text-purple-700'
                                                        : (Str::contains($card['color'], 'gray')
                                                            ? 'text-gray-700'
                                                            : 'text-gray-800'))))) }}"></i>
                            </div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                                {{ $card['titulo'] }}
                            </h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Opción no disponible</p>
                    </div>
                </div>
            @elseif($card['titulo'] === 'Importar Datos')
                <form action="{{ route('importar.excel') }}" method="POST" enctype="multipart/form-data"
                    class="transition-transform transform hover:scale-105">
                    @csrf
                    <div
                        class="p-4 rounded-xl shadow-md {{ $card['color'] }} hover:shadow-lg h-full flex flex-col justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="flex items-center justify-center mb-1 rounded-full shadow w-14 h-14 bg-white/80">
                                <i
                                    class="ti ti-{{ $card['icono'] }} text-3xl {{ Str::contains($card['color'], 'blue')
                                        ? 'text-blue-700'
                                        : (Str::contains($card['color'], 'yellow')
                                            ? 'text-yellow-700'
                                            : (Str::contains($card['color'], 'red')
                                                ? 'text-red-700'
                                                : (Str::contains($card['color'], 'green')
                                                    ? 'text-green-700'
                                                    : (Str::contains($card['color'], 'purple')
                                                        ? 'text-purple-700'
                                                        : (Str::contains($card['color'], 'gray')
                                                            ? 'text-gray-700'
                                                            : 'text-gray-800'))))) }}"></i>
                            </div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                                {{ $card['titulo'] }}
                            </h3>
                        </div>
                        <div class="mt-4">
                            <input type="file" name="excel" accept=".xlsx,.xls"
                                class="block w-full text-sm text-gray-500 dark:text-gray-300 file:mr-4 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                required>
                            <button type="submit"
                                class="mt-2 w-full bg-blue-600 text-white py-1 px-2 rounded hover:bg-blue-700 transition">
                                Importar Excel
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <a @if ($card['titulo'] === 'Recursos Humanos') href="#"
                        @click.prevent="$dispatch('cambiar-menu', { menu: 'rh' })"
                    @else
                        href="{{ $card['ruta'] }}" @endif
                    class="transition-transform transform hover:scale-105">
                    <div
                        class="p-4 rounded-xl shadow-md {{ $card['color'] }} hover:shadow-lg h-full flex flex-col justify-between">
                        <div class="flex items-center space-x-3">
                            @if (!empty($card['notificaciones']) && $card['notificaciones'] > 0)
                                <span
                                    class="absolute top-2 right-2 bg-red-600 text-white text-xs rounded-full px-2 py-0.5">
                                    {{ $card['notificaciones'] }}
                                </span>
                            @endif
                            <div
                                class="flex items-center justify-center mb-1 rounded-full shadow w-14 h-14 bg-white/80">
                                <i
                                    class="ti ti-{{ $card['icono'] }} text-3xl {{ Str::contains($card['color'], 'blue')
                                        ? 'text-blue-700'
                                        : (Str::contains($card['color'], 'yellow')
                                            ? 'text-yellow-700'
                                            : (Str::contains($card['color'], 'red')
                                                ? 'text-red-700'
                                                : (Str::contains($card['color'], 'green')
                                                    ? 'text-green-700'
                                                    : (Str::contains($card['color'], 'purple')
                                                        ? 'text-purple-700'
                                                        : (Str::contains($card['color'], 'gray')
                                                            ? 'text-gray-700'
                                                            : 'text-gray-800'))))) }}"></i>
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
