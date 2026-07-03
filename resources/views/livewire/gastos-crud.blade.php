<div class="py-6 mx-auto">
    <x-navbar />

    <x-livewire.monitoreo-layout :breadcrumb-items="$breadcrumbItems" :title-main="$titleMain" :help-text="$helpText">
        <div class="container mx-auto space-y-5">
            <section class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                    <div>
                        <label for="filtro_estatus" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                            Estatus
                        </label>
                        <select id="filtro_estatus" wire:model.live="filtro_estatus"
                            class="w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200">
                            <option value="activas">Activas</option>
                            <option value="terminadas">Terminadas</option>
                        </select>
                    </div>

                    <div>
                        <label for="filtro_fecha_inicio" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                            Desde
                        </label>
                        <input id="filtro_fecha_inicio" type="date" wire:model.live="filtro_fecha_inicio"
                            class="w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200">
                    </div>

                    <div>
                        <label for="filtro_fecha_fin" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                            Hasta
                        </label>
                        <input id="filtro_fecha_fin" type="date" wire:model.live="filtro_fecha_fin"
                            class="w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200">
                    </div>

                    <div>
                        <label for="filtro_busqueda" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                            Misión o cliente
                        </label>
                        <input id="filtro_busqueda" type="search" wire:model.live.debounce.400ms="filtro_busqueda"
                            placeholder="ID, nombre clave o cliente"
                            class="w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200">
                    </div>

                    <div>
                        <label for="filtro_agente" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                            Agente
                        </label>
                        <input id="filtro_agente" type="search" wire:model.live.debounce.400ms="filtro_agente"
                            placeholder="Nombre del agente"
                            class="w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200">
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label for="perPage" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                                Mostrar
                            </label>
                            <select id="perPage" wire:model.live="perPage"
                                class="w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>

                        <button type="button" wire:click="limpiarFiltros"
                            class="inline-flex items-center justify-center px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600"
                            title="Limpiar filtros">
                            <i class="ti ti-filter-off"></i>
                        </button>
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    El rango busca misiones cuyo periodo coincida con las fechas seleccionadas y limita a ese mismo rango los gastos del desglose.
                </p>
            </section>

            <div wire:loading.flex class="items-center gap-2 text-sm text-blue-600 dark:text-blue-300">
                <i class="ti ti-loader-2 animate-spin"></i>
                Actualizando resultados...
            </div>

            <section wire:loading.class="opacity-60" class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left text-gray-600 uppercase dark:text-gray-200">Misión</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left text-gray-600 uppercase dark:text-gray-200">Cliente</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left text-gray-600 uppercase dark:text-gray-200">Periodo</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left text-gray-600 uppercase dark:text-gray-200">Estatus</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-center text-gray-600 uppercase dark:text-gray-200">Agentes</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-center text-gray-600 uppercase dark:text-gray-200">Gastos</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-right text-gray-600 uppercase dark:text-gray-200">Total</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-center text-gray-600 uppercase dark:text-gray-200">Acciones</th>
                            </tr>
                        </thead>
                        <tbody x-data="{ misionAbierta: null }" class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($misiones as $mision)
                                @php
                                    $resumen = $resumenMisiones[$mision->id] ?? [
                                        'agentes' => [],
                                        'cantidad_gastos' => 0,
                                        'total' => 0,
                                        'estatus' => 'N/A',
                                    ];
                                @endphp

                                <tr wire:key="mision-{{ $mision->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-750">
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-100">
                                        <div class="font-semibold">Misión #{{ $mision->id }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $mision->nombre_clave ?: ($mision->tipo_servicio ?: 'Sin nombre clave') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                                        {{ $mision->cliente ?: 'Sin cliente registrado' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap dark:text-gray-200">
                                        {{ \Carbon\Carbon::parse($mision->fecha_inicio)->format('d/m/Y') }}
                                        <span class="mx-1 text-gray-400">—</span>
                                        {{ \Carbon\Carbon::parse($mision->fecha_fin)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' => $resumen['estatus'] === 'Activa',
                                            'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-100' => $resumen['estatus'] !== 'Activa',
                                        ])>
                                            {{ $resumen['estatus'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-700 dark:text-gray-200">
                                        {{ count($resumen['agentes']) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-700 dark:text-gray-200">
                                        {{ $resumen['cantidad_gastos'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-right text-gray-900 whitespace-nowrap dark:text-white">
                                        ${{ number_format($resumen['total'], 2) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button"
                                                @click="misionAbierta = misionAbierta === {{ $mision->id }} ? null : {{ $mision->id }}"
                                                :aria-expanded="misionAbierta === {{ $mision->id }}"
                                                class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-100"
                                                title="Ver agentes y gastos">
                                                <i class="ti ti-list-details"></i>
                                                Desglose
                                            </button>

                                            <button type="button" disabled
                                                class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium text-white bg-green-600 rounded-md cursor-not-allowed opacity-50"
                                                title="La exportación se implementará próximamente">
                                                <i class="ti ti-file-spreadsheet"></i>
                                                Excel
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr wire:key="desglose-{{ $mision->id }}" x-show="misionAbierta === {{ $mision->id }}" x-cloak
                                    class="bg-gray-50/70 dark:bg-gray-900/40">
                                    <td colspan="8" class="p-0">
                                        <div id="desglose-mision-{{ $mision->id }}">
                                            <div class="flex items-center justify-between gap-2 px-4 py-3 text-sm font-medium text-blue-700 dark:text-blue-300">
                                                <span class="flex items-center gap-2">
                                                    <i class="ti ti-list-details"></i>
                                                Ver agentes y gastos de la misión #{{ $mision->id }}
                                                </span>
                                                <button type="button" @click="misionAbierta = null"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-500 rounded hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700"
                                                    title="Cerrar desglose">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </div>

                                            <div class="px-4 pb-5 space-y-4">
                                                @forelse ($resumen['agentes'] as $agente)
                                                    <article class="overflow-hidden bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                                                        <header class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700">
                                                            <div class="flex items-center gap-2">
                                                                <span class="inline-flex items-center justify-center w-8 h-8 text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-100">
                                                                    <i class="ti ti-user-shield"></i>
                                                                </span>
                                                                <div>
                                                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $agente['nombre'] }}</h3>
                                                                    <p class="text-xs text-gray-500 dark:text-gray-300">Agente #{{ $agente['id'] }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                                                {{ $agente['gastos']->count() }} gasto(s) ·
                                                                ${{ number_format((float) $agente['gastos']->sum('Monto'), 2) }}
                                                            </div>
                                                        </header>

                                                        <div class="overflow-x-auto">
                                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                                <thead class="bg-white dark:bg-gray-800">
                                                                    <tr>
                                                                        <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-300">Fecha y hora</th>
                                                                        <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-300">Tipo</th>
                                                                        <th class="px-4 py-2 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-300">Monto</th>
                                                                        <th class="px-4 py-2 text-xs font-medium text-center text-gray-500 uppercase dark:text-gray-300">Comprobante</th>
                                                                        <th class="px-4 py-2 text-xs font-medium text-center text-gray-500 uppercase dark:text-gray-300">Detalle</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                                    @forelse ($agente['gastos'] as $gasto)
                                                                        <tr wire:key="mision-{{ $mision->id }}-gasto-{{ $gasto->id }}">
                                                                            <td class="px-4 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-200">
                                                                                {{ $gasto->Fecha?->format('d/m/Y') ?? 'Sin fecha' }}
                                                                                <span class="block text-xs text-gray-500">{{ $gasto->Hora ?: 'Sin hora' }}</span>
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $gasto->Tipo ?: 'Sin tipo' }}</td>
                                                                            <td class="px-4 py-2 text-sm font-semibold text-right text-gray-900 whitespace-nowrap dark:text-white">
                                                                                ${{ number_format((float) $gasto->Monto, 2) }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-center">
                                                                                @php
                                                                                    $evidenciaExiste = $gasto->Evidencia
                                                                                        && Storage::disk('public')->exists($gasto->Evidencia);
                                                                                @endphp

                                                                                @if ($evidenciaExiste)
                                                                                    <a href="{{ asset('storage/' . $gasto->Evidencia) }}" target="_blank" rel="noopener noreferrer"
                                                                                        class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-300">
                                                                                        <i class="ti {{ strtolower(pathinfo($gasto->Evidencia, PATHINFO_EXTENSION)) === 'pdf' ? 'ti-file-type-pdf' : 'ti-photo' }}"></i>
                                                                                        Ver comprobante
                                                                                    </a>
                                                                                @elseif ($gasto->Evidencia)
                                                                                    <span class="inline-flex items-center gap-1 text-xs text-red-600 dark:text-red-300">
                                                                                        <i class="ti ti-file-off"></i>
                                                                                        No disponible
                                                                                    </span>
                                                                                @else
                                                                                    <span class="text-xs text-gray-400">Sin comprobante</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="px-4 py-2 text-center">
                                                                                <a href="{{ route('gastos.detalle', $gasto->id) }}"
                                                                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-700 bg-gray-100 border border-gray-300 rounded hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                                                                                    title="Ver detalle del gasto">
                                                                                    <i class="ti ti-eye"></i>
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="5" class="px-4 py-5 text-sm text-center text-gray-500 dark:text-gray-400">
                                                                                Este agente no tiene gastos registrados en el periodo seleccionado.
                                                                            </td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </article>
                                                @empty
                                                    <div class="px-4 py-6 text-sm text-center text-gray-500 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                                                        La misión no tiene agentes asignados.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3 text-gray-500 dark:text-gray-400">
                                            <span class="inline-flex items-center justify-center w-14 h-14 bg-gray-100 rounded-full dark:bg-gray-700">
                                                <i class="text-3xl ti ti-clipboard-off"></i>
                                            </span>
                                            <div>
                                                <p class="font-semibold text-gray-700 dark:text-gray-200">No se encontraron misiones</p>
                                                <p class="text-sm">Ajusta los filtros para consultar otro periodo.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($misiones->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $misiones->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </section>
        </div>
    </x-livewire.monitoreo-layout>
</div>
