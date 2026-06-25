<x-app-layout>
    <x-navbar />

    @if(Auth::user()->rol == 'Supervisor' || Auth::user()->rol == 'SUPERVISOR' || Auth::user()->rol == 'SUPERVISOR OPERATIVO')
        <livewire:supervisor-asistencia-diaria />
    @else
        <div class="py-4 px-2 sm:py-6 sm:px-4">
            <div class="container mx-auto max-w-7xl">
                <section class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                    <div class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-5 md:flex-row md:items-center md:justify-between dark:border-gray-700">
                        <div>
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Módulo de supervisores</p>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Control de asistencias enviadas</h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Consulta qué supervisores registraron asistencia el día de hoy.</p>
                        </div>

                        <div class="rounded-lg bg-blue-50 px-4 py-3 text-blue-800 dark:bg-blue-900/20 dark:text-blue-200">
                            <span class="text-lg font-bold">{{ $supervisores->count() }}</span>
                            <span class="text-sm">supervisores activos</span>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">#</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Supervisor</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Punto</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Asistencia enviada</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    @foreach($supervisores as $user)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $loop->iteration }}</td>
                                            <td class="whitespace-nowrap px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                                                        {{ substr($user->name ?? '', 0, 2) }}
                                                    </div>
                                                    <div class="ml-3 text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                    {{ $user->punto }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4">
                                                @if($user->envio_asistencia === 'Sí')
                                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-200">Enviada</span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-200">Pendiente</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                                @if($user->envio_asistencia === 'Sí')
                                                    @php
                                                        $asistHoy = \App\Models\Asistencia::where('user_id', $user->id)
                                                            ->whereDate('fecha', \Carbon\Carbon::today())
                                                            ->latest('id')
                                                            ->first();
                                                    @endphp
                                                    @if($asistHoy)
                                                        <a href="{{ route('sup.detalleAsistencia', $asistHoy) }}"
                                                           class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-blue-700">
                                                            Ver detalles
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">Sin acciones</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg bg-gray-600 px-4 py-2 font-medium text-white shadow-sm hover:bg-gray-700">
                            Regresar
                        </a>
                    </div>
                </section>
            </div>
        </div>
    @endif
</x-app-layout>
