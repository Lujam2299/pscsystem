<x-livewire.monitoreo-layout
    :breadcrumb-items="[
        ['icon' => 'ti-home', 'url' => route('admin.monitoreoDashboard')],
        ['icon' => 'ti-inbox', 'label' => 'Evidencias recibidas', 'url' => route('inspecciones.recepcion.index')],
        ['icon' => 'ti-eye-check', 'label' => 'Caso #' . $caso->id],
    ]"
    title-main="Revisión del caso #{{ $caso->id }}"
    help-text="Confirma si los mensajes e imágenes corresponden a la misma unidad."
>
    <div class="space-y-6">
        @if (session('success'))
            <div class="p-4 text-sm text-emerald-800 border border-emerald-200 rounded-xl bg-emerald-50">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <section class="space-y-4 lg:col-span-2">
                <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-gray-900 dark:text-white">Línea de tiempo recibida</h2>
                            <p class="text-xs text-gray-500">Los mensajes excluidos no participan en la detección ni en la inspección.</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold bg-amber-100 rounded-full text-amber-700">{{ str($caso->estado)->replace('_', ' ')->title() }}</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        @foreach ($caso->mensajes as $mensaje)
                            <article class="p-4 border rounded-xl {{ $mensaje->incluido ? 'border-teal-300 bg-teal-50/40 dark:bg-teal-900/10' : 'border-gray-200 opacity-60 dark:border-gray-700' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500">{{ $mensaje->fecha_mensaje->format('d/m/Y H:i') }} · {{ $mensaje->remitente ?: 'Sin remitente' }}</p>
                                        @if ($mensaje->texto)<p class="mt-2 text-sm text-gray-800 whitespace-pre-line dark:text-gray-200">{{ $mensaje->texto }}</p>@endif
                                    </div>
                                    @if (!in_array($caso->estado, ['confirmado', 'descartado'], true))
                                        <button wire:click="alternarMensaje({{ $mensaje->id }})" type="button" class="text-xs font-semibold {{ $mensaje->incluido ? 'text-red-600' : 'text-teal-700' }}">
                                            {{ $mensaje->incluido ? 'Excluir' : 'Incluir' }}
                                        </button>
                                    @endif
                                </div>
                                @if ($mensaje->archivos->isNotEmpty())
                                    <div class="grid grid-cols-2 gap-3 mt-3 sm:grid-cols-3">
                                        @foreach ($mensaje->archivos as $archivo)
                                            <a href="{{ route('inspecciones.recepcion.archivos.show', $archivo) }}" target="_blank" class="block overflow-hidden bg-gray-100 border rounded-lg aspect-square">
                                                <img src="{{ route('inspecciones.recepcion.archivos.show', $archivo) }}" alt="{{ $archivo->nombre_original }}" class="object-cover w-full h-full">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    @error('mensajes') <p class="mt-3 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </section>

            <aside class="space-y-4">
                <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                    <h2 class="font-bold text-gray-900 dark:text-white">Detección</h2>
                    @if ($caso->unidadSugerida)
                        <p class="mt-3 text-sm">Sugerencia: <strong>{{ $caso->unidadSugerida->placas }}</strong></p>
                        <p class="text-xs text-gray-500">{{ $caso->unidadSugerida->marca }} {{ $caso->unidadSugerida->modelo }} · {{ $caso->confianza }}% de confianza</p>
                    @elseif (count($caso->placas_candidatas ?? []) > 1)
                        <p class="mt-3 text-sm font-semibold text-red-700">Caso ambiguo</p>
                        @foreach ($caso->placas_candidatas as $candidata)
                            <p class="mt-1 text-xs text-gray-600">{{ $candidata['placa'] }} · {{ $candidata['descripcion'] }}</p>
                        @endforeach
                    @else
                        <p class="mt-3 text-sm text-gray-500">No se detectó una placa confiable.</p>
                    @endif
                </div>

                <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                    <h2 class="font-bold text-gray-900 dark:text-white">Decisión humana</h2>
                    <label class="block mt-4">
                        <span class="text-sm font-medium">Unidad</span>
                        <select wire:model="unidadId" @disabled(in_array($caso->estado, ['confirmado', 'descartado'], true)) class="w-full mt-1 border-gray-300 rounded-lg disabled:opacity-60 dark:bg-gray-900 dark:border-gray-600">
                            <option value="">Selecciona la unidad</option>
                            @foreach ($unidades as $unidad)
                                <option value="{{ $unidad->id }}">{{ $unidad->placas }} · {{ $unidad->marca }} {{ $unidad->modelo }}</option>
                            @endforeach
                        </select>
                        @error('unidadId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block mt-3">
                        <span class="text-sm font-medium">Notas de revisión</span>
                        <textarea wire:model="notasRevision" @disabled(in_array($caso->estado, ['confirmado', 'descartado'], true)) rows="3" class="w-full mt-1 border-gray-300 rounded-lg disabled:opacity-60 dark:bg-gray-900 dark:border-gray-600"></textarea>
                    </label>
                    @if (!in_array($caso->estado, ['confirmado', 'descartado'], true))
                        <button wire:click="guardarRevision" type="button" class="w-full px-4 py-2 mt-3 text-sm font-semibold text-teal-700 bg-teal-50 rounded-lg">Guardar sin confirmar</button>
                    @endif
                </div>
            </aside>
        </div>

        @if (!in_array($caso->estado, ['confirmado', 'descartado'], true))
            <section class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                <h2 class="font-bold text-gray-900 dark:text-white">Crear inspección confirmada</h2>
                <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2 lg:grid-cols-4">
                    <label><span class="text-sm">Tipo</span><select wire:model="tipo" class="w-full mt-1 border-gray-300 rounded-lg"><option value="cambio_turno">Cambio de turno</option><option value="entrega">Entrega</option><option value="recepcion">Recepción</option><option value="revision">Revisión</option><option value="mantenimiento">Mantenimiento</option></select></label>
                    <label><span class="text-sm">Resultado</span><select wire:model="resultado" class="w-full mt-1 border-gray-300 rounded-lg"><option value="sin_novedad">Sin novedad</option><option value="con_observaciones">Con observaciones</option><option value="requiere_revision">Requiere revisión</option><option value="requiere_reparacion">Requiere reparación</option></select></label>
                    <label><span class="text-sm">Kilometraje</span><input wire:model="kilometraje" type="number" min="0" class="w-full mt-1 border-gray-300 rounded-lg"></label>
                    <label><span class="text-sm">Reportado por</span><input wire:model="reportadoPor" type="text" class="w-full mt-1 border-gray-300 rounded-lg"></label>
                    <label class="md:col-span-2 lg:col-span-4"><span class="text-sm">Observaciones consolidadas</span><textarea wire:model="observaciones" rows="4" class="w-full mt-1 border-gray-300 rounded-lg"></textarea></label>
                </div>
                <div class="flex flex-col justify-end gap-3 mt-5 sm:flex-row">
                    <button wire:click="descartar" wire:confirm="El caso se marcará como descartado. ¿Deseas continuar?" type="button" class="px-4 py-2 text-sm font-semibold text-red-700 bg-red-50 rounded-lg">Descartar caso</button>
                    <button wire:click="confirmar" wire:confirm="Se creará una inspección con los mensajes e imágenes incluidos. ¿Confirmas que corresponden a la unidad seleccionada?" type="button" class="px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded-lg">Confirmar y crear inspección</button>
                </div>
            </section>
        @elseif ($caso->inspeccion)
            <a href="{{ route('inspecciones.detalle', $caso->inspeccion) }}" class="inline-flex px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Ver inspección creada</a>
        @endif
    </div>
</x-livewire.monitoreo-layout>
