@php
$cards = [
[
'titulo' => 'Control de Vehículos',
'ruta' => route('vehiculos.index'),
'icono' => 'car',
'color' => 'bg-blue-100 dark:bg-blue-700',
],
[
'titulo' => 'Mapa',
'ruta' => route('monitoreo.mapa'),
'icono' => 'map-2',
'color' => 'bg-yellow-100 dark:bg-yellow-700',
],
[
'titulo' => 'Control de Gastos',
'ruta' => route('gastos.index'),
'icono' => 'receipt-2',
'color' => 'bg-red-100 dark:bg-red-700',
],
[
'titulo' => 'Compras',
'ruta' => route('compras.index'),
'icono' => 'shopping-cart',
'color' => 'bg-blue-100 dark:bg-blue-700',
],
[
'titulo' => 'Servicios y Reparaciones',
'ruta' => route('servicios.index'),
'icono' => 'tool',
'color' => 'bg-yellow-100 dark:bg-yellow-700',
],
[
'titulo' => 'Siniestros',
'ruta' => route('siniestros.index'),
'icono' => 'car-crash',
'color' => 'bg-red-100 dark:bg-red-700',
],
[
'titulo' => 'Documentación de Usuarios',
'ruta' => "#",
'icono' => 'file-description',
'color' => 'bg-blue-100 dark:bg-blue-700',
],
[
'titulo' => 'Solicitar Vacaciones',
'ruta' => route('user.solicitarVacacionesForm'),
'icono' => 'confetti',
'color' => 'bg-yellow-100 dark:bg-yellow-700',
],
[
'titulo' => 'Deducciones',
'ruta' => route('monitoreo.deducciones'),
'icono' => 'file-pencil',
'color' => 'bg-red-100 dark:bg-red-700',
],
[
'titulo' => 'Gasolinas',
'ruta' => route('gasolinas.index'),
'icono' => 'gas-station',
'color' => 'bg-blue-100 dark:bg-blue-700',
],
[
'titulo' => 'Ficha Técnica',
'ruta' => route('user.verFicha', auth()->user()->id),
'icono' => 'id',
'color' => 'bg-yellow-100 dark:bg-yellow-700',
],
[
'titulo' => 'Buzón de Quejas y Sugerencias',
'ruta' => route('user.buzon'),
'icono' => 'message',
'color' => 'bg-purple-100 dark:bg-purple-700',
],
[
'titulo' => 'Mi Historial de Vacaciones',
'ruta' => route('user.historialVacaciones'),
'icono' => 'calendar',
'color' => 'bg-green-100 dark:bg-green-700',
],
/*
[
'titulo' => 'Importar Datos',
'ruta' => route('importar.excel'),
'icono' => 'folder',
'color' => 'bg-gray-100 dark:bg-gray-700',
'disabled' => true,
],*/

];
@endphp
<div class="col-span-full">
    @if(session('success'))
    <div class="px-4 py-3 text-green-900 bg-green-100 border-t-4 border-green-500 rounded-b shadow-md" role="alert">
        <div class="flex">
            <div>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @elseif (session('error'))
    <div class="px-4 py-3 text-red-900 bg-red-100 border-t-4 border-red-500 rounded-b shadow-md" role="alert">
        <div class="flex">
            <div>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
                    <div class="text-3xl">
                        <i class="ti ti-{{ $card['icono'] }} text-3xl text-gray-400"></i>
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
                <div class="mt-4">
                    <input type="file" name="excel" accept=".xlsx,.xls"
                        class="block w-full text-sm text-gray-500 dark:text-gray-300 file:mr-4 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        required>
                    <button type="submit"
                        class="w-full px-2 py-1 mt-2 text-white transition bg-blue-600 rounded hover:bg-blue-700">
                        Importar Excel
                    </button>
                </div>
            </div>
        </form>
        @else
        <a @if($card['titulo']==='Recursos Humanos' ) href="#"
            @click.prevent="$dispatch('cambiar-menu', { menu: 'rh' })" @else href="{{ $card['ruta'] }}" @endif
            class="transition-transform transform hover:scale-105">
            <div
                class="p-4 rounded-xl shadow-md {{ $card['color'] }} hover:shadow-lg h-full flex flex-col justify-between">
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
        @endif
        @endforeach
    </div>
</div>
