<div class="space-y-6">

    {{-- PANEL DE FILTROS --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4l2 4m0 0l4-2m-4 2l-2 4m2-4l2 4m6 0l2-4m-2 4l4 2m-4-2l-2-4m2 4l2-4m-2 4l4 2m0 0l-2 4m2-4l4 2M3 4l4 2m0 0l-2 4m2-4l4 2m0 0l-2 4m2-4l4 2m0 0l-2 4m2-4l4 2" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">Filtros de Búsqueda</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Refina los resultados por periodo</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <label class="sr-only">Quincena</label>
                    <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 focus-within:ring-2 focus-within:ring-amber-500 focus-within:border-amber-500 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <select wire:model.live="filtroQuincena" class="bg-transparent border-none text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-0 cursor-pointer py-1">
                            <option value="todas">Todas las quincenas</option>
                            <option value="1">1ª Quincena (1-15)</option>
                            <option value="2">2ª Quincena (16-fin)</option>
                        </select>
                    </div>
                </div>

                <div class="relative">
                    <label class="sr-only">Mes</label>
                    <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 focus-within:ring-2 focus-within:ring-amber-500 focus-within:border-amber-500 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <select wire:model.live="filtroMes" class="bg-transparent border-none text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-0 cursor-pointer py-1">
                            <option value="todos">Todos los meses</option>
                            @foreach (range(1, 12) as $mes)
                                <option value="{{ $mes }}">{{ Str::upper(\Carbon\Carbon::create()->month($mes)->translatedFormat('F')) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button wire:click="generarExcel" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar Excel
                </button>

                @if($usuarios->total() > 0)
                    <div class="hidden lg:flex items-center gap-2 px-3 py-2 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 rounded-xl text-sm font-medium border border-amber-100 dark:border-amber-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ $usuarios->total() }} registros
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">#</th>
                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Empresa
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[200px]">
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Empleado
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Ingreso
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <div class="flex items-center justify-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Antigüedad
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <div class="flex items-center justify-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Días
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <div class="flex items-center justify-end gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Salario Diario
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 text-right text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider bg-green-50/50 dark:bg-green-900/10">
                            <div class="flex items-center justify-end gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Total Vacaciones
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 text-right text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider bg-amber-50/50 dark:bg-amber-900/10">
                            <div class="flex items-center justify-end gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Prima Vacacional
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($usuarios as $usuario)
                        @php
                            $fechaIngreso = \Carbon\Carbon::parse($usuario->fecha_ingreso);
                            $antiguedad = $fechaIngreso->diff(now());

                            $diasVacaciones = match (true) {
                                $antiguedad->y < 2 => 12,
                                $antiguedad->y === 2 => 14,
                                $antiguedad->y === 3 => 16,
                                $antiguedad->y === 4 => 18,
                                $antiguedad->y === 5 => 20,
                                $antiguedad->y > 5 && $antiguedad->y <= 10 => 22,
                                $antiguedad->y > 10 && $antiguedad->y <= 15 => 24,
                                $antiguedad->y > 15 && $antiguedad->y <= 20 => 26,
                                $antiguedad->y > 20 && $antiguedad->y <= 25 => 28,
                                $antiguedad->y > 25 && $antiguedad->y <= 30 => 30,
                                default => 32,
                            };

                            $rawSueldo = $usuario->solicitudAlta->sueldo_mensual ?? '0';
                            if (preg_match('/\((.*?)\)/', $rawSueldo, $matches)) {
                                $soloNumero = preg_replace('/[^0-9.]/', '', $matches[1]);
                            } else {
                                $soloNumero = preg_replace('/[^0-9.]/', '', $rawSueldo);
                            }

                            $salario = floatval($soloNumero) / 2;
                            $salarioDiario = $salario > 0 ? round($salario / 15, 2) : 0;
                            $prima = round($salarioDiario * $diasVacaciones * 0.25, 2);
                            $vacacionesMonto = $diasVacaciones * $salarioDiario;
                        @endphp

                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-400 dark:text-gray-500">
                                {{ $usuarios->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($usuario->empresa)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-100 dark:border-amber-800">
                                        {{ $usuario->empresa }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 text-sm">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        @php
                                            $doc = $usuario->documentacionAltas;
                                            $rutaBD = $doc?->arch_foto;

                                            // Debug paso a paso
                                            $debug = [
                                                'user_id' => $usuario->id,
                                                'ruta_en_bd' => $rutaBD,
                                                'tiene_relacion' => $doc ? 'SÍ' : 'NO',
                                                'tiene_arch_foto' => $rutaBD ? 'SÍ' : 'NO',
                                            ];

                                            if ($rutaBD) {
                                                // Quitar 'storage/' del inicio si existe
                                                $rutaRelativa = preg_replace('#^storage/#i', '', $rutaBD);
                                                $rutaDisco = storage_path('app/public/' . $rutaRelativa);

                                                $debug['ruta_relativa'] = $rutaRelativa;
                                                $debug['ruta_completa_disco'] = $rutaDisco;
                                                $debug['archivo_existe'] = file_exists($rutaDisco) ? 'SÍ ✓' : 'NO ✗';
                                                $debug['es_legible'] = is_readable($rutaDisco) ? 'SÍ ✓' : 'NO ✗';

                                                // URL pública
                                                $url = asset($rutaBD);
                                                $debug['url_publica'] = $url;

                                                // Verificar si storage link existe
                                                $debug['storage_link'] = file_exists(public_path('storage')) ? 'EXISTS ✓' : 'NO EXISTS ✗';
                                            }
                                        @endphp
                                        {{-- IMAGEN O FALLBACK --}}
                                        @if(isset($url) && isset($debug['archivo_existe']) && $debug['archivo_existe'] === 'SÍ ✓')
                                            <img src="{{ $url }}"
                                                alt="Foto"
                                                class="h-10 w-10 rounded-full object-cover shadow-sm ring-2 ring-white dark:ring-gray-800"
                                                loading="lazy"
                                                onerror="console.error('ERROR AL CARGAR:', this.src); this.classList.add('border-2', 'border-red-500')">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-sm ring-2 ring-white dark:ring-gray-800">
                                                <span class="text-white font-semibold text-xs">{{ strtoupper(substr($usuario->name ?? 'N', 0, 2)) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                            @php
                                                $nombreCompleto = trim("{$usuario->solicitudAlta->apellido_paterno} {$usuario->solicitudAlta->apellido_materno} {$usuario->solicitudAlta->nombre}");
                                            @endphp
                                            {{ strtoupper($nombreCompleto) }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 font-mono">
                                {{ $usuario->fecha_ingreso ? $fechaIngreso->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800 min-w-[70px]">
                                    {{ $antiguedad->y }} {{ $antiguedad->y == 1 ? 'Año' : 'Años' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-100 dark:border-purple-800 min-w-[70px]">
                                    {{ $diasVacaciones }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-mono text-gray-700 dark:text-gray-300">
                                ${{ number_format($salarioDiario, 2) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right bg-green-50/30 dark:bg-green-900/5">
                                <span class="inline-flex items-center justify-end px-3 py-1.5 rounded-lg text-sm font-bold text-green-700 dark:text-green-300">
                                    ${{ number_format($vacacionesMonto, 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right bg-amber-50/30 dark:bg-amber-900/5">
                                <span class="inline-flex items-center justify-end px-3 py-1.5 rounded-lg text-sm font-bold text-amber-700 dark:text-amber-300">
                                    ${{ number_format($prima, 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-16">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Sin resultados</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                                        No se encontraron empleados con los filtros seleccionados. Intenta ajustar la búsqueda.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINACIÓN --}}
    @if($usuarios->hasPages())
    <div id="paginacion-wrapper"
         x-data
         x-on:livewire:updated.window="$nextTick(() => {
             const el = document.getElementById('paginacion-wrapper');
             if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
         })"
         class="mt-6">
        {{ $usuarios->withQueryString()->links() }}
    </div>
@endif
</div>
