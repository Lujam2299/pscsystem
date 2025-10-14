<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
    @php
        $currentPage = $vales->currentPage();
        $lastPage = $vales->lastPage();
    @endphp

    <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Vales de Comida
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Gestione las solicitudes de vales de comida
                </p>
            </div>

            <div class="flex items-center space-x-2">
                <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 px-3 py-1 rounded-full">
                    <span class="text-sm font-medium">{{ $vales->total() }}</span>
                    <span class="text-xs">vales</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre del usuario</label>
            <input type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por nombre..."
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha</label>
            <input type="date"
                wire:model.live="fecha"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
        </div>
    </div>

    <div wire:loading class="mb-4 flex items-center text-sm text-amber-600 dark:text-amber-400">
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Cargando vales...
    </div>

    @if($vales->isEmpty())
        <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay vales</h3>
            <p class="text-gray-500 dark:text-gray-400">No se encontraron solicitudes de vales de comida.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Elementos</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($vales as $vale)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ ($vales->currentPage() - 1) * $vales->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($vale->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    ${{ number_format($vale->monto, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $vale->num_elementos }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $vale->user?->name ?? 'N/D' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusConfig = match($vale->estatus) {
                                            'Pendiente' => ['bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200', 'clock'],
                                            'Aprobado' => ['bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200', 'check'],
                                            'Rechazado' => ['bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200', 'x'],
                                            default => ['bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200', 'help'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusConfig[0] }}">
                                        @if($statusConfig[1] == 'clock')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @elseif($statusConfig[1] == 'check')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @elseif($statusConfig[1] == 'x')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        @endif
                                        {{ $vale->estatus }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="text-gray-400">—</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($vales->hasPages())
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Mostrando
                    <span class="font-medium">{{ $vales->firstItem() }}</span>
                    a
                    <span class="font-medium">{{ $vales->lastItem() }}</span>
                    de
                    <span class="font-medium">{{ $vales->total() }}</span>
                    vales
                </div>

                <div class="flex items-center space-x-1">
                    @if ($vales->onFirstPage())
                        <span class="px-3 py-1 text-gray-400 dark:text-gray-500 rounded">&laquo;</span>
                    @else
                        <button wire:click="previousPage" class="px-3 py-1 text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">&laquo;</button>
                    @endif

                    @if ($currentPage > 2)
                        <button wire:click="gotoPage(1)" class="px-3 py-1 text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">1</button>
                        @if ($currentPage > 3)
                            <span class="px-2 py-1 text-gray-400 dark:text-gray-500">...</span>
                        @endif
                    @endif

                    @for ($i = max(1, $currentPage - 1); $i <= min($lastPage, $currentPage + 1); $i++)
                        @if ($i == $currentPage)
                            <span class="px-3 py-1 bg-amber-500 text-white rounded">{{ $i }}</span>
                        @else
                            <button wire:click="gotoPage({{ $i }})" class="px-3 py-1 text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ $i }}</button>
                        @endif
                    @endfor

                    @if ($currentPage < $lastPage - 1)
                        @if ($currentPage < $lastPage - 2)
                            <span class="px-2 py-1 text-gray-400 dark:text-gray-500">...</span>
                        @endif
                        <button wire:click="gotoPage({{ $lastPage }})" class="px-3 py-1 text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ $lastPage }}</button>
                    @endif

                    @if ($vales->hasMorePages())
                        <button wire:click="nextPage" class="px-3 py-1 text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">&raquo;</button>
                    @else
                        <span class="px-3 py-1 text-gray-400 dark:text-gray-500 rounded">&raquo;</span>
                    @endif
                </div>
            </div>
        @endif
    @endif

    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-center gap-3">
        <a href="{{ route('vales.comida.crear') }}"
           class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Crear Solicitud
        </a>
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Regresar
        </a>
    </div>
</div>
