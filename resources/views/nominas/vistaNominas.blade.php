<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 7a2 2 0 012-2h2a2 2 0 012 2M9 7a2 2 0 002 2h2a2 2 0 002-2m-6 9h6" />
                                </svg>
                                Cálculo de Nóminas
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Filtre y calcule las nóminas de los empleados
                            </p>
                        </div>

                        @if(isset($usuarios))
                        <div class="flex items-center space-x-2">
                            <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full">
                                <span class="text-sm font-medium">{{ $usuarios->count() }}</span>
                                <span class="text-xs">empleados</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <form method="GET" action="{{ route('nominas.calculos') }}" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="punto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Punto
                                </div>
                            </label>
                            <select name="punto" id="punto" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                                <option value="">Todos</option>
                                <option value="DRONES" {{ request('punto') == 'DRONES' ? 'selected' : '' }}>Drones</option>
                                <option value="KANSAS" {{ request('punto') == 'KANSAS' ? 'selected' : '' }}>Kansas</option>
                                <option value="MONTERREY" {{ request('punto') == 'MONTERREY' ? 'selected' : '' }}>Monterrey</option>
                                <option value="GUANAJUATO" {{ request('punto') == 'GUANAJUATO' ? 'selected' : '' }}>Guanajuato</option>
                                <option value="NUEVO LAREDO" {{ request('punto') == 'NUEVO LAREDO' ? 'selected' : '' }}>Nvo Laredo</option>
                                <option value="MEXICO" {{ request('punto') == 'MEXICO' ? 'selected' : '' }}>Mexico</option>
                                <option value="SLP" {{ request('punto') == 'SLP' ? 'selected' : '' }}>SLP</option>
                                <option value="XALAPA" {{ request('punto') == 'XALAPA' ? 'selected' : '' }}>Xalapa</option>
                                <option value="MICHOACAN" {{ request('punto') == 'MICHOACAN' ? 'selected' : '' }}>Michoacán</option>
                                <option value="PUEBLA" {{ request('punto') == 'PUEBLA' ? 'selected' : '' }}>Puebla</option>
                                <option value="TOLUCA" {{ request('punto') == 'TOLUCA' ? 'selected' : '' }}>Toluca</option>
                                <option value="QUERETARO" {{ request('punto') == 'QUERETARO' ? 'selected' : '' }}>Querétaro</option>
                                <option value="SALTILLO" {{ request('punto') == 'SALTILLO' ? 'selected' : '' }}>Saltillo</option>
                            </select>
                        </div>

                        <div>
                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Fecha Inicio
                                </div>
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div>
                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Fecha Fin
                                </div>
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filtrar
                            </button>
                        </div>
                    </div>
                </form>

                @if(isset($usuarios) && $usuarios->count() > 0)
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
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Nombre
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Punto
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Fecha de ingreso
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Rol
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Detalle
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($usuarios as $user)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0">
                                                        @php
                                                            $foto = $user->solicitudAlta?->documentacion?->arch_foto;
                                                        @endphp
                                                        @if($foto)
                                                            <img src="{{ asset($foto) }}" class="h-8 w-8 rounded-full" alt="{{ $user->name }}" />
                                                        @else
                                                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                                                <span class="text-white font-medium text-xs">
                                                                    {{ substr($user->name ?? '', 0, 2) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ $user->name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                @if($user->punto)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                                                        {{ $user->punto }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">N/D</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ \Carbon\Carbon::parse($user->fecha_ingreso)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $user->rol ?? 'N/D' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <button type="button"
                                                    onclick="abrirModalNomina(
                                                        {{ $user->id }},
                                                        '{{ addslashes($user->name) }}',
                                                        {{ $user->asistencias_count }},
                                                        {{ $user->descansos_count }},
                                                        {{ $user->faltas_count }},
                                                        {{ $user->solicitudAlta->sd ?? 0 }},
                                                        {{ $user->solicitudAlta->sdi ?? 0 }},
                                                        {{ $user->monto_deducciones ?? 0 }},
                                                        {{ $user->monto_vacaciones ?? 0 }}
                                                    )"
                                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm text-xs">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Ver
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif(isset($usuarios) && $usuarios->count() == 0)
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No se encontraron resultados</h3>
                        <p class="text-gray-500 dark:text-gray-400">No se encontraron usuarios activos con los filtros aplicados.</p>
                    </div>
                @elseif(request()->has('punto') || request()->has('fecha_inicio') || request()->has('fecha_fin'))
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No se encontraron resultados</h3>
                        <p class="text-gray-500 dark:text-gray-400">No se encontraron usuarios activos con los filtros aplicados.</p>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aplicar filtros</h3>
                        <p class="text-gray-500 dark:text-gray-400">Utilice los filtros para buscar empleados y calcular sus nóminas.</p>
                    </div>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function calcularImss(sdi, asistencias, descansos){
        let diasTrabajados = asistencias + descansos;
        let sueldo = diasTrabajados * sdi;
        let inv = sueldo * 0.00625;
        let ces = sueldo * 0.01125;
        let mat = sdi * 0.05;

        let imss = inv + ces + mat;

        return imss.toFixed(2);
    }

    function calcularISR(sd, asistencias, descansos, faltas) {
        let diasTrabajados = asistencias + descansos;
        let sueldo = sd * diasTrabajados;

        if (faltas === 0) {
            sueldo += sueldo * 0.20;
        }

        const tablaISR = [
            { limInf: 0.01, limSup: 368.10, cuotaFija: 0.00, porcentaje: 1.92 },
            { limInf: 368.11, limSup: 3124.35, cuotaFija: 7.05, porcentaje: 6.40 },
            { limInf: 3124.36, limSup: 5490.75, cuotaFija: 183.45, porcentaje: 10.88 },
            { limInf: 5490.76, limSup: 6382.80, cuotaFija: 441.00, porcentaje: 16.00 },
            { limInf: 6382.81, limSup: 7641.90, cuotaFija: 583.65, porcentaje: 17.92 },
            { limInf: 7641.91, limSup: 15412.80, cuotaFija: 809.25, porcentaje: 21.36 },
            { limInf: 15412.81, limSup: 24292.65, cuotaFija: 2469.15, porcentaje: 23.52 },
            { limInf: 24292.66, limSup: 46378.50, cuotaFija: 4557.75, porcentaje: 30.00 },
            { limInf: 46378.51, limSup: 61838.10, cuotaFija: 11183.40, porcentaje: 32.00 },
            { limInf: 61838.11, limSup: 185514.30, cuotaFija: 16130.55, porcentaje: 34.00 },
            { limInf: 185514.31, limSup: Infinity, cuotaFija: 58180.35, porcentaje: 35.00 },
        ];

        const rango = tablaISR.find(r => sueldo >= r.limInf && sueldo <= r.limSup);

        if (!rango) {
            return 0;
        }

        const excedente = sueldo - r.limInf;
        const isr = (excedente * (rango.porcentaje / 100)) + rango.cuotaFija;

        return isr.toFixed(2);
    }

    function abrirModalNomina(userId, userName, asistencias, descansos, faltas, sd, sdi, deducciones, vacaciones) {
        const isr = calcularISR(sd, asistencias, descansos, faltas);
        const imss = calcularImss(sdi, asistencias, descansos);
        const sueldo = ((asistencias + descansos) * sd).toFixed(2);
        const totalPremio = ((asistencias + descansos) * sd * 0.1).toFixed(2);

        let percepcionesNum = parseFloat(
            faltas === 0
                ? (parseFloat(totalPremio) * 2 + (asistencias + descansos) * sd)
                : (asistencias + descansos) * sd
        );

        let filaVacaciones = '';
        if (vacaciones > 0) {
            percepcionesNum += vacaciones;
            filaVacaciones = `
                <tr>
                    <td>Vacaciones disfrutadas</td>
                    <td></td>
                    <td>$</td>
                    <td>${vacaciones.toFixed(2)}</td>
                </tr>
            `;
        }

        let deduccionesNum = parseFloat(isr) + parseFloat(imss);

        const netoPagarCalculado = parseFloat((percepcionesNum - deduccionesNum).toFixed(2));

        let ajusteAlNeto = 0;
        let ladoPercepciones = false;

        const ultimoDecimal = parseInt(netoPagarCalculado.toFixed(2).slice(-1));

        if (ultimoDecimal < 5) {
            ajusteAlNeto = parseFloat((ultimoDecimal / 100).toFixed(2));
            deduccionesNum += ajusteAlNeto;
            ladoPercepciones = false;
        } else if (ultimoDecimal > 0) {
            ajusteAlNeto = parseFloat(((10 - ultimoDecimal) / 100).toFixed(2));
            percepcionesNum += ajusteAlNeto;
            ladoPercepciones = true;
        }

        const netoPagarFinal = (percepcionesNum - deduccionesNum).toFixed(2);

        const filasPremios = (faltas === 0)
            ? `
                <tr>
                    <td>Premio Asistencia</td>
                    <td></td>
                    <td>$</td>
                    <td>${totalPremio}</td>
                </tr>
                <tr>
                    <td>Premio de Puntualidad</td>
                    <td></td>
                    <td>$</td>
                    <td>${totalPremio}</td>
                </tr>
            `
            : '';

        const filaAjuste = `
            <tr>
                <td>Ajuste al neto</td>
                ${ladoPercepciones ? '<td></td><td>$</td><td>' + ajusteAlNeto.toFixed(2) + '</td>' : '<td>$</td><td>' + ajusteAlNeto.toFixed(2) + '</td><td></td>'}
            </tr>
        `;

        let filaDeduccionesExtra = '';
        if (deducciones > 0) {
            filaDeduccionesExtra = `
                <tr>
                    <td>Deducciones adicionales</td>
                    <td>$</td>
                    <td>${deducciones.toFixed(2)}</td>
                </tr>
            `;
            deduccionesNum += deducciones;
        }

        Swal.fire({
            title: 'Detalle de nómina de ' + userName,
            html: `
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><strong>Asistencias:</strong> ${asistencias + descansos}</div>
                        <div><strong>Faltas:</strong> ${faltas}</div>
                    </div>
                </div>
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                    <div>
                        <h3 style="font-weight: bold; margin-bottom: 10px; color: #374151;">PERCEPCIONES</h3>
                        <table border="1" cellpadding="8" style="border-collapse: collapse; background: white;">
                            <thead style="background-color: #f3f4f6;">
                                <tr>
                                    <th>Concepto</th>
                                    <th>Días</th>
                                    <th></th>
                                    <th>Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Sueldo</td>
                                    <td>${asistencias + descansos}</td>
                                    <td>$</td>
                                    <td>${sueldo}</td>
                                </tr>
                                ${filasPremios}
                                ${filaVacaciones}
                                ${ladoPercepciones ? filaAjuste : ''}
                                <tr><td colspan="4" style="height: 20px;"></td></tr>
                                <tr style="background-color: #e5e7eb;">
                                    <td><strong>Total Percepciones</strong></td>
                                    <td></td>
                                    <td><strong>$</strong></td>
                                    <td><strong>${percepcionesNum.toFixed(2)}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <h3 style="font-weight: bold; margin-bottom: 10px; color: #374151;">DEDUCCIONES</h3>
                        <table border="1" cellpadding="8" style="border-collapse: collapse; background: white;">
                            <thead style="background-color: #f3f4f6;">
                                <tr>
                                    <th>Concepto</th>
                                    <th></th>
                                    <th>Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>I.S.R. (mes)</td>
                                    <td>$</td>
                                    <td>${parseFloat(isr).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td>I.M.S.S.</td>
                                    <td>$</td>
                                    <td>${parseFloat(imss).toFixed(2)}</td>
                                </tr>
                                ${filaDeduccionesExtra}
                                ${!ladoPercepciones ? filaAjuste : ''}
                                <tr><td colspan="3" style="height: 20px;"></td></tr>
                                <tr style="background-color: #e5e7eb;">
                                    <td><strong>Total Deducciones</strong></td>
                                    <td><strong>$</strong></td>
                                    <td><strong>${deduccionesNum.toFixed(2)}</strong></td>
                                </tr>
                                <tr><td colspan="3" style="height: 20px;"></td></tr>
                                <tr style="background-color: #3b82f6; color: white;">
                                    <td><strong>Neto a pagar</strong></td>
                                    <td><strong>$</strong></td>
                                    <td><strong>${(netoPagarFinal-deducciones).toFixed(2)}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            `,
            width: '80%',
            showCloseButton: true,
            confirmButtonText: 'Cerrar',
            customClass: {
                popup: 'swal2-popup-custom'
            }
        });
    }
</script>

<style>
    .swal2-popup-custom {
        border-radius: 0.5rem !important;
    }
    .swal2-title {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
    }
</style>
