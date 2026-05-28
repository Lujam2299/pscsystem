<x-app-layout>
    <x-navbar></x-navbar>

    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="mx-auto max-w-7xl">

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                {{-- Header --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Itinerarios - Misión #{{ $mision->id }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $mision->tipo_servicio }} • {{ $mision->nombre_clave }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $mision->cliente }}
                        </p>
                        @if($mision->nombres_agentes)
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium text-gray-900 dark:text-white">Agentes:</span>
                                {{ $mision->nombres_agentes }}
                            </p>
                        @else
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 italic">
                                Sin agentes asignados
                            </p>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        {{-- Botón Descargar PDF --}}
                        <a href="{{ route('misiones.itinerarios.pdf', $mision->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow-sm"
                            target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Descargar PDF
                        </a>

                        {{-- Botón Regresar --}}
                        <a href="{{ route('custodios.misionesTerminadas') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Regresar
                        </a>
                    </div>
                </div>

                {{-- Tabla de Eventos del Itinerario --}}
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    @if(empty($eventosPlanos))
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p>No hay eventos registrados en este itinerario.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Agente
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Fecha
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hora</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Evento</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ubicación</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($eventosPlanos as $index => $evento)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                            {{-- # --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $index + 1 }}
                                            </td>

                                            {{-- Agente --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3">
                                                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">
                                                            {{ strtoupper(substr($evento['user_name'], 0, 2)) }}
                                                        </span>
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $evento['user_name'] }}
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- Fecha --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $evento['fecha'] ? \Carbon\Carbon::parse($evento['fecha'])->format('d/m/Y') : 'N/A' }}
                                            </td>

                                            {{-- Hora --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $evento['hora'] ?? 'N/A' }}
                                            </td>

                                            {{-- Descripción/Evento --}}
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white max-w-xs">
                                                <span class="line-clamp-2">{{ $evento['descripcion'] ?? 'Sin descripción' }}</span>
                                            </td>

                                            {{-- Ubicación --}}
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300 max-w-xs">
                                                @if($evento['ubicacion'])
                                                    <div class="flex items-start">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 mt-0.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <span class="truncate">{{ $evento['ubicacion'] }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 italic">Sin ubicación</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Info adicional --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Periodo:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($mision->fecha_inicio)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($mision->fecha_fin)->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Total de eventos:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ count($eventosPlanos) }}</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Agentes activos:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">
                            {{ collect($eventosPlanos)->pluck('user_id')->unique()->count() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
