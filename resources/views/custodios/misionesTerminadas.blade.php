<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Misiones Finalizadas
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Listado de misiones que han sido completadas
                            </p>
                        </div>

                        @if(isset($misiones) && $misiones->isNotEmpty())
                            <div class="flex items-center space-x-2">
                                <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-3 py-1 rounded-full">
                                    <span class="text-sm font-medium">{{ $misiones->total() }}</span>
                                    <span class="text-xs">misiones</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($misiones->isEmpty())
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay misiones finalizadas</h3>
                        <p class="text-gray-500 dark:text-gray-400">Actualmente no hay misiones completadas registradas en el sistema.</p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            #
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                                </svg>
                                                Cliente
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Ubicación
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Fecha Inicio
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Fecha Fin
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Acciones
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($misiones as $mision)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $loop->iteration + ($misiones->currentPage() - 1) * $misiones->perPage() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $mision->cliente }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 dark:text-white max-w-xs">
                                                    @forelse($mision->ubicacion as $ubicacion)
                                                        <div class="mb-1 last:mb-0">{{ $ubicacion['direccion'] ?? 'Sin dirección' }}</div>
                                                    @empty
                                                        <span class="text-gray-400 dark:text-gray-500">Sin ubicaciones</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ \Carbon\Carbon::parse($mision->fecha_inicio)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ \Carbon\Carbon::parse($mision->fecha_fin)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <div class="flex items-center justify-center gap-2">
                                                    {{-- Botón Ver Itinerarios --}}
                                                    @if($mision->itinerarios && !empty($mision->itinerarios))
                                                        {{-- Estado: Habilitado --}}
                                                        <a href="{{ route('misiones.itinerarios.show', $mision->id) }}"
                                                            class="inline-flex items-center justify-center p-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition duration-200 shadow-sm cursor-pointer"
                                                            title="Ver detalle de itinerarios">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        </a>
                                                    @else
                                                        <div class="relative inline-flex group">
                                                            <button type="button"
                                                                disabled
                                                                class="inline-flex items-center justify-center p-2 rounded-lg bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed transition duration-200 shadow-sm"
                                                                title="Aún no hay itinerarios ingresados">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                </svg>
                                                            </button>

                                                            {{-- Tooltip estilizado --}}
                                                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2.5 py-1 text-xs font-medium text-white bg-gray-900 dark:bg-gray-700 rounded-md shadow-sm opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                                                Aún no hay itinerarios de esta misión
                                                                {{-- Flecha inferior del tooltip --}}
                                                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></span>
                                                            </span>
                                                        </div>
                                                    @endif

                                                    {{-- Botón 2: Gastos --}}
                                                    @php
                                                        $hasGastos = false;
                                                        $agentesIds = is_string($mision->agentes_id)
                                                            ? json_decode($mision->agentes_id, true)
                                                            : $mision->agentes_id;

                                                        if (is_array($agentesIds) && !empty($agentesIds)) {
                                                            $hasGastos = \App\Models\Gastos::whereIn('user_id', $agentesIds)
                                                                ->whereBetween('Fecha', [$mision->fecha_inicio, $mision->fecha_fin])
                                                                ->exists();
                                                        }
                                                    @endphp

                                                    @if($hasGastos)
                                                        <a href="{{ route('misiones.gastos.show', $mision->id) }}"
                                                            class="inline-flex items-center justify-center p-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white transition duration-200 shadow-sm cursor-pointer"
                                                            title="Ver gastos de la misión">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </a>
                                                    @else
                                                        <div class="relative inline-flex group">
                                                            <button type="button" disabled
                                                                class="inline-flex items-center justify-center p-2 rounded-lg bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed transition duration-200 shadow-sm">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            </button>
                                                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2.5 py-1 text-xs font-medium text-white bg-gray-900 dark:bg-gray-700 rounded-md shadow-sm opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                                                Sin gastos registrados
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="text-gray-500 dark:text-gray-400">No hay misiones finalizadas aún.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($misiones->hasPages())
                        <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Mostrando
                                <span class="font-medium">{{ $misiones->firstItem() }}</span>
                                a
                                <span class="font-medium">{{ $misiones->lastItem() }}</span>
                                de
                                <span class="font-medium">{{ $misiones->total() }}</span>
                                misiones
                            </div>

                            <div class="flex items-center space-x-1">
                                @if ($misiones->onFirstPage())
                                    <span class="px-3 py-1 text-gray-400 dark:text-gray-500 rounded">&laquo;</span>
                                @else
                                    <a href="{{ $misiones->previousPageUrl() }}" class="px-3 py-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">&laquo;</a>
                                @endif

                                @foreach ($misiones->links()->elements[0] as $page => $url)
                                    @if ($page == $misiones->currentPage())
                                        <span class="px-3 py-1 bg-green-500 text-white rounded">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="px-3 py-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ $page }}</a>
                                    @endif
                                @endforeach

                                @if ($misiones->hasMorePages())
                                    <a href="{{ $misiones->nextPageUrl() }}" class="px-3 py-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">&raquo;</a>
                                @else
                                    <span class="px-3 py-1 text-gray-400 dark:text-gray-500 rounded">&raquo;</span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif

                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-center">
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
