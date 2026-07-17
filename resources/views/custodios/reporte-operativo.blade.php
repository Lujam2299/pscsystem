<x-app-layout>
    <x-navbar />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span class="p-2 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h6l6 6v10a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            Reporte operativo
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Misión #{{ $mision->id }} · {{ $mision->cliente ?? 'Cliente no definido' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('misiones.reporte-operativo.pdf', $mision->id) }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">
                            Descargar PDF
                        </a>
                        <a href="{{ route('custodios.misionesTerminadas') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-600 text-white text-sm font-semibold hover:bg-gray-700 transition">
                            Regresar
                        </a>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/30 border border-slate-200 dark:border-slate-700 p-4">
                            <p class="text-xs uppercase tracking-wide font-bold text-slate-500 dark:text-slate-400">Estado</p>
                            <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $mision->estado_normalizado }}</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 p-4">
                            <p class="text-xs uppercase tracking-wide font-bold text-blue-700 dark:text-blue-300">Itinerarios</p>
                            <p class="mt-1 text-3xl font-bold text-blue-900 dark:text-blue-100">{{ count($eventosPlanos) }}</p>
                        </div>
                        <div class="rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 p-4">
                            <p class="text-xs uppercase tracking-wide font-bold text-purple-700 dark:text-purple-300">Total gastos</p>
                            <p class="mt-1 text-3xl font-bold text-purple-900 dark:text-purple-100">${{ number_format($totalGastos, 2) }}</p>
                        </div>
                        <div class="rounded-xl bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-100 dark:border-cyan-800 p-4">
                            <p class="text-xs uppercase tracking-wide font-bold text-cyan-700 dark:text-cyan-300">Cierres</p>
                            <p class="mt-1 text-3xl font-bold text-cyan-900 dark:text-cyan-100">{{ $cierres->count() }}</p>
                        </div>
                    </div>

                    <section class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Datos generales</h2>
                        </div>
                        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Periodo</p>
                                <p class="text-gray-900 dark:text-white">{{ optional(\Carbon\Carbon::parse($mision->fecha_inicio))->format('d/m/Y') }} - {{ optional(\Carbon\Carbon::parse($mision->fecha_fin))->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Tipo de servicio</p>
                                <p class="text-gray-900 dark:text-white">{{ $mision->tipo_servicio ?? 'No definido' }}</p>
                            </div>
                            <div>
                                <p class="font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Agentes</p>
                                <p class="text-gray-900 dark:text-white">{{ $agentes->pluck('name')->implode(', ') ?: 'Sin agentes' }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-800 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Revisión administrativa</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Candado previo a pre-facturación.</p>
                            </div>
                            <span class="inline-flex items-center self-start rounded-full px-3 py-1 text-xs font-semibold {{ $mision->revision_tone }}">
                                {{ $mision->revision_estado_normalizado }}
                            </span>
                        </div>

                        <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div class="space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
                                        <p class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Agentes</p>
                                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $agentes->count() }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
                                        <p class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Gastos</p>
                                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $gastos->count() }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
                                        <p class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Cierres</p>
                                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $cierres->count() }}</p>
                                    </div>
                                </div>

                                @if($mision->revisionUser)
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        Última revisión por <span class="font-semibold">{{ $mision->revisionUser->name }}</span>
                                        @if($mision->revision_at)
                                            el {{ $mision->revision_at->format('d/m/Y H:i') }}
                                        @endif
                                    </p>
                                @endif

                                @if(filled($mision->revision_observaciones))
                                    <div class="rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 p-3">
                                        <p class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Observaciones actuales</p>
                                        <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $mision->revision_observaciones }}</p>
                                    </div>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('misiones.revision.update', $mision) }}" class="space-y-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">Estado de revisión</label>
                                    <select name="revision_estado" required
                                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                        @foreach(['Pendiente de revisión', 'En revisión', 'Lista para facturar', 'Observada / requiere corrección'] as $estadoRevision)
                                            <option value="{{ $estadoRevision }}" @selected(old('revision_estado', $mision->revision_estado_normalizado) === $estadoRevision)>
                                                {{ $estadoRevision }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('revision_estado')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">Observaciones internas</label>
                                    <textarea name="revision_observaciones" rows="4"
                                              class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                              placeholder="Ej. Falta validar una evidencia, todo correcto, corregir gasto duplicado...">{{ old('revision_observaciones', $mision->revision_observaciones) }}</textarea>
                                    @error('revision_observaciones')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                        class="inline-flex w-full justify-center items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                    Guardar revisión
                                </button>
                            </form>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Itinerario cronológico</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/40">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Hora</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Agente</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Descripción</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Ubicación</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($eventosPlanos as $evento)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $evento['fecha'] ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $evento['hora'] ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $evento['user_name'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $evento['descripcion'] ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $evento['ubicacion'] ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No hay itinerarios registrados.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 bg-purple-50 dark:bg-purple-900/20 border-b border-purple-100 dark:border-purple-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Gastos</h2>
                            <p class="text-sm font-semibold text-purple-800 dark:text-purple-200">Viáticos: ${{ number_format($totalViaticos, 2) }} · Gasolina: ${{ number_format($totalGasolina, 2) }}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/40">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Agente</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Tipo</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Categoría</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($gastos as $gasto)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $gasto->Fecha ? \Carbon\Carbon::parse($gasto->Fecha)->format('d/m/Y') : '-' }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $gasto->user?->name ?? $agentesNombres[$gasto->user_id] ?? 'Agente #' . $gasto->user_id }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $gasto->Tipo }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $gasto->categoria_label ?? $gasto->Categoria ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-bold text-gray-900 dark:text-white">${{ number_format((float) $gasto->Monto, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No hay gastos registrados.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 bg-cyan-50 dark:bg-cyan-900/20 border-b border-cyan-100 dark:border-cyan-800">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Cierres operativos</h2>
                        </div>
                        <div class="p-5 space-y-4">
                            @forelse($cierres as $cierre)
                                <article class="rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-3">
                                        <h3 class="font-bold text-gray-900 dark:text-white">{{ $cierre->fecha ? $cierre->fecha->format('d/m/Y') : 'Sin fecha' }}</h3>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $cierre->user?->name ?? 'Escolta #' . $cierre->user_id }}</span>
                                    </div>
                                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $cierre->resumen }}</p>
                                    @foreach(['novedades' => 'Novedades', 'incidencias' => 'Incidencias', 'pendientes' => 'Pendientes', 'observaciones' => 'Observaciones'] as $campo => $label)
                                        @if(filled($cierre->{$campo}))
                                            <p class="mt-3 text-xs font-bold uppercase text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $cierre->{$campo} }}</p>
                                        @endif
                                    @endforeach
                                </article>
                            @empty
                                <p class="text-center text-gray-500 dark:text-gray-400 py-6">No hay cierres operativos registrados.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
