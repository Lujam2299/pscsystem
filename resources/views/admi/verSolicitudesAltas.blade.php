<x-app-layout>
    <x-navbar></x-navbar>

    <div class="min-h-screen bg-slate-50 px-4 py-8 dark:bg-gray-900 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="bg-gradient-to-r from-indigo-600 via-blue-600 to-sky-500 px-6 py-8 text-white sm:px-8">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-100">
                                Panel administrativo
                            </p>
                            <h1 class="mt-2 text-3xl font-bold tracking-tight">
                                Últimas modificaciones
                            </h1>
                            <p class="mt-2 max-w-2xl text-sm text-blue-50">
                                Consulta las solicitudes de alta aceptadas y actualizadas durante el día de hoy.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white/15 px-5 py-4 text-center shadow-sm ring-1 ring-white/20 backdrop-blur">
                            <p class="text-3xl font-bold">{{ $solicitudes->count() }}</p>
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-100">
                                registros de hoy
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    @if(session('success'))
                        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200" role="alert">
                            <i class="ti ti-circle-check mt-0.5 text-lg"></i>
                            <p class="text-sm font-medium">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if($solicitudes->isEmpty())
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center dark:border-gray-600 dark:bg-gray-900/40">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                <i class="ti ti-inbox text-3xl"></i>
                            </div>
                            <h2 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">
                                Sin modificaciones recientes
                            </h2>
                            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500 dark:text-gray-400">
                                No hay altas aceptadas con actualización registrada el día de hoy.
                            </p>
                            <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                                <i class="ti ti-arrow-left"></i>
                                Regresar al dashboard
                            </a>
                        </div>
                    @else
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
                                    <thead class="bg-slate-100 dark:bg-gray-700/70">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-300">No.</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-300">Colaborador</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-300">CURP</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-300">RFC</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-300">Editado por</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-300">Estado</th>
                                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-300">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        @foreach($solicitudes as $solicitud)
                                            <tr class="transition hover:bg-slate-50 dark:hover:bg-gray-700/40">
                                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700 dark:text-gray-200">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="font-semibold text-slate-900 dark:text-white">
                                                        {{ $solicitud->nombre }} {{ $solicitud->apellido_paterno }} {{ $solicitud->apellido_materno }}
                                                    </div>
                                                    <div class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                                                        Actualizado {{ optional($solicitud->updated_at)->format('d/m/Y H:i') }}
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 dark:text-gray-300">
                                                    {{ $solicitud->curp ?: '—' }}
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 dark:text-gray-300">
                                                    {{ $solicitud->rfc ?: '—' }}
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 dark:text-gray-300">
                                                    {{ $solicitud->ultima_edicion ?: '—' }}
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4">
                                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold
                                                        {{ $solicitud->status === 'En Proceso'
                                                            ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'
                                                            : ($solicitud->status === 'Aceptada'
                                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200'
                                                                : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200') }}">
                                                        {{ $solicitud->status }}
                                                    </span>
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                                    @if($solicitud->usuario)
                                                        <a href="{{ route('user.verFicha', $solicitud->usuario->id) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800">
                                                            Ver más
                                                            <i class="ti ti-arrow-right"></i>
                                                        </a>
                                                    @else
                                                        <span class="inline-flex items-center rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400 dark:bg-gray-700 dark:text-gray-500">
                                                            Sin ficha
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                <i class="ti ti-arrow-left"></i>
                                Regresar al dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
