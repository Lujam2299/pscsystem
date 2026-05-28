<x-app-layout>
    <x-navbar></x-navbar>

    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="mx-auto max-w-7xl">

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                {{-- Header --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Gastos - Misión #{{ $mision->id }}
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
                        @endif
                    </div>

                    <div class="flex gap-2">
                        {{-- Botón Descargar PDF --}}
                        <a href="{{ route('misiones.gastos.pdf', $mision->id) }}"
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

                {{-- Tarjetas de Resumen --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white shadow-lg">
                        <div class="text-xs font-medium opacity-80 uppercase tracking-wide">Total General</div>
                        <div class="text-2xl font-bold mt-1">${{ number_format($totalGeneral, 2) }}</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white shadow-lg">
                        <div class="text-xs font-medium opacity-80 uppercase tracking-wide">Viáticos</div>
                        <div class="text-2xl font-bold mt-1">${{ number_format($totalViaticos, 2) }}</div>
                        <div class="text-xs opacity-75">{{ $gastos->where('Tipo', 'Viaticos')->count() }} registros</div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-4 text-white shadow-lg">
                        <div class="text-xs font-medium opacity-80 uppercase tracking-wide">Gasolina</div>
                        <div class="text-2xl font-bold mt-1">${{ number_format($totalGasolina, 2) }}</div>
                        <div class="text-xs opacity-75">{{ number_format($totalLitros, 2) }} L • {{ number_format($totalKm, 2) }} Km</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-4 text-white shadow-lg">
                        <div class="text-xs font-medium opacity-80 uppercase tracking-wide">Total Gastos</div>
                        <div class="text-2xl font-bold mt-1">{{ $gastos->count() }}</div>
                        <div class="text-xs opacity-75">en el período</div>
                    </div>
                </div>

                {{-- Tabla de Gastos --}}
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Detalles</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Evidencia</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($gastos as $index => $gasto)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                        {{-- # --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>

                                        {{-- Agente --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-3">
                                                    <span class="text-xs font-semibold text-purple-700 dark:text-purple-300">
                                                        {{ strtoupper(substr($agentesNombres[$gasto->user_id] ?? 'A', 0, 2)) }}
                                                    </span>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $agentesNombres[$gasto->user_id] ?? 'Agente #' . $gasto->user_id }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Fecha y Hora --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ \Carbon\Carbon::parse($gasto->Fecha)->format('d/m/Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $gasto->Hora ? \Carbon\Carbon::parse($gasto->Hora)->format('H:i') : 'N/A' }}
                                            </div>
                                        </td>

                                        {{-- Tipo --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($gasto->Tipo === 'Gasolina')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                    Gasolina
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Viáticos
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Monto --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                            ${{ number_format($gasto->Monto, 2) }}
                                        </td>

                                        {{-- Detalles --}}
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                            @if($gasto->Tipo === 'Gasolina')
                                                <div class="space-y-1">
                                                    <div><span class="text-gray-400">Litros:</span> {{ number_format($gasto->Litros, 2) }} L</div>
                                                    <div><span class="text-gray-400">Km:</span> {{ number_format($gasto->Km, 2) }} Km</div>
                                                    @if($gasto->Gasolina_antes_carga || $gasto->Gasolina_despues_carga)
                                                        <div class="text-xs text-gray-400">
                                                            {{ number_format($gasto->Gasolina_antes_carga, 1) }} → {{ number_format($gasto->Gasolina_despues_carga, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">Viático</span>
                                            @endif
                                        </td>

                                        {{-- Evidencia --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($gasto->Evidencia)
                                                <a href="{{ asset('storage/' . $gasto->Evidencia) }}"
                                                    target="_blank"
                                                    class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Ver
                                                </a>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Info adicional --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Período:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($mision->fecha_inicio)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($mision->fecha_fin)->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Total registros:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ $gastos->count() }}</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Agentes con gastos:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ $gastos->unique('user_id')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
