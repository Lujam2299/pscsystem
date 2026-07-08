<x-app-layout>
    <x-navbar />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span class="p-2 rounded-lg bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6M9 3h6a2 2 0 012 2v1h1a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h1V5a2 2 0 012-2z" />
                                </svg>
                            </span>
                            Cierres operativos
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Misión #{{ $mision->id }} · {{ $mision->cliente ?? 'Cliente no definido' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.detalleMision', $mision->id) }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-sky-600 text-white text-sm font-semibold hover:bg-sky-700 transition">
                            Ver detalle
                        </a>
                        <a href="{{ route('custodios.misionesTerminadas') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-600 text-white text-sm font-semibold hover:bg-gray-700 transition">
                            Regresar
                        </a>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="rounded-xl bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-100 dark:border-cyan-800 p-4">
                            <p class="text-xs uppercase tracking-wide font-bold text-cyan-700 dark:text-cyan-300">Total cierres</p>
                            <p class="text-3xl font-bold text-cyan-900 dark:text-cyan-100 mt-1">{{ $cierres->count() }}</p>
                        </div>
                        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 p-4">
                            <p class="text-xs uppercase tracking-wide font-bold text-emerald-700 dark:text-emerald-300">Escoltas con cierre</p>
                            <p class="text-3xl font-bold text-emerald-900 dark:text-emerald-100 mt-1">{{ $cierres->pluck('user_id')->unique()->count() }}</p>
                        </div>
                        <div class="rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4">
                            <p class="text-xs uppercase tracking-wide font-bold text-indigo-700 dark:text-indigo-300">Días reportados</p>
                            <p class="text-3xl font-bold text-indigo-900 dark:text-indigo-100 mt-1">{{ $cierres->pluck('fecha')->map(fn ($fecha) => optional($fecha)->format('Y-m-d'))->unique()->count() }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($cierres as $cierre)
                            <article class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ $cierre->fecha ? $cierre->fecha->format('d/m/Y') : 'Sin fecha' }}
                                        </h2>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $cierre->user?->name ?? 'Escolta #' . $cierre->user_id }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center self-start sm:self-center px-3 py-1 rounded-full text-xs font-semibold bg-white text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700">
                                        Registrado {{ $cierre->created_at ? $cierre->created_at->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </div>

                                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Resumen operativo</p>
                                        <p class="text-sm leading-6 text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $cierre->resumen }}</p>
                                    </div>

                                    @foreach([
                                        'novedades' => 'Novedades',
                                        'incidencias' => 'Incidencias o riesgos',
                                        'pendientes' => 'Pendientes',
                                        'observaciones' => 'Observaciones',
                                    ] as $campo => $etiqueta)
                                        @if(filled($cierre->{$campo}))
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">{{ $etiqueta }}</p>
                                                <p class="text-sm leading-6 text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $cierre->{$campo} }}</p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
