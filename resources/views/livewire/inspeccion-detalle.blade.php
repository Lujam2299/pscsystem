<x-livewire.monitoreo-layout
    :breadcrumb-items="[
        ['icon' => 'ti-home', 'url' => route('admin.monitoreoDashboard')],
        ['icon' => 'ti-clipboard-check', 'url' => route('inspecciones.index'), 'label' => 'Inspecciones'],
        ['icon' => 'ti-file-description', 'label' => 'Inspección #' . $inspeccion->id],
    ]"
    title-main="Inspección #{{ $inspeccion->id }}"
    help-text="Detalle operativo y evidencia privada asociada a la unidad."
>
    <div class="max-w-6xl mx-auto space-y-6">
        @if (session('success'))
            <div class="p-4 text-sm text-emerald-800 border border-emerald-200 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-200 dark:border-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <section class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                <h2 class="mb-3 font-bold text-blue-700 dark:text-blue-300"><i class="mr-2 ti ti-car"></i>Unidad</h2>
                <p class="font-mono text-2xl font-bold text-gray-900 dark:text-white">{{ $inspeccion->unidad->placas }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $inspeccion->unidad->marca }} {{ $inspeccion->unidad->modelo }}</p>
                <a href="{{ route('vehiculos.detalle', $inspeccion->unidad) }}" class="inline-block mt-3 text-sm font-semibold text-blue-600">Ver vehículo</a>
            </section>

            <section class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                <h2 class="mb-3 font-bold text-blue-700 dark:text-blue-300"><i class="mr-2 ti ti-calendar"></i>Inspección</h2>
                <dl class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
                    <div><dt class="font-semibold inline">Fecha:</dt> <dd class="inline">{{ $inspeccion->fecha_inspeccion->format('d/m/Y H:i') }}</dd></div>
                    <div><dt class="font-semibold inline">Tipo:</dt> <dd class="inline">{{ str($inspeccion->tipo)->replace('_', ' ')->title() }}</dd></div>
                    <div><dt class="font-semibold inline">Kilometraje:</dt> <dd class="inline">{{ $inspeccion->kilometraje !== null ? number_format($inspeccion->kilometraje) : 'No informado' }}</dd></div>
                    <div><dt class="font-semibold inline">Origen:</dt> <dd class="inline">{{ ucfirst($inspeccion->origen) }}</dd></div>
                </dl>
            </section>

            <section class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                <h2 class="mb-3 font-bold text-blue-700 dark:text-blue-300"><i class="mr-2 ti ti-status-change"></i>Resultado</h2>
                <p class="font-semibold text-gray-900 dark:text-white">{{ str($inspeccion->resultado)->replace('_', ' ')->title() }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Estado: {{ str($inspeccion->estado)->replace('_', ' ')->title() }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Reportó: {{ $inspeccion->reportado_por ?: 'No informado' }}</p>
            </section>
        </div>

        @if ($inspeccion->observaciones)
            <section class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                <h2 class="mb-2 font-bold text-gray-900 dark:text-white">Observaciones</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line dark:text-gray-200">{{ $inspeccion->observaciones }}</p>
            </section>
        @endif

        <section class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 dark:text-white">Evidencias ({{ $inspeccion->evidencias->count() }})</h2>
                <span class="text-xs text-gray-500"><i class="mr-1 ti ti-lock"></i>Archivos privados</span>
            </div>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($inspeccion->evidencias as $evidencia)
                    <a href="{{ route('inspecciones.evidencias.show', $evidencia) }}" target="_blank" rel="noopener"
                        class="block overflow-hidden border border-gray-200 group rounded-xl dark:border-gray-700">
                        <img src="{{ route('inspecciones.evidencias.show', $evidencia) }}" alt="Evidencia {{ $loop->iteration }}"
                            loading="lazy" class="object-cover w-full h-48 transition-transform group-hover:scale-105">
                        <div class="p-2 text-xs text-gray-600 truncate dark:text-gray-300">{{ $evidencia->nombre_original }}</div>
                    </a>
                @endforeach
            </div>
        </section>

        <div class="flex flex-wrap justify-end gap-3">
            @can(\App\Support\Authorization\Permission::INSPECTIONS_MANAGE)
                @if ($inspeccion->servicio_id)
                    <a href="{{ route('servicio.detalle', $inspeccion->servicio_id) }}" class="px-4 py-2 text-sm font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700">
                        Ver reparación asociada
                    </a>
                @endif
            @endcan
            <a href="{{ route('inspecciones.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Regresar</a>
        </div>
    </div>
</x-livewire.monitoreo-layout>
