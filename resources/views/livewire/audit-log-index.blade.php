<div class="min-h-screen bg-gray-50 p-4 dark:bg-gray-900 sm:p-6">
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Auditoría del sistema</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Historial de acciones sensibles realizadas en el ERP.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white">Regresar</a>
        </div>

        <div class="mb-5 grid gap-3 rounded-xl bg-white p-4 shadow dark:bg-gray-800 md:grid-cols-6">
            <input wire:model.live.debounce.400ms="search" placeholder="Usuario o ID" class="rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white md:col-span-2">
            <select wire:model.live="module" class="rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                <option value="">Todos los módulos</option>
                @foreach($modules as $moduleOption)<option value="{{ $moduleOption }}">{{ $moduleOption }}</option>@endforeach
            </select>
            <input wire:model.live.debounce.400ms="action" placeholder="Acción" class="rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
            <input wire:model.live="dateFrom" type="date" class="rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
            <input wire:model.live="dateTo" type="date" class="rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
            <button wire:click="resetFilters" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white md:col-span-6 md:justify-self-end">Limpiar filtros</button>
        </div>

        <div class="overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-100 text-left text-xs uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    <tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Usuario</th><th class="px-4 py-3">Módulo</th><th class="px-4 py-3">Acción</th><th class="px-4 py-3">Registro</th><th class="px-4 py-3">Cambios</th><th class="px-4 py-3">IP</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($logs as $log)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="whitespace-nowrap px-4 py-3">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3"><div class="font-semibold">{{ $log->actor?->name ?? 'Sistema' }}</div><div class="text-xs text-gray-500">{{ $log->actor?->email }}</div></td>
                            <td class="px-4 py-3">{{ $log->module }}</td>
                            <td class="px-4 py-3 font-medium">{{ $log->action }}</td>
                            <td class="px-4 py-3"><div>{{ class_basename($log->subject_type ?? '') ?: 'N/D' }}</div><div class="text-xs text-gray-500">ID: {{ $log->subject_id ?? 'N/D' }}</div></td>
                            <td class="min-w-72 px-4 py-3"><details><summary class="cursor-pointer text-blue-600">Ver detalle</summary><pre class="mt-2 max-w-xl overflow-auto rounded bg-gray-100 p-2 text-xs dark:bg-gray-950">{{ json_encode(['antes' => $log->old_values, 'después' => $log->new_values, 'datos' => $log->metadata], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details></td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $log->ip_address ?? 'N/D' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No hay acciones que coincidan con los filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</div>
