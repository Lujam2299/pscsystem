<div class="py-4 px-2 sm:py-6 sm:px-4">
    <div class="container mx-auto max-w-7xl space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @error('guardado')
            <div class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">{{ $message }}</div>
        @enderror

        @if ($modoEdicion)
            <div class="rounded-xl border-l-4 border-blue-600 bg-blue-50 px-5 py-4 text-blue-900 shadow" role="alert">
                <p class="font-bold">Ya existe una asistencia para el punto y la fecha seleccionados</p>
                <p class="mt-1 text-sm">
                    Se cargaron los valores del registro #{{ $registroId }}. Los cambios que guardes actualizarán ese mismo registro.
                </p>
            </div>
        @endif

        <section class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
            <div class="mb-5 border-b border-gray-200 pb-5 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Registro diario de asistencias</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Selecciona el punto y la fecha. Si ya existe un registro, se cargará para edición.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="punto" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Punto</label>
                    <select id="punto" wire:model.live="punto"
                            class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">Selecciona un punto</option>
                        @foreach ($subpuntosMap as $puntoGeneral => $subpuntos)
                            <optgroup label="{{ $puntoGeneral }}">
                                @foreach ($subpuntos as $subpunto)
                                    <option value="{{ $subpunto['valor'] }}">
                                        @if ($subpunto['codigo'] !== null)({{ str_pad($subpunto['codigo'], 3, '0', STR_PAD_LEFT) }})@endif
                                        {{ $subpunto['nombre'] }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('punto') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="fecha" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha</label>
                    <input id="fecha" type="date" wire:model.live="fecha"
                           class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    @error('fecha') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div wire:loading wire:target="punto,fecha" class="mt-4 text-sm text-blue-600">
                Cargando personal y registro…
            </div>

            @if ($punto && $fecha)
                @unless ($modoEdicion)
                    <div class="mt-5 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-800">
                        <strong>Nuevo registro:</strong> el personal inicia como descanso y puedes cambiar cada estatus.
                    </div>
                @endunless
            @endif
        </section>

        @if ($punto && $fecha && !empty($usuarios))
            <section class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                <div class="mb-5 flex flex-col gap-3 border-b border-gray-200 pb-5 md:flex-row md:items-center md:justify-between dark:border-gray-700">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Personal del punto</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Cada elemento debe quedar como asistencia, falta o descanso.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-sm font-semibold">
                        <span class="rounded-full bg-green-100 px-3 py-1 text-green-800">Asistencias: {{ $resumen['asistencias'] }}</span>
                        <span class="rounded-full bg-red-100 px-3 py-1 text-red-800">Faltas: {{ $resumen['faltas'] }}</span>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">Descansos: {{ $resumen['descansos'] }}</span>
                    </div>
                </div>

                @error('estatusPorUsuario') <p class="mb-4 text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    @foreach ($usuarios as $usuario)
                        @php $id = $usuario['id']; @endphp
                        <article wire:key="usuario-{{ $id }}" class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                @if ($usuario['foto'])
                                    <img src="{{ asset('storage/' . str_replace('storage/', '', $usuario['foto'])) }}"
                                         alt="Foto de {{ $usuario['name'] }}" class="h-14 w-14 rounded-full object-cover">
                                @else
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-teal-600 font-semibold text-white">
                                        {{ mb_substr($usuario['name'], 0, 2) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-semibold text-gray-900 dark:text-white">{{ $usuario['name'] }}</h3>
                                    <p class="truncate text-xs text-gray-500">{{ $usuario['empresa'] }} · {{ $usuario['punto'] }} · {{ $usuario['rol'] }}</p>
                                    @unless ($usuario['activo'])
                                        <span class="text-xs font-medium text-amber-700">Ya no pertenece a la plantilla activa</span>
                                    @endunless
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <label class="cursor-pointer rounded-lg border p-2 text-center text-sm {{ ($estatusPorUsuario[$id] ?? '') === 'asistio' ? 'border-green-500 bg-green-50 text-green-800' : 'border-gray-200 dark:border-gray-600' }}">
                                    <input type="radio" wire:model.live="estatusPorUsuario.{{ $id }}" value="asistio" class="mr-1 text-green-600">
                                    Asistió
                                </label>
                                <label class="cursor-pointer rounded-lg border p-2 text-center text-sm {{ ($estatusPorUsuario[$id] ?? '') === 'falto' ? 'border-red-500 bg-red-50 text-red-800' : 'border-gray-200 dark:border-gray-600' }}">
                                    <input type="radio" wire:model.live="estatusPorUsuario.{{ $id }}" value="falto" class="mr-1 text-red-600">
                                    Faltó
                                </label>
                                <label class="cursor-pointer rounded-lg border p-2 text-center text-sm {{ ($estatusPorUsuario[$id] ?? '') === 'descanso' ? 'border-amber-500 bg-amber-50 text-amber-800' : 'border-gray-200 dark:border-gray-600' }}">
                                    <input type="radio" wire:model.live="estatusPorUsuario.{{ $id }}" value="descanso" class="mr-1 text-amber-600">
                                    Descansó
                                </label>
                            </div>

                            @if (($estatusPorUsuario[$id] ?? '') === 'asistio')
                                <div class="mt-4 space-y-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-700/40">
                                    <div>
                                        <p class="mb-2 text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Turnos trabajados</p>
                                        <div class="flex flex-wrap gap-3 text-sm">
                                            @foreach (['dia' => 'Día', 'tarde' => 'Tarde', 'noche' => 'Noche'] as $valor => $etiqueta)
                                                <label wire:key="turno-{{ $id }}-{{ $valor }}">
                                                    <input type="checkbox" wire:model.live="turnosPorUsuario.{{ $id }}" value="{{ $valor }}" class="mr-1 rounded">
                                                    {{ $etiqueta }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium">Minutos de retardo</label>
                                            <input type="number" min="1" max="599" wire:model="minutosRetardo.{{ $id }}"
                                                   class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800">
                                            @error("minutosRetardo.$id") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium">Horas extra</label>
                                            <input type="number" min="0.01" max="24" step="0.01" wire:model="tiempoExtraHoras.{{ $id }}"
                                                   class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800">
                                            @error("tiempoExtraHoras.$id") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-medium">Observaciones de tiempo extra</label>
                                        <input type="text" wire:model="tiempoExtraObs.{{ $id }}" maxlength="255"
                                               class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-medium">Evidencia fotográfica</label>
                                        @if (isset($fotosNuevas[$id]) && $fotosNuevas[$id])
                                            <img src="{{ $fotosNuevas[$id]->temporaryUrl() }}" class="mb-2 h-28 w-full rounded-lg object-cover" alt="Nueva evidencia">
                                        @elseif (isset($fotosExistentes[$id]) && empty($eliminarFotos[$id]))
                                            <img src="{{ asset('storage/' . $fotosExistentes[$id]) }}" class="mb-2 h-28 w-full rounded-lg object-cover" alt="Evidencia existente">
                                            <label class="mb-2 block text-xs text-red-700">
                                                <input type="checkbox" wire:model="eliminarFotos.{{ $id }}" class="mr-1 rounded">Eliminar evidencia existente
                                            </label>
                                        @endif
                                        <input type="file" wire:model="fotosNuevas.{{ $id }}" accept="image/*"
                                               class="block w-full text-xs text-gray-600 file:mr-2 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-white">
                                        <div wire:loading wire:target="fotosNuevas.{{ $id }}" class="mt-1 text-xs text-blue-600">Subiendo vista previa…</div>
                                        @error("fotosNuevas.$id") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Coberturas de turno</h2>
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Busca al elemento y selecciona obligatoriamente el subpunto que cubrirá.</p>

                <div class="relative max-w-xl">
                    <input type="search" wire:model.live.debounce.300ms="busquedaCobertura" placeholder="Buscar por nombre…"
                           class="w-full rounded-lg border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800">
                    @if (!empty($resultadosCobertura))
                        <div class="absolute z-20 mt-1 w-full rounded-lg border bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800">
                            @foreach ($resultadosCobertura as $resultado)
                                <button type="button" wire:click="seleccionarCobertura({{ $resultado['id'] }})"
                                        class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                    {{ $resultado['nombre'] }} <span class="text-xs text-gray-500">· {{ $resultado['punto_actual'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($coberturas as $indice => $cobertura)
                        <div wire:key="cobertura-{{ $cobertura['id'] }}" class="grid items-center gap-3 rounded-lg border border-gray-200 p-3 md:grid-cols-[1fr_2fr_auto] dark:border-gray-700">
                            <div class="font-medium">{{ $cobertura['nombre'] }}</div>
                            <select wire:change="actualizarSubpuntoCobertura({{ $indice }}, $event.target.value)"
                                    class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800">
                                <option value="">Selecciona el punto cubierto</option>
                                @foreach ($subpuntosMap as $grupo => $subpuntos)
                                    <optgroup label="{{ $grupo }}">
                                        @foreach ($subpuntos as $subpunto)
                                            @if ($subpunto['id'])
                                                <option value="{{ $subpunto['id'] }}" @selected(($cobertura['subpunto_id'] ?? null) == $subpunto['id'])>{{ $subpunto['nombre'] }}</option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <button type="button" wire:click="eliminarCobertura({{ $indice }})" class="text-sm font-medium text-red-600">Eliminar</button>
                            @error("coberturas.$indice.subpunto_id") <p class="text-xs text-red-600 md:col-span-3">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                <label for="observaciones" class="mb-2 block font-semibold text-gray-900 dark:text-white">Observaciones generales</label>
                <textarea id="observaciones" wire:model="observaciones" rows="3" maxlength="255"
                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"></textarea>
                @error('observaciones') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="mt-5 rounded-lg bg-gray-50 p-4 dark:bg-gray-700/40">
                    <p class="font-semibold text-gray-900 dark:text-white">Resumen antes de guardar</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ $resumen['asistencias'] }} asistencias, {{ $resumen['faltas'] }} faltas y {{ $resumen['descansos'] }} descansos.
                    </p>
                </div>

                <div class="mt-5 text-center">
                    <button type="button" wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar,fotosNuevas"
                            class="rounded-lg bg-green-600 px-7 py-3 font-semibold text-white shadow hover:bg-green-700 disabled:opacity-60">
                        <span wire:loading.remove wire:target="guardar">{{ $modoEdicion ? 'Actualizar asistencia' : 'Guardar asistencia' }}</span>
                        <span wire:loading wire:target="guardar">Guardando…</span>
                    </button>
                </div>
            </section>
        @elseif ($punto && $fecha)
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-6 text-center text-blue-800">
                No hay personal asociado al punto seleccionado.
            </div>
        @endif
    </div>
</div>
