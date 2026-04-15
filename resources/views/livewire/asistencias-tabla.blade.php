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

    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
        <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">Simbología</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-xs">
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-green-200 dark:bg-green-900/40 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">A: Asistió</span>
            </div>
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-red-200 dark:bg-red-900/40 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">F: Falta</span>
            </div>
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-green-200 dark:bg-green-900/40 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">FJ: Falta Justificada</span>
            </div>
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-yellow-200 dark:bg-yellow-900/40 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">D: Descanso</span>
            </div>
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-blue-200 dark:bg-blue-900/40 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">V: Vacaciones</span>
            </div>
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-orange-100 dark:bg-orange-900/30 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">Vacío: Sin registro</span>
            </div>
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-gray-200 dark:bg-gray-700 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">TE: Tiempo Extra</span>
            </div>
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-purple-200 dark:bg-purple-900/40 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">PE-CG: Permiso Especial con Goce</span>
            </div>
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-gray-200 dark:bg-gray-700 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">PE-SG: Permiso Especial sin Goce</span>
            </div>
            <!-- Nuevo: Retardo -->
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-yellow-200 dark:bg-yellow-900/40 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">Rxx: Retardo (xx minutos)</span>
            </div>
        </div>
        <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
            <strong>Turnos:</strong> Día (D), Tarde (T), Noche (N). Ej: D/T = Asistió en Día y Tarde.
        </p>
    </div>

    @if($usuarios->isEmpty())
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            No hay datos para mostrar con los filtros actuales.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th class="sticky-first-col px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">No.</th>
                        <th class="sticky-second-col px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Nombre</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Sueldo Qna</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">T.Extra</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Sueldo Qna</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">ASISTENCIAS</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">DESCANSOS</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">PERM.CG</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">PERM.SG</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">TE.HRS</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">FJ</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">FALTAS</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">INC</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">VACACI</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Punto</th>

                        <!-- Nuevas columnas de nómina -->
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Sueldo Diario</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Días Pagados</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Bono Asist.</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Bono Punt.</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Hrs Extra</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Subtotal</th>

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
                            <th colspan="4" class="px-2 py-2 text-center text-xs font-bold bg-orange-100 dark:bg-orange-900/30 border-l border-r border-gray-300 dark:border-gray-600">
                                {{ $diaEspanol }}<br>{{ $numeroDia }}
                            </th>
                        @endforeach
                    </tr>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        @for($i = 0; $i < 21; $i++)
                            <th class="border-r border-gray-300 dark:border-gray-600"></th>
                        @endfor
                        @foreach($fechas as $fecha)
                            <th class="px-1 py-1 text-center text-xs border-r border-gray-300 dark:border-gray-600">Día</th>
                            <th class="px-1 py-1 text-center text-xs border-r border-gray-300 dark:border-gray-600">Tarde</th>
                            <th class="px-1 py-1 text-center text-xs border-r border-gray-300 dark:border-gray-600">Noche</th>
                            <th class="px-1 py-1 text-center text-xs">TE</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $user)
                        @php
                            $faltas = 0;
                            $faltasJustificadasCount = 0;
                            $vacaciones = 0;
                            $descansos = 0;
                            $asistenciasCount = 0;
                            $permisosConGoce = 0;
                            $permisosSinGoce = 0;
                            $totalHorasExtra = 0;
                            $incidencias = [];
                            foreach($fechas as $f) {
                                $asistio = false;
                                $falto = false;
                                $descanso = false;
                                $asistencia = $asistenciasIndexadas->get($f);

                                if ($asistencia) {
                                    $enlistados = json_decode($asistencia->elementos_enlistados, true) ?? [];
                                    $faltantes = json_decode($asistencia->faltas, true) ?? [];
                                    $descansantes = json_decode($asistencia->descansos, true) ?? [];

                                    $asistio = in_array($user->id, $enlistados);
                                    $falto = in_array($user->id, $faltantes);
                                    $descanso = in_array($user->id, $descansantes);
                                }

                                $dia = '';
                                $tarde = '';
                                $noche = '';

                                $permiso = $permisosPorUsuario[$user->id][$f] ?? null;

                                if ($permiso) {
                                    $codigo = $permiso['con_goce'] ? 'PE-CG' : 'PE-SG';
                                    $dia = $codigo;
                                    $tarde = '';
                                    $noche = '';
                                    if ($permiso['con_goce']) {
                                        $permisosConGoce++;
                                    } else {
                                        $permisosSinGoce++;
                                    }
                                } elseif (in_array($f, $vacacionesPorUsuario[$user->id] ?? [])) {
                                    $dia = 'V';
                                    $vacaciones++;
                                } elseif ($descanso) {
                                    $dia = 'D';
                                    $descansos++;
                                } elseif ($falto) {
                                    $esJustificada = $faltasJustificadas[$user->id][$f] ?? false;
                                    if ($esJustificada) {
                                        $dia = 'FJ';
                                        $faltasJustificadasCount++;
                                    } else {
                                        $dia = 'F';
                                        $faltas++;
                                    }
                                } elseif ($asistio) {
                                    $turnosRegistro = json_decode($asistencia->turnos, true) ?? [];
                                    $turnosUsuario = $turnosRegistro[$user->id] ?? [];

                                    if (in_array('dia', $turnosUsuario)) $dia = 'A';
                                    if (in_array('tarde', $turnosUsuario)) $tarde = 'A';
                                    if (in_array('noche', $turnosUsuario)) $noche = 'A';
                                    $asistenciasCount++;

                                    $minutosRetardo = $retardosPorUsuario[$user->id][$f] ?? 0;
                                    if ($minutosRetardo > 0) {
                                        $dia = 'R' . $minutosRetardo;
                                    }
                                }

                                $incidencias[$f] = [$dia, $tarde, $noche];
                            }

                            $sueldoBase = $this->normalize($user->rol) === 'guardia' ? 5500 : 5500;
                            $totalHorasExtra = array_sum($horasExtrasPorUsuario[$user->id] ?? []);
                            $pagoHorasExtra = $totalHorasExtra > 0 ? (940 / 24) * $totalHorasExtra : 0;

                            // Datos de nómina para este usuario
                            $nomina = $nominaPorUsuario[$user->id] ?? null;
                        @endphp
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50
                        @if(in_array($user->id, $usuariosConAlerta))
                            bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500
                        @endif">
                            <td class="sticky-first-col px-3 py-2 text-sm border-r border-gray-300 dark:border-gray-600">{{ $user->id }}</td>
                            <td class="sticky-second-col px-3 py-2 text-sm border-r border-gray-300 dark:border-gray-600 @if(in_array($user->id, $usuariosConAlerta)) bg-red-200 dark:bg-red-900/40 @endif ">
                                {{ $user->name }}
                                @if(in_array($user->id, $usuariosConAlerta))
                                    <span class="ml-1 text-red-600 dark:text-red-400" title="Últimas 2 asistencias fueron faltas">
                                        ⚠️
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm bg-yellow-100 dark:bg-yellow-900/30 border-r border-gray-300 dark:border-gray-600">${{ number_format($sueldoBase, 2) }}</td>
                            <td class="px-3 py-2 text-sm bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">{{ $totalHorasExtra }}</td>
                            <td class="px-3 py-2 text-sm {{ $totalHorasExtra > 0 ? 'bg-yellow-100 dark:bg-yellow-900/30' : '' }} border-r border-gray-300 dark:border-gray-600">
                                {{ $totalHorasExtra > 0 ? '$' . number_format($pagoHorasExtra, 2) : '0' }}
                            </td>
                            <td class="px-3 py-2 text-sm bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">
                                {{ $asistenciasCount }}
                            </td>
                            <td class="px-3 py-2 text-sm bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">
                                {{ $descansos }}
                            </td>
                            <td class="px-3 py-2 text-sm bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">
                                {{ $permisosConGoce }}
                            </td>
                            <td class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">
                                {{ $permisosSinGoce }}
                            </td>
                            <td class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">
                                {{ $totalHorasExtra }}
                            </td>
                            <td class="px-3 py-2 text-sm bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">
                                {{ $faltasJustificadasCount }}
                            </td>
                            <td class="px-3 py-2 text-sm bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">
                                {{ $faltas }}
                            </td>
                            <td class="px-3 py-2 text-sm bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">
                                0
                            </td>
                            <td class="px-3 py-2 text-sm bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">
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
                            <td class="px-3 py-2 text-sm border-r border-gray-300 dark:border-gray-600">{{ $puntoMostrar }}</td>

                            <!-- Nuevas columnas de nómina -->
                            <td class="px-3 py-2 text-sm border-r border-gray-300 dark:border-gray-600">
                                ${{ number_format($nomina['sueldo_diario'] ?? 0, 2) }}
                            </td>
                            <td class="px-3 py-2 text-sm border-r border-gray-300 dark:border-gray-600">
                                {{ $nomina['dias_pagados']['total'] ?? 0 }}
                            </td>
                            <td class="px-3 py-2 text-sm border-r border-gray-300 dark:border-gray-600">
                                @if(($nomina['bonos']['asistencia']['aplica'] ?? false))
                                    +${{ number_format($nomina['bonos']['asistencia']['monto'] ?? 0, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm border-r border-gray-300 dark:border-gray-600">
                                @if(($nomina['bonos']['puntualidad']['aplica'] ?? false))
                                    +${{ number_format($nomina['bonos']['puntualidad']['monto'] ?? 0, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm border-r border-gray-300 dark:border-gray-600">
                                ${{ number_format($nomina['horas_extra']['monto'] ?? 0, 2) }}
                            </td>
                            <td class="px-3 py-2 text-sm font-bold bg-green-50 dark:bg-green-900/20 border-r border-gray-300 dark:border-gray-600">
                                ${{ number_format($nomina['subtotal_percepciones'] ?? 0, 2) }}
                            </td>

                            @foreach($fechas as $f)
                                @php
                                    $turnos = $incidencias[$f] ?? ['', '', ''];
                                    $dia = $turnos[0];
                                    $tarde = $turnos[1];
                                    $noche = $turnos[2];
                                    $horasExtra = $horasExtrasPorUsuario[$user->id][$f] ?? 0;
                                @endphp
                                <td class="px-1 py-1 text-center text-sm font-medium border-r border-gray-300 dark:border-gray-600
                                    @if(str_starts_with($dia, 'R')) bg-yellow-200 dark:bg-yellow-900/40
                                    @elseif($dia === 'F') bg-red-200 dark:bg-red-900/40
                                    @elseif($dia === 'FJ') bg-green-200 dark:bg-green-900/40
                                    @elseif($dia === 'V') bg-blue-200 dark:bg-blue-900/40
                                    @elseif($dia === 'D') bg-yellow-200 dark:bg-yellow-900/40
                                    @elseif($dia === 'A') bg-green-200 dark:bg-green-900/40
                                    @elseif($dia === 'PE-CG') bg-purple-200 dark:bg-purple-900/40
                                    @elseif($dia === 'PE-SG') bg-gray-200 dark:bg-gray-700
                                    @else bg-orange-100 dark:bg-orange-900/30 @endif">
                                    {{ $dia }}
                                </td>
                                <td class="px-1 py-1 text-center text-sm font-medium border-r border-gray-300 dark:border-gray-600
                                    @if($tarde === 'A') bg-green-200 dark:bg-green-900/40
                                    @elseif($tarde === 'PE-CG') bg-purple-200 dark:bg-purple-900/40
                                    @elseif($tarde === 'PE-SG') bg-gray-200 dark:bg-gray-700
                                    @else bg-orange-100 dark:bg-orange-900/30 @endif">
                                    {{ $tarde }}
                                </td>
                                <td class="px-1 py-1 text-center text-sm font-medium border-r border-gray-300 dark:border-gray-600
                                    @if($noche === 'A') bg-green-200 dark:bg-green-900/40
                                    @elseif($noche === 'PE-CG') bg-purple-200 dark:bg-purple-900/40
                                    @elseif($noche === 'PE-SG') bg-gray-200 dark:bg-gray-700
                                    @else bg-orange-100 dark:bg-orange-900/30 @endif">
                                    {{ $noche }}
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
