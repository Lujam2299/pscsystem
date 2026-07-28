<x-app-layout>
    <x-navbar></x-navbar>

    <div class="min-h-screen bg-slate-50 px-2 py-5 dark:bg-gray-950 sm:px-5 lg:px-8">
        <div class="mx-auto max-w-[1800px]">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <header class="relative overflow-hidden bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-600 px-6 py-7 sm:px-8">
                    <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10"></div>
                    <div class="absolute bottom-0 right-40 h-24 w-24 rounded-full bg-teal-300/10"></div>
                    <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="mb-2 inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-50">
                                Nóminas · Control quincenal
                            </div>
                            <h1 class="flex items-center text-2xl font-bold text-white sm:text-3xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-3 h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Preparación de nómina
                            </h1>
                            <p class="mt-2 max-w-2xl text-sm text-emerald-50/90">
                                Revisa incidencias, valida pendientes y prepara el concentrado que utilizará el personal de Nóminas.
                            </p>
                        </div>
                        <div class="rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white backdrop-blur-sm">
                            <div class="font-semibold">Flujo recomendado</div>
                            <div class="mt-1 text-emerald-50/90">Filtrar → corregir pendientes → revisar importes → exportar Excel</div>
                        </div>
                    </div>
                </header>

                <main class="p-4 sm:p-6">
                    <livewire:asistencias-tabla />
                </main>

                <footer class="border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/60">
                    <div class="flex justify-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Regresar al panel
                        </a>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</x-app-layout>
