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
        .payroll-scroll {
            scrollbar-color: #94a3b8 #e2e8f0;
            scrollbar-width: thin;
        }
        .payroll-table thead {
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .payroll-table tbody tr:nth-child(even) td:not(.sticky-first-col):not(.sticky-second-col) {
            background-color: rgba(248, 250, 252, .72);
        }
        .dark .payroll-table tbody tr:nth-child(even) td:not(.sticky-first-col):not(.sticky-second-col) {
            background-color: rgba(31, 41, 55, .45);
        }
    </style>

    <div wire:loading.flex class="fixed inset-0 z-[70] items-center justify-center bg-slate-950/20 backdrop-blur-[1px]">
        <div class="flex items-center gap-3 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-xl dark:bg-gray-800 dark:text-gray-100">
            <svg class="h-5 w-5 animate-spin text-emerald-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            Actualizando concentrado…
        </div>
    </div>

    <section class="mb-6 rounded-2xl border border-slate-200 bg-slate-50/80 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800/60 sm:p-5">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-gray-200">Filtros del periodo</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">Los resultados se actualizan automáticamente.</p>
            </div>
        </div>
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
            <form method="GET" action="{{ route('exportar.asistencias') }}" class="mt-7">
                <input type="hidden" name="punto" value="{{ $punto }}">
                <input type="hidden" name="fecha_inicio" value="{{ $fecha_inicio }}">
                <input type="hidden" name="fecha_fin" value="{{ $fecha_fin }}">
                <button type="submit" @disabled(!$fecha_inicio || !$fecha_fin) class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                    Exportar concentrado
                </button>
            </form>
        </div>
    </section>

    @php
        $resumenEmpleados = $usuarios->count();
        $resumenPendientes = 0;
        $resumenFaltas = 0;
        $resumenHorasExtra = 0;
        $resumenBruto = 0;
        foreach ($usuarios as $resumenUsuario) {
            $resumenNomina = $nominaPorUsuario[$resumenUsuario->id] ?? [];
            $resumenPendientes += $resumenNomina['dias_pagados']['desglose']['pendientes_captura'] ?? 0;
            $resumenFaltas += $resumenNomina['dias_pagados']['desglose']['faltas_injustificadas'] ?? 0;
            $resumenHorasExtra += $resumenNomina['horas_extra']['total_horas'] ?? 0;
            $resumenBruto += $resumenNomina['subtotal_percepciones'] ?? 0;
        }
    @endphp

    @if($fecha_inicio && $fecha_fin)
        <section class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"><div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Empleados</div><div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $resumenEmpleados }}</div><div class="mt-1 text-xs text-slate-500">En el filtro actual</div></div>
            <div class="rounded-xl border {{ $resumenPendientes > 0 ? 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/20' : 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20' }} p-4 shadow-sm"><div class="text-xs font-semibold uppercase tracking-wide {{ $resumenPendientes > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-emerald-700 dark:text-emerald-300' }}">Pendientes</div><div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $resumenPendientes }}</div><div class="mt-1 text-xs text-slate-500">Días por validar</div></div>
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 shadow-sm dark:border-rose-800 dark:bg-rose-900/20"><div class="text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">Faltas</div><div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $resumenFaltas }}</div><div class="mt-1 text-xs text-slate-500">Sin justificar</div></div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 shadow-sm dark:border-sky-800 dark:bg-sky-900/20"><div class="text-xs font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">Tiempo extra</div><div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($resumenHorasExtra, 1) }} h</div><div class="mt-1 text-xs text-slate-500">Acumulado</div></div>
            <div class="col-span-2 rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-800 dark:bg-emerald-900/20 lg:col-span-1"><div class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Bruto estimado</div><div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($resumenBruto, 2) }}</div><div class="mt-1 text-xs text-slate-500">Antes de deducciones</div></div>
        </section>

        @if($resumenPendientes > 0)
            <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200">
                <svg class="mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.667 1.73-3L13.73 4c-.77-1.333-2.69-1.333-3.46 0L3.34 16c-.77 1.333.19 3 1.73 3z"/></svg>
                <div><strong>Concentrado incompleto.</strong> Hay {{ $resumenPendientes }} días sin captura. Se incluyen provisionalmente para evitar descuentos automáticos; deben validarse antes de usar el archivo.</div>
            </div>
        @endif
    @endif

    <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">Simbología</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3 text-xs">
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
            <!-- Nuevo: Incapacidad -->
            <div class="flex items-center">
                <span class="inline-block w-3 h-3 rounded-sm bg-red-200 dark:bg-red-900/40 mr-2"></span>
                <span class="text-gray-700 dark:text-gray-300">I: Incapacidad</span>
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
        <div class="payroll-scroll max-h-[70vh] overflow-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <table class="payroll-table min-w-full bg-white dark:bg-gray-800">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th class="sticky-first-col px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">No.</th>
                        <th class="sticky-second-col px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Nombre</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Sueldo Qna</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">T.Extra</th>
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
                        @for($i = 0; $i < 20; $i++)
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
                            $pendientesCaptura = 0;
                            $incidencias = [];

                            // 🔴 Nueva lógica: cargar incapacidades del usuario
                            $incapacidadesDelUsuario = $incapacidadesPorUsuario[$user->id] ?? [];

                            foreach($fechas as $f) {
                                $asistio = false;
                                $falto = false;
                                $descanso = false;
                                $incapacidad = in_array($f, $incapacidadesDelUsuario); // 👈 Nuevo
                                $asistencia = $this->asistenciaUsuarioFecha($asistenciasIndexadas, $f, $user->id);

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
                                    // ✅ Corrección: Forzar conversión a booleano
                                    $esConGoce = (int) $permiso['con_goce'] === 1;
                                    $codigo = $esConGoce ? 'PE-CG' : 'PE-SG';
                                    $dia = $codigo;
                                    $tarde = '';
                                    $noche = '';
                                    if ($esConGoce) {
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
                                } elseif ($incapacidad) {
                                    $dia = 'I';
                                    $tarde = '';
                                    $noche = '';
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
                                } else {
                                    $dia = 'P';
                                    $pendientesCaptura++;
                                }

                                $incidencias[$f] = [$dia, $tarde, $noche];
                            }

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
                            <td class="sticky-second-col px-3 py-2 text-sm border-r border-gray-300 dark:border-gray-600 @if(in_array($user->id, $usuariosConAlerta)) bg-red-200 dark:bg-red-900/40 @endif">
                                {{ $user->name }}
                                <button
                                    wire:click="mostrarDetalleNomina({{ $user->id }})"
                                    class="ml-2 text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 px-2 py-0.5 rounded"
                                    type="button"
                                    title="Ver detalle de nómina"
                                >
                                    📋
                                </button>
                                @if(in_array($user->id, $usuariosConAlerta))
                                    <span class="ml-1 text-red-600 dark:text-red-400" title="Últimas 2 asistencias fueron faltas">
                                        ⚠️
                                    </span>
                                @endif
                                @if($pendientesCaptura > 0)
                                    <span class="ml-1 text-orange-700 dark:text-orange-300" title="Días sin captura">P: {{ $pendientesCaptura }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm bg-yellow-100 dark:bg-yellow-900/30 border-r border-gray-300 dark:border-gray-600">${{ number_format($nomina['sueldo_quincenal'] ?? 0, 2) }}</td>
                            <td class="px-3 py-2 text-sm bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 font-medium rounded border-r border-gray-300 dark:border-gray-600">{{ $totalHorasExtra }}</td>
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
                                    $asistencia = $this->asistenciaUsuarioFecha($asistenciasIndexadas, $f, $user->id);
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
                                    @elseif($dia === 'I') bg-red-200 dark:bg-red-900/40  <!-- 👈 Nuevo: I = rojo como F -->
                                    @elseif($dia === 'P') bg-orange-200 dark:bg-orange-900/50
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

    <!-- Modal de detalle de nómina -->
    @if($showModal && $detalleNomina)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm">
            <div class="max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-emerald-500/30 bg-gradient-to-r from-emerald-700 to-teal-600 p-6 text-white">
                    <h3 class="text-xl font-bold text-white">
                        Detalle de Nómina - {{ $detalleNomina['user_id'] }}: {{ $usuarios->firstWhere('id', $userIdModal)?->name ?? 'Usuario' }}
                    </h3>
                    <button
                        wire:click="cerrarModal"
                        class="rounded-lg bg-white/10 p-2 text-white transition hover:bg-white/20"
                        aria-label="Cerrar"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 bg-slate-50 p-5 dark:bg-gray-900/40 md:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800"><div class="text-xs text-slate-500">Días pagados</div><div class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ $detalleNomina['dias_pagados']['total'] ?? 0 }}</div></div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20"><div class="text-xs text-emerald-700 dark:text-emerald-300">Bruto</div><div class="mt-1 text-xl font-bold text-slate-900 dark:text-white">${{ number_format($detalleNomina['subtotal_percepciones'] ?? 0, 2) }}</div></div>
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 dark:border-rose-800 dark:bg-rose-900/20"><div class="text-xs text-rose-700 dark:text-rose-300">Deducciones + ISR</div><div class="mt-1 text-xl font-bold text-slate-900 dark:text-white">${{ number_format(($detalleNomina['deducciones_especiales'] ?? 0) + ($detalleNomina['isr'] ?? 0), 2) }}</div></div>
                    <div class="rounded-xl border border-sky-200 bg-sky-50 p-3 dark:border-sky-800 dark:bg-sky-900/20"><div class="text-xs text-sky-700 dark:text-sky-300">Neto estimado</div><div class="mt-1 text-xl font-bold text-slate-900 dark:text-white">${{ number_format($detalleNomina['total_neto'] ?? 0, 2) }}</div></div>
                </div>

                <!-- Contenedor principal: 2 columnas -->
                <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Columna izquierda: Percepciones -->
                    <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                        <div class="bg-gray-200 dark:bg-gray-700 px-4 py-2">
                            <h4 class="font-bold text-gray-800 dark:text-white text-center">Percepciones</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Agrup. SAT</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No.</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Concepto</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">P</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">001 001</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">Sueldo</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                            ${{ number_format($detalleNomina['dias_pagados']['total'] * $detalleNomina['sueldo_diario'], 2) }}
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">P</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">038 014</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">Premios Asistencia</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                            ${{ number_format($detalleNomina['bonos']['asistencia']['monto'] ?? 0, 2) }}
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">P</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">010 015</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">Premio puntualidad</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                            ${{ number_format($detalleNomina['bonos']['puntualidad']['monto'] ?? 0, 2) }}
                                        </td>
                                    </tr>
                                    <!-- Nuevo: Ajuste al neto -->
                                    @if(($detalleNomina['ajuste_al_neto']['monto'] ?? 0) !== 0 && ($detalleNomina['ajuste_al_neto']['tipo'] ?? 'ninguno') === 'percepcion')
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">OP</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">999 099</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">Ajuste al neto</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                                ${{ number_format($detalleNomina['ajuste_al_neto']['monto'], 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                                        <td colspan="3" class="px-3 py-3 text-right">Total Percepc. más Otros Pagos $</td>
                                        <td class="px-3 py-3 text-right text-lg">
                                            ${{ number_format($detalleNomina['subtotal_percepciones'], 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Columna derecha: Deducciones -->
                    <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                        <div class="bg-gray-200 dark:bg-gray-700 px-4 py-2">
                            <h4 class="font-bold text-gray-800 dark:text-white text-center">Deducciones</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Agrup. SAT</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No.</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Concepto</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">002</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">045</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">I.S.R. mes</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                            ${{ number_format($detalleNomina['isr'], 2) }}
                                        </td>
                                    </tr>

                                    <!-- Deducciones especiales -->
                                    @foreach($detalleNomina['detalle_deducciones_especiales'] ?? [] as $deduccion)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">003</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">999</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $deduccion['concepto'] }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                                ${{ number_format($deduccion['monto_periodo'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <!-- Ajuste al neto (si es deducción) -->
                                    @if(($detalleNomina['ajuste_al_neto']['monto'] ?? 0) !== 0 && ($detalleNomina['ajuste_al_neto']['tipo'] ?? 'ninguno') === 'deduccion')
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">099</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">099</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">Ajuste al neto</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                                -$ {{ number_format($detalleNomina['ajuste_al_neto']['monto'], 2) }}
                                            </td>
                                        </tr>
                                    @endif

                                    <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                                        <td colspan="3" class="px-3 py-3 text-right">Subtotal $</td>
                                        <td class="px-3 py-3 text-right">
                                            ${{ number_format($detalleNomina['subtotal_percepciones'], 2) }}
                                        </td>
                                    </tr>
                                    <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                                        <td colspan="3" class="px-3 py-3 text-right">Descuentos $</td>
                                        <td class="px-3 py-3 text-right">${{ number_format($detalleNomina['deducciones_especiales'] ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                                        <td colspan="3" class="px-3 py-3 text-right">Retenciones $</td>
                                        <td class="px-3 py-3 text-right">${{ number_format($detalleNomina['isr'], 2) }}</td>
                                    </tr>
                                    <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                                        <td colspan="3" class="px-3 py-3 text-right">Total $</td>
                                        <td class="px-3 py-3 text-right">${{ number_format($detalleNomina['subtotal_percepciones'] - $detalleNomina['isr'] - ($detalleNomina['deducciones_especiales'] ?? 0), 2) }}</td>
                                    </tr>
                                    <tr class="bg-blue-50 dark:bg-blue-900/20 font-bold text-blue-800 dark:text-blue-200">
                                        <td colspan="3" class="px-3 py-3 text-right">Neto del recibo $</td>
                                        <td class="px-3 py-3 text-right text-xl">
                                            ${{ number_format($detalleNomina['total_neto'], 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pie de modal -->
                <div class="p-6 bg-gray-50 dark:bg-gray-700/50">
                    <div class="text-sm text-gray-500 dark:text-gray-400 italic mb-4">
                        Se puso a mi disposición el archivo XML correspondiente y recibí de la empresa arriba mencionada la cantidad neta a que este documento se refiere estando conforme con las percepciones y deducciones que en él aparecen especificados.
                    </div>
                    <div class="text-center text-xs text-gray-500 dark:text-gray-400">
                        <!-- Mostrar en palabras (eliminado por error NumberFormatter) -->
                        <span class="italic">cinco mil cuatrocientos noventa y cinco pesos 20/100 M.N.</span>
                    </div>
                </div>

                <div class="p-6 bg-gray-50 dark:bg-gray-700/50 flex justify-end space-x-3">
                    <button
                        wire:click="cerrarModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white rounded-lg"
                    >
                        Cerrar
                    </button>
                    <button
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg"
                        onclick="printNomina({{ $userIdModal }})"
                    >
                        Imprimir
                    </button>
                </div>
            </div>
        </div>

        <script>
            function printNomina(userId) {
                alert('Función de impresión para usuario ' + userId + ' (próximamente)');
            }
        </script>
    @endif
</div>
