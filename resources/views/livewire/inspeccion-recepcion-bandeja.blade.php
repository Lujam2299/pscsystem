<x-livewire.monitoreo-layout
    :breadcrumb-items="[
        ['icon' => 'ti-home', 'url' => route('admin.monitoreoDashboard')],
        ['icon' => 'ti-inbox', 'label' => 'Evidencias recibidas'],
    ]"
    title-main="Evidencias recibidas"
    help-text="Revisa mensajes e imágenes antes de convertirlos en una inspección."
>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Bandeja de revisión</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">La placa sugerida nunca confirma automáticamente una unidad.</p>
            </div>
            @can(\App\Support\Authorization\Permission::INSPECTION_INBOX_MANAGE)
                <button wire:click="abrirImportador" type="button" class="px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700">
                    <i class="ti ti-upload"></i> Importar caso de prueba
                </button>
            @endcan
        </div>

        @if ($mostrarImportador)
            <form wire:submit="importar" class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">Simular secuencia de mensajes</h3>
                        <p class="text-xs text-gray-500">Los textos se guardarán un minuto antes, junto a las imágenes y un minuto después.</p>
                    </div>
                    <button wire:click="cancelarImportador" type="button" class="text-gray-500 hover:text-gray-800"><i class="text-xl ti ti-x"></i></button>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Conversación *</span>
                        <input wire:model="conversacion" type="text" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        @error('conversacion') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Remitente</span>
                        <input wire:model="remitente" type="text" placeholder="Conductor o supervisor" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha base *</span>
                        <input wire:model="fechaBase" type="datetime-local" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mensaje anterior</span>
                        <textarea wire:model="textoAnterior" rows="2" placeholder="Ejemplo: Unidad saliendo de base" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white"></textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Texto adjunto a las imágenes</span>
                        <textarea wire:model="textoImagenes" rows="2" placeholder="Texto enviado con el álbum, si existe" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white"></textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mensaje posterior</span>
                        <textarea wire:model="textoPosterior" rows="2" placeholder="Aquí puede llegar la placa después de las fotografías" class="w-full mt-1 border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white"></textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Imágenes *</span>
                        <input wire:model="imagenes" type="file" multiple accept="image/jpeg,image/png,image/webp" class="block w-full mt-1 text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-semibold file:text-teal-700">
                        <span class="block mt-1 text-xs text-gray-500">Entre 1 y 20 imágenes; máximo 10 MB cada una.</span>
                        <div wire:loading wire:target="imagenes" class="mt-2 text-sm text-teal-600">Preparando imágenes…</div>
                        @error('imagenes') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                        @error('imagenes.*') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>

                <div class="flex justify-end gap-3 mt-5">
                    <button wire:click="cancelarImportador" type="button" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="importar,imagenes" class="px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded-lg disabled:opacity-50">Crear caso para revisión</button>
                </div>
            </form>
        @endif

        <div class="p-4 bg-white border border-gray-200 rounded-2xl dark:bg-gray-800 dark:border-gray-700">
            <select wire:model.live="filtroEstado" class="w-full border-gray-300 rounded-lg md:w-72 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="placa_sugerida">Placa sugerida</option>
                <option value="ambiguo">Ambiguo</option>
                <option value="confirmado">Confirmado</option>
                <option value="descartado">Descartado</option>
            </select>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @forelse ($casos as $caso)
                <a href="{{ route('inspecciones.recepcion.detalle', $caso) }}" class="block p-5 bg-white border border-gray-200 shadow-sm rounded-2xl hover:border-teal-400 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white">Caso #{{ $caso->id }}</p>
                            <p class="text-xs text-gray-500">{{ $caso->created_at->format('d/m/Y H:i') }} · {{ $caso->mensajes_count }} mensaje(s)</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $caso->estado === 'ambiguo' ? 'bg-red-100 text-red-700' : ($caso->estado === 'confirmado' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ str($caso->estado)->replace('_', ' ')->title() }}
                        </span>
                    </div>
                    <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                        @if ($caso->unidadConfirmada)
                            Confirmada: <strong>{{ $caso->unidadConfirmada->placas }}</strong>
                        @elseif ($caso->unidadSugerida)
                            Sugerida: <strong>{{ $caso->unidadSugerida->placas }}</strong> · confianza {{ $caso->confianza }}%
                        @elseif (count($caso->placas_candidatas ?? []) > 1)
                            {{ count($caso->placas_candidatas) }} unidades candidatas; requiere decisión humana.
                        @else
                            Sin placa detectada; requiere revisión humana.
                        @endif
                    </div>
                </a>
            @empty
                <div class="p-10 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl lg:col-span-2 dark:bg-gray-800 dark:border-gray-700">No hay casos en esta bandeja.</div>
            @endforelse
        </div>

        {{ $casos->links() }}
    </div>
</x-livewire.monitoreo-layout>
