<x-livewire.monitoreo-layout
    :breadcrumb-items="[
        ['icon' => 'ti-home', 'url' => route('admin.monitoreoDashboard')],
        ['icon' => 'ti-report-analytics', 'label' => 'Reporte semanal de evidencias'],
    ]"
    title-main="Reporte semanal de evidencias"
    help-text="Casos confirmados agrupados por la semana en que se recibió su primera evidencia."
>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex flex-wrap items-end gap-3">
                <button wire:click="anterior" type="button" class="px-3 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">
                    <i class="ti ti-chevron-left"></i> Anterior
                </button>
                <label>
                    <span class="block mb-1 text-xs font-semibold text-gray-500 uppercase">Lunes de la semana</span>
                    <input wire:model.live="semana" type="date" class="border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                </label>
                <button wire:click="siguiente" type="button" class="px-3 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">
                    Siguiente <i class="ti ti-chevron-right"></i>
                </button>
                <button wire:click="actual" type="button" class="px-3 py-2 text-sm font-semibold text-teal-700 bg-teal-50 rounded-lg hover:bg-teal-100 dark:bg-teal-950 dark:text-teal-300">
                    Semana actual
                </button>
            </div>

            <a href="{{ route('inspecciones.reportes.semanal.xlsx', ['semana' => $reporte['inicio']->toDateString()]) }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                <i class="mr-2 ti ti-file-spreadsheet"></i> Descargar Excel
            </a>
        </div>

        <div class="p-4 text-center border border-teal-200 bg-teal-50 rounded-2xl dark:border-teal-900 dark:bg-teal-950">
            <p class="text-sm font-semibold text-teal-900 dark:text-teal-100">
                Del {{ $reporte['inicio']->locale('es')->translatedFormat('l d \d\e F') }} al {{ $reporte['fin']->locale('es')->translatedFormat('l d \d\e F \d\e Y') }}
            </p>
            <p class="mt-1 text-xs text-teal-700 dark:text-teal-300">Sólo se incluyen casos confirmados.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="p-5 bg-white border border-gray-200 rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                <p class="text-sm text-gray-500">Casos confirmados</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $reporte['total_casos'] }}</p>
            </div>
            <div class="p-5 bg-white border border-gray-200 rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                <p class="text-sm text-gray-500">Evidencias</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $reporte['total_evidencias'] }}</p>
            </div>
            <div class="p-5 bg-white border border-gray-200 rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                <p class="text-sm text-gray-500">Unidades diferentes</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $reporte['total_unidades'] }}</p>
            </div>
        </div>

        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-600 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3">Primera evidencia</th>
                            <th class="px-4 py-3">Unidad</th>
                            <th class="px-4 py-3 text-center">Evidencias</th>
                            <th class="px-4 py-3">Resultado</th>
                            <th class="px-4 py-3">Revisor</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($reporte['casos'] as $caso)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $caso['primera_evidencia_at']->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $caso['primera_evidencia_at']->format('H:i') }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-gray-900 dark:text-white">{{ $caso['placa'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $caso['vehiculo'] }}</p>
                                </td>
                                <td class="px-4 py-4 font-semibold text-center">{{ $caso['evidencias'] }}</td>
                                <td class="px-4 py-4">{{ str($caso['resultado'])->replace('_', ' ')->title() }}</td>
                                <td class="px-4 py-4">{{ $caso['revisor'] }}</td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('inspecciones.recepcion.detalle', $caso['caso_id']) }}" class="font-semibold text-teal-700 hover:underline">Caso</a>
                                    <span class="mx-1 text-gray-300">·</span>
                                    <a href="{{ route('inspecciones.detalle', $caso['inspeccion_id']) }}" class="font-semibold text-blue-700 hover:underline">Inspección</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No hay casos confirmados en esta semana.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-livewire.monitoreo-layout>
