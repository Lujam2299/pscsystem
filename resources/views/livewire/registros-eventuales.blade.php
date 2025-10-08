<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
    @php
        $currentPage = $registros->currentPage();
        $lastPage = $registros->lastPage();
    @endphp

    <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Registros de Eventuales
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Consulte y filtre los registros de turnos de eventuales
                </p>
            </div>

            <div class="flex items-center space-x-2">
                <div class="bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full">
                    <span class="text-sm font-medium">{{ $registros->total() }}</span>
                    <span class="text-xs">registros</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre del usuario</label>
            <input type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por nombre..."
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Pago</label>
            <select wire:model.live="tipo_pago"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                <option value="todos">Todos</option>
                <option value="nomina">Nómina</option>
                <option value="efectivo">Efectivo</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subpunto</label>
            <select wire:model.live="subpunto_id"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                <option value="">Todos</option>
                @foreach($subpuntos as $subpunto)
                    <option value="{{ $subpunto->id }}">{{ $subpunto->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha</label>
            <input type="date"
                wire:model.live="fecha"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
        </div>
    </div>

    <div wire:loading class="mb-4 flex items-center text-sm text-purple-600 dark:text-purple-400">
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Cargando registros...
    </div>

    @if($registros->isEmpty())
        <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay registros</h3>
            <p class="text-gray-500 dark:text-gray-400">No se encontraron registros con los filtros aplicados.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Punto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turnos</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Pago</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($registros as $registro)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ ($registros->currentPage() - 1) * $registros->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
    <div class="flex items-center">
        <div class="flex-shrink-0 h-8 w-8">
            @php
                $foto = null;
                if ($registro->user?->documentacionAltas?->arch_foto) {
                    $foto = asset($registro->user->documentacionAltas->arch_foto);
                }
            @endphp

            @if($foto)
                <img src="{{ $foto }}"
                     alt="Foto de {{ $registro->user->name ?? 'usuario' }}"
                     class="h-8 w-8 rounded-full object-cover border border-gray-200 dark:border-gray-700">
            @else
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                    <span class="text-white font-medium text-xs">
                        {{ substr(trim($registro->user->name ?? 'ND'), 0, 2) }}
                    </span>
                </div>
            @endif
        </div>
        <div class="ml-3">
            <div class="text-sm font-medium text-gray-900 dark:text-white">
                {{ $registro->user->name ?? 'N/D' }}
            </div>
        </div>
    </div>
</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $registro->subpunto?->nombre ?? 'N/D' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    @php
                                        $turnos = is_array($registro->turnos)
                                            ? $registro->turnos
                                            : (is_string($registro->turnos) ? json_decode($registro->turnos, true) : []);
                                        $turnos = is_array($turnos) ? $turnos : [];
                                    @endphp
                                    @foreach($turnos as $turno)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-200 mr-1">
                                            {{ ucfirst($turno) }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($registro->tipo_pago === 'nomina')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                                            Nómina
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">
                                            Efectivo
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($registros->hasPages())
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Mostrando
                    <span class="font-medium">{{ $registros->firstItem() }}</span>
                    a
                    <span class="font-medium">{{ $registros->lastItem() }}</span>
                    de
                    <span class="font-medium">{{ $registros->total() }}</span>
                    registros
                </div>

                <div class="flex items-center space-x-1">
                    @if ($registros->onFirstPage())
                        <span class="px-3 py-1 text-gray-400 dark:text-gray-500 rounded">&laquo;</span>
                    @else
                        <button wire:click="previousPage" class="px-3 py-1 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">&laquo;</button>
                    @endif

                    @if ($currentPage > 2)
                        <button wire:click="gotoPage(1)" class="px-3 py-1 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">1</button>
                        @if ($currentPage > 3)
                            <span class="px-2 py-1 text-gray-400 dark:text-gray-500">...</span>
                        @endif
                    @endif

                    @for ($i = max(1, $currentPage - 1); $i <= min($lastPage, $currentPage + 1); $i++)
                        @if ($i == $currentPage)
                            <span class="px-3 py-1 bg-purple-500 text-white rounded">{{ $i }}</span>
                        @else
                            <button wire:click="gotoPage({{ $i }})" class="px-3 py-1 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ $i }}</button>
                        @endif
                    @endfor

                    @if ($currentPage < $lastPage - 1)
                        @if ($currentPage < $lastPage - 2)
                            <span class="px-2 py-1 text-gray-400 dark:text-gray-500">...</span>
                        @endif
                        <button wire:click="gotoPage({{ $lastPage }})" class="px-3 py-1 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ $lastPage }}</button>
                    @endif

                    @if ($registros->hasMorePages())
                        <button wire:click="nextPage" class="px-3 py-1 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">&raquo;</button>
                    @else
                        <span class="px-3 py-1 text-gray-400 dark:text-gray-500 rounded">&raquo;</span>
                    @endif
                </div>
            </div>
        @endif
    @endif

    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex justify-center">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Regresar
            </a>
        </div>
    </div>
</div>
