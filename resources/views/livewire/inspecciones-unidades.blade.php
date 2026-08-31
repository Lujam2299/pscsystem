<x-livewire.monitoreo-layout
    :breadcrumb-items="[
        ['icon' => 'ti-home', 'url' => route('admin.monitoreoDashboard')],
        ['icon' => 'ti-clipboard-check', 'label' => 'Inspecciones de unidades'],
    ]"
    title-main="Inspecciones de unidades"
    help-text="Registra revisiones, entregas y recepciones de las unidades con evidencia fotográfica privada."
>
    <div class="space-y-6">
        @if (session('success'))
            <div class="p-4 text-sm text-emerald-800 border border-emerald-200 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-200 dark:border-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Historial de inspecciones</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Las evidencias se conservan en almacenamiento privado.</p>
            </div>
            @can(\App\Support\Authorization\Permission::INSPECTIONS_MANAGE)
                <button wire:click="mostrarAlta" type="button"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    <i class="ti ti-plus"></i> Nueva inspección
                </button>
            @endcan
        </div>

        @if ($mostrarFormulario)
            <form wire:submit="guardar" class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-900 dark:text-white">Registrar inspección</h3>
                    <button wire:click="cancelarAlta" type="button" class="text-gray-500 hover:text-gray-800 dark:hover:text-white">
                        <i class="text-xl ti ti-x"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <label class="block lg:col-span-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Unidad *</span>
                        <select wire:model="form.unidad_id" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="">Selecciona una unidad</option>
                            @foreach ($unidadesDisponibles as $unidad)
                                <option value="{{ $unidad->id }}">{{ $unidad->placas }} · {{ $unidad->marca }} {{ $unidad->modelo }}</option>
                            @endforeach
                        </select>
                        @error('form.unidad_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha y hora *</span>
                        <input wire:model="form.fecha_inspeccion" type="datetime-local" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        @error('form.fecha_inspeccion') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo *</span>
                        <select wire:model="form.tipo" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="cambio_turno">Cambio de turno</option>
                            <option value="entrega">Entrega</option>
                            <option value="recepcion">Recepción</option>
                            <option value="revision">Revisión</option>
                            <option value="mantenimiento">Mantenimiento</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Kilometraje</span>
                        <input wire:model="form.kilometraje" type="number" min="0" placeholder="Ej. 2176" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        @error('form.kilometraje') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Resultado *</span>
                        <select wire:model="form.resultado" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="sin_novedad">Sin novedad</option>
                            <option value="con_observaciones">Con observaciones</option>
                            <option value="requiere_revision">Requiere revisión</option>
                            <option value="requiere_reparacion">Requiere reparación</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Reportado por</span>
                        <input wire:model="form.reportado_por" type="text" maxlength="150" placeholder="Nombre del conductor o supervisor" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </label>

                    <label class="block md:col-span-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</span>
                        <textarea wire:model="form.observaciones" rows="3" maxlength="5000" placeholder="Estado, daños, niveles, herramientas o detalles relevantes" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white"></textarea>
                        @error('form.observaciones') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="block md:col-span-2 lg:col-span-3">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Evidencias *</span>
                        <input wire:model="evidencias" type="file" multiple accept="image/jpeg,image/png,image/webp"
                            class="block w-full mt-1 text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:text-gray-300">
                        <span class="block mt-1 text-xs text-gray-500">De 1 a 20 imágenes JPG, PNG o WebP; máximo 10 MB por archivo.</span>
                        <div wire:loading wire:target="evidencias" class="mt-2 text-sm text-blue-600">Preparando imágenes…</div>
                        @error('evidencias') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                        @error('evidencias.*') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>

                <div class="flex justify-end gap-3 mt-5">
                    <button wire:click="cancelarAlta" type="button" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="guardar,evidencias"
                        class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg disabled:opacity-50 hover:bg-blue-700">
                        Guardar inspección
                    </button>
                </div>
            </form>
        @endif

        <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5">
                <select wire:model.live="filtroUnidad" class="border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white lg:col-span-2">
                    <option value="">Todas las unidades</option>
                    @foreach ($unidadesFiltro as $unidad)
                        <option value="{{ $unidad->id }}">{{ $unidad->placas }} · {{ $unidad->marca }} {{ $unidad->modelo }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filtroResultado" class="border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    <option value="">Todos los resultados</option>
                    <option value="sin_novedad">Sin novedad</option>
                    <option value="con_observaciones">Con observaciones</option>
                    <option value="requiere_revision">Requiere revisión</option>
                    <option value="requiere_reparacion">Requiere reparación</option>
                </select>
                <input wire:model.live.debounce.400ms="filtroDesde" type="date" title="Desde" class="border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                <input wire:model.live.debounce.400ms="filtroHasta" type="date" title="Hasta" class="border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
            </div>
        </div>

        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr class="text-xs tracking-wide text-left text-gray-600 uppercase dark:text-gray-300">
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Unidad</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Resultado</th>
                            <th class="px-4 py-3 text-center">Evidencias</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($inspecciones as $inspeccion)
                            <tr class="text-sm text-gray-700 dark:text-gray-200">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $inspeccion->fecha_inspeccion->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-mono font-bold">{{ $inspeccion->unidad->placas }}</span>
                                    <span class="block text-xs text-gray-500">{{ $inspeccion->unidad->marca }} {{ $inspeccion->unidad->modelo }}</span>
                                </td>
                                <td class="px-4 py-3">{{ str($inspeccion->tipo)->replace('_', ' ')->title() }}</td>
                                <td class="px-4 py-3">{{ str($inspeccion->resultado)->replace('_', ' ')->title() }}</td>
                                <td class="px-4 py-3 text-center">{{ $inspeccion->evidencias_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('inspecciones.detalle', $inspeccion) }}" class="font-semibold text-blue-600 hover:text-blue-800">Ver detalle</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">No se encontraron inspecciones.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $inspecciones->links() }}</div>
        </div>
    </div>
</x-livewire.monitoreo-layout>
