<div>
    <style>
        .sticky-first-col { position: sticky; left: 0; background-color: #fff; z-index: 10; box-shadow: inset -2px 0 0 #d1d5db; }
        .dark .sticky-first-col { background-color: #1f2937; box-shadow: inset -2px 0 0 #374151; }
        .sticky-second-col { position: sticky; left: 40px; background-color: #fff; z-index: 10; box-shadow: inset -2px 0 0 #d1d5db; }
        .dark .sticky-second-col { background-color: #1f2937; box-shadow: inset -2px 0 0 #374151; }
    </style>

    <!-- Filtros en una sola fila -->
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- Punto -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Punto</label>
                <select wire:model.live="punto" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Todos</option>
                    @foreach($subpuntosMap as $puntoGeneral => $subpuntos)
                        <optgroup label="{{ $puntoGeneral }}">
                            <option value="{{ $puntoGeneral }}">(Todos) {{ $puntoGeneral }}</option>
                            @foreach($subpuntos as $subpunto)
                                <option value="{{ $subpunto['nombre'] }}">{{ $subpunto['nombre'] }}
                                    @if($subpunto['codigo'])
                                        ({{ str_pad($subpunto['codigo'], 3, '0', STR_PAD_LEFT) }})
                                    @endif
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <!-- Fecha Inicio -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Inicio</label>
                <input type="date" wire:model.live="fecha_inicio" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
            </div>

            <!-- Fecha Fin -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Fin</label>
                <input type="date" wire:model.live="fecha_fin" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
            </div>

            <!-- Botón de Exportar Excel -->
            <div class="flex items-end">
                @if($fecha_inicio && $fecha_fin)
                    <form method="GET" action="{{ route('exportar.destajos') }}" target="_blank" class="w-full">
                        <input type="hidden" name="punto" value="{{ $punto }}">
                        <input type="hidden" name="fecha_inicio" value="{{ $fecha_inicio }}">
                        <input type="hidden" name="fecha_fin" value="{{ $fecha_fin }}">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm h-[42px]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Exportar
                        </button>
                    </form>
                @else
                    <button disabled class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed opacity-70 h-[42px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar
                    </button>
                @endif
            </div>

        </div>
    </div>

    <!-- Leyenda Visual -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
        <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">Simbología Destajos</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 text-xs">
            <div class="flex items-center"><span class="w-3 h-3 rounded-sm bg-green-200 dark:bg-green-900/40 mr-2"></span><span>A: Asistencia (Paga)</span></div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-sm bg-yellow-200 dark:bg-yellow-900/40 mr-2"></span><span>D: Descanso (Paga)</span></div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-sm bg-red-200 dark:bg-red-900/40 mr-2"></span><span>F: Falta</span></div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-sm bg-red-300 dark:bg-red-900/60 mr-2"></span><span>I: Incapacidad</span></div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-sm bg-blue-200 dark:bg-blue-900/40 mr-2"></span><span>V: Vacaciones</span></div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-sm bg-purple-200 dark:bg-purple-900/40 mr-2"></span><span>PE: Permiso C/G</span></div>
        </div>
    </div>

    @if($usuarios->isEmpty())
        <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-lg shadow">
            Selecciona un punto y rango de fechas.
        </div>
    @else
        <div class="overflow-x-auto shadow-lg rounded-lg">
            <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th class="sticky-first-col px-3 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase border-r">No.</th>
                        <th class="sticky-second-col px-3 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase border-r">Nombre</th>

                        <!-- Columnas de Conteo Solicitadas -->
                        <th class="px-2 py-2 text-center text-xs font-bold text-green-700 dark:text-green-400 uppercase border-r bg-green-50 dark:bg-green-900/10" title="Asistencias + Descansos">Días Lab.</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-yellow-700 dark:text-yellow-400 uppercase border-r bg-yellow-50 dark:bg-yellow-900/10">Desc.</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-red-700 dark:text-red-400 uppercase border-r bg-red-50 dark:bg-red-900/10">Faltas</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-orange-700 dark:text-orange-400 uppercase border-r bg-orange-50 dark:bg-orange-900/10">Incap.</th>

                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase border-r">Tarifa Diaria</th>
                        <th class="px-2 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400 uppercase border-r bg-green-50 dark:bg-green-900/20">TOTAL DESTAJO</th>

                        @foreach($fechas as $fecha)
                            <th class="px-1 py-2 text-center text-xs font-bold bg-gray-50 dark:bg-gray-700 border-l border-r border-gray-300 dark:border-gray-600 min-w-[30px]">
                                {{ Carbon\Carbon::parse($fecha)->format('d') }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $user)
                        @php
                            $data = $destajosPorUsuario[$user->id] ?? [
                                'dias_laborados' => 0,
                                'tarifa_diaria' => 0,
                                'total_monto' => 0,
                                'conteos' => ['descansos'=>0, 'faltas'=>0, 'incapacidades'=>0],
                                'desglose_diario' => []
                            ];
                            $conteos = $data['conteos'];
                        @endphp
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150">
                            <td class="sticky-first-col px-3 py-2 text-sm border-r">{{ $user->id }}</td>
                            <td class="sticky-second-col px-3 py-2 text-sm font-medium border-r text-gray-900 dark:text-white">
                                {{ strtoupper($user->name) }}
                            </td>

                            <!-- Valores de Conteo -->
                            <td class="px-2 py-2 text-center text-sm font-bold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/10 border-r">
                                {{ $data['dias_laborados'] }}
                            </td>
                            <td class="px-2 py-2 text-center text-sm text-yellow-700 dark:text-yellow-400 border-r">
                                {{ $conteos['descansos'] ?? 0 }}
                            </td>
                            <td class="px-2 py-2 text-center text-sm text-red-700 dark:text-red-400 border-r">
                                {{ $conteos['faltas'] ?? 0 }}
                            </td>
                            <td class="px-2 py-2 text-center text-sm text-orange-700 dark:text-orange-400 border-r">
                                {{ $conteos['incapacidades'] ?? 0 }}
                            </td>

                            <td class="px-2 py-2 text-center text-xs text-gray-500 dark:text-gray-400 border-r">
                                ${{ number_format($data['tarifa_diaria'], 2) }}
                            </td>
                            <td class="px-2 py-2 text-sm text-right font-bold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border-r">
                                ${{ number_format($data['total_monto'], 2) }}
                            </td>

                            <!-- Cuadrícula Diaria con Colores -->
                            @foreach($fechas as $f)
                                @php
                                    $codigo = $data['desglose_diario'][$f] ?? '';
                                    $claseBg = 'text-gray-300 dark:text-gray-600'; // Default vacío

                                    if ($codigo === 'A') $claseBg = 'bg-green-200 dark:bg-green-900/40 text-green-800 dark:text-green-200 font-bold';
                                    elseif ($codigo === 'D') $claseBg = 'bg-yellow-200 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200 font-bold';
                                    elseif ($codigo === 'F') $claseBg = 'bg-red-200 dark:bg-red-900/40 text-red-800 dark:text-red-200';
                                    elseif ($codigo === 'I') $claseBg = 'bg-red-300 dark:bg-red-900/60 text-red-900 dark:text-red-100';
                                    elseif ($codigo === 'V') $claseBg = 'bg-blue-200 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200';
                                    elseif (str_starts_with($codigo, 'PE')) $claseBg = 'bg-purple-200 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200 text-[10px]';
                                @endphp
                                <td class="px-1 py-1 text-center text-xs border-r border-gray-300 dark:border-gray-600 {{ $claseBg }}">
                                    {{ $codigo }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    <!-- Totales Generales -->
                    <tr class="bg-gray-200 dark:bg-gray-700 font-bold border-t-2 border-gray-400">
                        <td colspan="2" class="px-3 py-2 text-right text-sm uppercase text-gray-700 dark:text-gray-200">Totales:</td>
                        <td class="px-2 py-2 text-center text-sm text-green-800 dark:text-green-200 bg-green-100 dark:bg-green-900/20 border-r">
                            {{ array_sum(array_column($destajosPorUsuario, 'dias_laborados')) }}
                        </td>
                        <td class="px-2 py-2 text-center text-sm text-yellow-800 dark:text-yellow-200 border-r">
                            {{ array_sum(array_map(fn($d) => $d['conteos']['descansos'] ?? 0, $destajosPorUsuario)) }}
                        </td>
                        <td class="px-2 py-2 text-center text-sm text-red-800 dark:text-red-200 border-r">
                            {{ array_sum(array_map(fn($d) => $d['conteos']['faltas'] ?? 0, $destajosPorUsuario)) }}
                        </td>
                        <td class="px-2 py-2 text-center text-sm text-orange-800 dark:text-orange-200 border-r">
                            {{ array_sum(array_map(fn($d) => $d['conteos']['incapacidades'] ?? 0, $destajosPorUsuario)) }}
                        </td>
                        <td colspan="2" class="border-r"></td>
                        <td class="px-2 py-2 text-right text-sm text-green-800 dark:text-green-200 bg-green-100 dark:bg-green-900/30 border-r">
                            ${{ number_format(array_sum(array_column($destajosPorUsuario, 'total_monto')), 2) }}
                        </td>
                        <td colspan="{{ count($fechas) }}"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>
