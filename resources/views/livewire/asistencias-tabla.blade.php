<div>
    <style>
        .sticky-first-col {
            position: sticky;
            left: 0;
            background-color: #fff;
            z-index: 10;
            box-shadow: inset -2px 0 0 #d1d5db;
        }
        .dark .sticky-first-col {
            background-color: #1f2937;
            box-shadow: inset -2px 0 0 #374151;
        }
        .sticky-second-col {
            position: sticky;
            left: 40px;
            background-color: #fff;
            z-index: 10;
            box-shadow: inset -2px 0 0 #d1d5db;
        }
        .dark .sticky-second-col {
            background-color: #1f2937;
            box-shadow: inset -2px 0 0 #374151;
        }
    </style>

    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
            <div>
                <label for="punto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Punto
                </label>
                <select wire:model.live="punto" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-800 dark:text-white">
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

            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Fecha Inicio
                </label>
                <input type="date"
                       wire:model.live="fecha_inicio"
                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-800 dark:text-white">
            </div>

            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Fecha Fin
                </label>
                <input type="date"
                       wire:model.live="fecha_fin"
                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label for="tipo_filtro" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Tipo de Registro
                </label>
                <select wire:model.live="tipoFiltro"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Todos</option>
                    <option value="asistencias">Asistencias</option>
                    <option value="faltas">Faltas</option>
                    <option value="descansos">Descansos</option>
                </select>
            </div>
            <form method="GET" action="{{ route('exportar.asistencias') }}" class="mb-4 mt-7">
                <input type="hidden" name="punto" value="{{ $punto }}">
                <input type="hidden" name="fecha_inicio" value="{{ $fecha_inicio }}">
                <input type="hidden" name="fecha_fin" value="{{ $fecha_fin }}">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                    Generar Reporte (Excel)
                </button>
            </form>
        </div>
    </div>

    @if($usuarios->isEmpty())
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            No hay datos para mostrar con los filtros actuales.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th class="sticky-first-col px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">No.</th>
                        <th class="sticky-second-col px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Sueldo Qna</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">T.Extra</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Sueldo Qna</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">FJ</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">FALTAS</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">INC</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">VACACI</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Punto</th>

                        @foreach($fechas as $fecha)
                            @php
                                $diaEspanol = [
                                    'Monday' => 'Lunes',
                                    'Tuesday' => 'Martes',
                                    'Wednesday' => 'Miércoles',
                                    'Thursday' => 'Jueves',
                                    'Friday' => 'Viernes',
                                    'Saturday' => 'Sábado',
                                    'Sunday' => 'Domingo',
                                ][Carbon\Carbon::parse($fecha)->format('l')];
                                $numeroDia = Carbon\Carbon::parse($fecha)->format('d');
                            @endphp
                            <th colspan="2" class="px-2 py-2 text-center text-xs font-bold bg-orange-100 dark:bg-orange-900/30 border border-gray-300 dark:border-gray-600">
                                {{ $diaEspanol }}<br>{{ $numeroDia }}
                            </th>
                        @endforeach
                    </tr>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        @for($i = 0; $i < 10; $i++)
                            <th></th>
                        @endfor
                        @foreach($fechas as $fecha)
                            <th class="px-1 py-1 text-center text-xs">A/F</th>
                            <th class="px-1 py-1 text-center text-xs">TE</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $user)
                        @php
                            $faltas = 0;
                            $vacaciones = 0;
                            $descansos = 0;
                            $incidencias = [];
                            foreach($fechas as $f) {
                                $asistio = false;
                                $falto = false;
                                $descanso = false;
                                $asistencia = $asistenciasIndexadas->get($f);

                                if ($asistencia) {
                                    if ($asistencia->elementos_enlistados) {
                                        $enlistados = json_decode($asistencia->elementos_enlistados, true);
                                        $asistio = in_array($user->id, $enlistados);
                                    }
                                    if ($asistencia->faltas) {
                                        $faltantes = json_decode($asistencia->faltas, true);
                                        $falto = in_array($user->id, $faltantes);
                                    }
                                    if ($asistencia->descansos) {
                                        $descansantes = json_decode($asistencia->descansos, true);
                                        $descanso = in_array($user->id, $descansantes);
                                    }
                                }

                                if (in_array($f, $vacacionesPorUsuario[$user->id] ?? [])) {
                                    $incidencias[$f] = 'V';
                                    $vacaciones++;
                                } elseif ($descanso) {
                                    $incidencias[$f] = 'D';
                                    $descansos++;
                                } elseif ($asistio) {
                                    $incidencias[$f] = 'A';
                                } elseif ($falto) {
                                    $incidencias[$f] = 'F';
                                    $faltas++;
                                } else {
                                    $incidencias[$f] = '';
                                }
                            }

                            $sueldoBase = $this->normalize($user->rol) === 'guardia' ? 5500 : 5500;
                            $totalHorasExtra = array_sum($horasExtrasPorUsuario[$user->id] ?? []);
                            $pagoHorasExtra = $totalHorasExtra > 0 ? (940 / 24) * $totalHorasExtra : 0;
                        @endphp
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50
    @if(in_array($user->id, $usuariosConAlerta))
        bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500
    @endif">
                            <td class="sticky-first-col px-3 py-2 text-sm">{{ $user->id }}</td>
                            <td class="sticky-second-col px-3 py-2 text-sm @if(in_array($user->id, $usuariosConAlerta)) bg-red-200 dark:bg-red-900/40 @endif ">
                                {{ $user->name }}
                                @if(in_array($user->id, $usuariosConAlerta))
                                    <span class="ml-1 text-red-600 dark:text-red-400" title="Últimas 2 asistencias fueron faltas">
                                        ⚠️
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm bg-yellow-100 dark:bg-yellow-900/30">${{ number_format($sueldoBase, 2) }}</td>
                            <td class="px-3 py-2 text-sm bg-green-100 dark:bg-red-green/40 text-green-800 dark:text-green-200 font-medium rounded">{{ $totalHorasExtra }}</td>
                            <td class="px-3 py-2 text-sm {{ $totalHorasExtra > 0 ? 'bg-yellow-100 dark:bg-yellow-900/30' : '' }}">
                                {{ $totalHorasExtra > 0 ? '$' . number_format($pagoHorasExtra, 2) : '0' }}
                            </td>
                            <td class="px-3 py-2 text-sm">0</td>
                            <td class="px-3 py-2 text-sm bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 font-medium rounded">
                                {{ $faltas }}
                            </td>
                            <td class="px-3 py-2 text-sm bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-200 font-medium rounded">
                                0
                            </td>
                            <td class="px-3 py-2 text-sm bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 font-medium rounded">
                                {{ $vacaciones }}
                            </td>
                            @php

                                $puntoMostrar = $user->punto;
                                $puntoAsignado = null;

                                foreach($fechas as $f) {
                                    $asistencia = $asistenciasIndexadas->get($f);
                                    if ($asistencia && isset($puntosAsignadosMap[$f][$user->id])) {
                                        $puntoAsignado = $puntosAsignadosMap[$f][$user->id];
                                        break;
                                    }
                                }

                                if ($puntoAsignado) {
                                    $puntoMostrar = $puntoAsignado;
                                }
                            @endphp
                            <td class="px-3 py-2 text-sm">{{ $puntoMostrar }}</td>

                            @foreach($fechas as $f)
                                @php
                                    $valor = $incidencias[$f] ?? '';
                                    $horasExtra = $horasExtrasPorUsuario[$user->id][$f] ?? 0;
                                @endphp
                                <td class="px-1 py-1 text-center text-sm font-medium
                                    @if($valor === 'F') bg-red-200 dark:bg-red-900/40
                                    @elseif($valor === 'V') bg-blue-200 dark:bg-blue-900/40
                                    @elseif($valor === 'A') bg-green-200 dark:bg-green-900/40
                                    @elseif($valor === 'D') bg-yellow-200 dark:bg-yellow-900/40
                                    @elseif($valor === '') bg-orange-100 dark:bg-orange-900/30
                                    @else bg-white dark:bg-gray-800 @endif">
                                    {{ $valor }}
                                </td>
                                <td class="px-1 py-1 text-center text-sm {{ $horasExtra > 0 ? 'font-bold' : '' }}">
                                    {{ $horasExtra }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
