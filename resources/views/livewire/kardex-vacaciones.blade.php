<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <!-- Título con ícono SVG -->
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-3">
            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Kárdex de Vacaciones
        </h2>

        <!-- Botón Exportar PDF -->
        @if($selectedUser && count($vacaciones) > 0)
            <div class="mb-4 flex justify-end">
                <button
                    wire:click="exportToPdf"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Exportar a PDF</span>

                    <!-- Spinner de carga -->
                    <svg wire:loading class="animate-spin h-4 w-4 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Buscador de Usuarios -->
        <div class="mb-6 relative">
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Usuario</label>
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Escribe el nombre o email del usuario..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                @if($selectedUser)
                    <button
                        wire:click="clearSelection"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Dropdown de Usuarios -->
            @if($showDropdown && count($users) > 0)
                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                    @foreach($users as $user)
                        <button
                            wire:click="selectUser({{ $user->id }})"
                            class="w-full px-4 py-2 text-left hover:bg-blue-50 focus:bg-blue-50 focus:outline-none"
                        >
                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Información del Usuario Seleccionado (CON FOTO) -->
        @if($selectedUser)
            @php
                $fotoRuta = $selectedUser->documentacionAltas?->arch_foto;
            @endphp
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200 flex items-center gap-4">
                <div class="flex-shrink-0">
                    @if($fotoRuta)
                        <img
                            src="{{ asset($fotoRuta) }}"
                            alt="Foto de {{ $selectedUser->name }}"
                            class="w-16 h-16 rounded-full object-cover border-2 border-blue-300 bg-gray-200"
                            onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjNmI3MjgwIiBzdHJva2Utd2lkdGg9IjIiPjxwYXRoIGQ9Ik0xNiA3YTQgNCAwIDExLTggMCA0IDQgMCAwMTggMHpNMTIgMTRhNyA3IDAgMDAtNyA3aDE0YTcgNyAwIDAwLTctN3oiLz48L3N2Zz4=';"
                        >
                    @else
                        <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center border-2 border-blue-300">
                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-blue-900">{{ $selectedUser->name }}</h3>
                    <p class="text-sm text-blue-700">{{ $selectedUser->email }}</p>
                </div>
            </div>

            <!-- Tabla de Vacaciones (CON COLORES POR PERIODO) -->
            @if(count($vacaciones) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Periodo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha Inicio</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha Fin</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Días por Derecho</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Días Disponibles</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Días Solicitados</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Días Restantes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php
                                $periodColors = [
                                    'bg-blue-50 border-l-4 border-blue-300',
                                    'bg-emerald-50 border-l-4 border-emerald-300',
                                    'bg-violet-50 border-l-4 border-violet-300',
                                    'bg-amber-50 border-l-4 border-amber-300',
                                    'bg-rose-50 border-l-4 border-rose-300',
                                    'bg-cyan-50 border-l-4 border-cyan-300',
                                ];
                            @endphp
                            @foreach($vacaciones as $vacacion)
                                @php
                                    $colorKey = abs(crc32($vacacion->periodo)) % count($periodColors);
                                    $colorClass = $periodColors[$colorKey];
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors {{ $colorClass }}">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $vacacion->periodo }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ ucfirst($vacacion->tipo) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($vacacion->fecha_inicio)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($vacacion->fecha_fin)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vacacion->dias_por_derecho }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vacacion->dias_disponibles }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vacacion->dias_solicitados }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ $vacacion->dias_disponibles - $vacacion->dias_solicitados }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- RESUMEN EN FORMATO TABLA (HOMOGÉNEO) -->
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Resumen por Periodo
                    </h4>

                    @php
                        $resumenPorPeriodo = $vacaciones->groupBy('periodo')->map(function($items, $periodo) {
                            return [
                                'periodo' => $periodo,
                                'dias_por_derecho' => $items->first()->dias_por_derecho,
                                'dias_disponibles' => $items->first()->dias_disponibles,
                                'dias_solicitados' => $items->sum('dias_solicitados'),
                                'dias_restantes' => $items->first()->dias_disponibles - $items->sum('dias_solicitados'),
                                'aprobadas' => $items->where('estatus', 'Aceptada')->count(),
                                'pendientes' => $items->where('estatus', 'Pendiente')->count(),
                            ];
                        });
                    @endphp

                    <div class="overflow-x-auto border border-gray-300 rounded-lg">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Periodo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Días por Derecho</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Días Solicitados</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Días Restantes</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aceptadas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Pendientes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($resumenPorPeriodo as $resumen)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $resumen['periodo'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $resumen['dias_por_derecho'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $resumen['dias_solicitados'] }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold {{ $resumen['dias_restantes'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $resumen['dias_restantes'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">{{ $resumen['aprobadas'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">{{ $resumen['pendientes'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No se encontraron solicitudes de vacaciones para este usuario.</p>
                </div>
            @endif
        @else
            <div class="text-center py-12 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <p class="mt-2">Busca y selecciona un usuario para ver su kárdex de vacaciones</p>
            </div>
        @endif

        <!-- Botón Regresar -->
        <div class="mt-6 flex justify-center">
            <button
                onclick="history.back()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Regresar</span>
            </button>
        </div>
    </div>
</div>
