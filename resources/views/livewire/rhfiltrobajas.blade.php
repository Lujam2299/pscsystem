<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
    @php
        $currentPage = $solicitudes->currentPage();
        $lastPage = $solicitudes->lastPage();
    @endphp

    <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Solicitudes de Baja
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Gestione y revise las solicitudes de baja de empleados
                </p>
            </div>

            <div class="flex items-center space-x-2">
                <div class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 px-3 py-1 rounded-full">
                    <span class="text-sm font-medium">{{ $solicitudes->total() }}</span>
                    <span class="text-xs">solicitudes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes Flash -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Buscar por nombre
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nombre..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                    >
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Filtrar por fecha
                </label>
                <input
                    type="date"
                    wire:model.live="fecha"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                >
            </div>
        </div>

        <div wire:loading class="mt-2 flex items-center text-sm text-red-600 dark:text-red-400">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Buscando solicitudes...
        </div>
    </div>

    @if($solicitudes->isEmpty())
        <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay solicitudes</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">No se encontraron solicitudes de baja.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Empresa</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Por</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Detalles</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha de Baja</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($solicitudes as $solicitud)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ ($solicitudes->currentPage() - 1) * $solicitudes->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-red-500 to-pink-600 flex items-center justify-center">
                                                <span class="text-white font-medium text-xs">
                                                    {{ substr($solicitud->user->name ?? '', 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $solicitud->user->name ?? 'N/D' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    @if($solicitud->user->empresa)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">
                                            {{ $solicitud->user->empresa }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">N/D</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $solicitud->por ?? 'N/D' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    <div class="max-w-xs truncate" title="{{ $solicitud->motivo ?? 'Sin detalles' }}">
                                        {{ $solicitud->motivo ?? 'Sin detalles' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($solicitud->fecha_baja)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusConfig = match($solicitud->estatus) {
                                            'En Proceso' => ['bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200', 'clock'],
                                            'Aceptada' => ['bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200', 'check'],
                                            'Rechazada' => ['bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200', 'x'],
                                            default => ['bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200', 'help'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusConfig[0] }}">
                                        @if($statusConfig[1] == 'clock')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @elseif($statusConfig[1] == 'check')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        @elseif($statusConfig[1] == 'x')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        @endif
                                        {{ $solicitud->estatus }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <!-- BOTÓN EDITAR AGREGADO -->
                                    <button wire:click="abrirModalEditar({{ $solicitud->id }})"
                                            class="inline-flex items-center justify-center px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition duration-200 shadow-sm text-xs mr-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Editar
                                    </button>

                                    <a href="{{ route('rh.detalleSolicitudBaja', $solicitud->id) }}"
                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow-sm text-xs mr-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Ver
                                    </a>

                                    @if(is_null($solicitud->arch_renuncia))
                                        <button wire:click="abrirModalSubirRenuncia({{ $solicitud->id }})"
                                                class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            Subir Renuncia
                                        </button>
                                    @else
                                        <span class="inline-flex items-center justify-center px-3 py-1.5 bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200 font-medium rounded-lg text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Renuncia Subida
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($solicitudes->hasPages())
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Mostrando
                    <span class="font-medium">{{ $solicitudes->firstItem() }}</span>
                    a
                    <span class="font-medium">{{ $solicitudes->lastItem() }}</span>
                    de
                    <span class="font-medium">{{ $solicitudes->total() }}</span>
                    solicitudes
                </div>

                <div class="flex items-center space-x-1">
                    @if ($solicitudes->onFirstPage())
                        <span class="px-3 py-1 text-gray-400 dark:text-gray-500 rounded">&laquo;</span>
                    @else
                        <button wire:click="previousPage" class="px-3 py-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">&laquo;</button>
                    @endif

                    @if ($currentPage > 2)
                        <button wire:click="gotoPage(1)" class="px-3 py-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">1</button>
                        @if ($currentPage > 3)
                            <span class="px-2 py-1 text-gray-400 dark:text-gray-500">...</span>
                        @endif
                    @endif

                    @for ($i = max(1, $currentPage - 1); $i <= min($lastPage, $currentPage + 1); $i++)
                        @if ($i == $currentPage)
                            <span class="px-3 py-1 bg-red-500 text-white rounded">{{ $i }}</span>
                        @else
                            <button wire:click="gotoPage({{ $i }})" class="px-3 py-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ $i }}</button>
                        @endif
                    @endfor

                    @if ($currentPage < $lastPage - 1)
                        @if ($currentPage < $lastPage - 2)
                            <span class="px-2 py-1 text-gray-400 dark:text-gray-500">...</span>
                        @endif
                        <button wire:click="gotoPage({{ $lastPage }})" class="px-3 py-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ $lastPage }}</button>
                    @endif

                    @if ($solicitudes->hasMorePages())
                        <button wire:click="nextPage" class="px-3 py-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700">&raquo;</button>
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

    <!-- ========================================== -->
    <!-- MODAL 1: Subir Renuncia (EXISTENTE)        -->
    <!-- ========================================== -->
    @if($solicitudId)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: block;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Subir Archivo de Renuncia</h3>
                    <button wire:click="$set('solicitudId', null)" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selecciona un archivo (PDF, JPG, PNG)</label>
                        <input type="file"
                               wire:model="archivoRenuncia"
                               wire:loading.attr="disabled"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                               accept=".pdf,.jpg,.jpeg,.png">

                        @error('archivoRenuncia') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                        @if($archivoRenuncia)
                            <div class="mt-2 p-2 bg-green-100 text-green-800 rounded text-sm">
                                Archivo seleccionado: {{ $archivoRenuncia->getClientOriginalName() }}
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" wire:click="$set('solicitudId', null)" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Cancelar</button>
                        <button type="button" wire:click="subirRenuncia" wire:loading.attr="disabled" @if(!$archivoRenuncia) disabled @endif class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 @if(!$archivoRenuncia) opacity-50 cursor-not-allowed @endif">
                            <span wire:loading.remove wire:target="subirRenuncia">Subir Archivo</span>
                            <span wire:loading wire:target="subirRenuncia">Subiendo...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

<!-- ========================================== -->
<!-- MODAL 2: Editar Solicitud (NUEVO)          -->
<!-- ========================================== -->
@if($editSolicitudId)
<div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50">
    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">

        <!-- Header del Modal -->
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 dark:from-yellow-600 dark:to-yellow-700 px-6 py-4 border-b border-yellow-200 dark:border-yellow-900">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-yellow-500/20 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white">Editar Solicitud de Baja</h3>
                </div>
                <button wire:click="$set('editSolicitudId', null)" class="text-yellow-100 hover:text-white focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Contenido Principal del Modal -->
        <div class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Columna Izquierda: Datos de Texto -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo de Baja</label>
                        <textarea wire:model="editMotivo" rows="4" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white transition-all duration-200"></textarea>
                        @error('editMotivo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Procesado Por</label>
                        <select wire:model="editPor" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white transition-all duration-200">
                            <option value="">Seleccionar...</option>
                            <option value="Renuncia">Renuncia</option>
                            <option value="Ausentismo">Ausentismo</option>
                            <option value="Separación Voluntaria">Separación Voluntaria</option>
                            <option value="Otro">Otro</option>
                        </select>
                        @error('editPor') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de Baja</label>
                            <input type="date" wire:model="editFechaBaja" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white transition-all duration-200">
                            @error('editFechaBaja') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <!-- Campo Estatus (oculto, se guarda como 'Aceptada') -->
                        <input type="hidden" wire:model="editEstatus" value="Aceptada">
                        <!-- Nuevos Campos -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Última Asistencia</label>
                            <input type="date" wire:model="editUltimaAsistencia" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white transition-all duration-200">
                            @error('editUltimaAsistencia') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descuento ($)</label>
                            <input type="number" step="0.01" wire:model="editDescuento" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white transition-all duration-200">
                            @error('editDescuento') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Gestión de Archivos -->
                <div class="space-y-6">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-5 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">Documentación Adjunta</h4>

                        <div class="space-y-5">
                            @php
                                $archivosConfig = [
                                    ['key' => 'newArchBaja', 'label' => 'Acta de Baja / Documento Formal', 'db_field' => 'archivo_baja'],
                                    ['key' => 'newArchEquipo', 'label' => 'Entrega de Equipo', 'db_field' => 'arch_equipo_entregado'],
                                    ['key' => 'newArchCheque', 'label' => 'Cheque / Finiquito', 'db_field' => 'arch_cheque'],
                                    ['key' => 'newArchRenuncia', 'label' => 'Carta de Renuncia', 'db_field' => 'arch_renuncia'],
                                ];
                            @endphp

                            @foreach($archivosConfig as $arch)
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-white dark:bg-gray-800 shadow-sm">
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 uppercase mb-2 tracking-wide">
                                        {{ $arch['label'] }}
                                    </label>

                                    @php
                                        $currentPath = $solicitudActual ? $solicitudActual->{$arch['db_field']} : null;
                                    @endphp

                                    <!-- Mostrar archivo actual si existe -->
                                    @if($currentPath)
                                        <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded mb-3">
                                            <div class="flex items-center overflow-hidden">
                                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <span class="text-sm text-green-800 dark:text-green-300 truncate" title="{{ basename($currentPath) }}">
                                                    {{ basename($currentPath) }}
                                                </span>
                                            </div>
                                            @if(\Illuminate\Support\Facades\Storage::disk('public')->exists($currentPath))
                                                @php
                                                    $fileUrl = asset('storage/' . $currentPath);
                                                @endphp
                                                <a href="{{ $fileUrl }}" target="_blank" class="ml-2 px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 whitespace-nowrap flex items-center transition-colors duration-200">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                    Abrir
                                                </a>
                                            @else
                                                <span class="text-[10px] text-red-500 font-bold ml-2">Falta Archivo</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-400 italic mb-3 bg-gray-100 dark:bg-gray-800 p-2 rounded text-center">
                                            No hay archivo guardado
                                        </div>
                                    @endif

                                    <!-- Dropzone con Alpine.js - Versión Forzada -->
                                    <div x-data="dropzone('{{ $arch['key'] }}')"
                                         x-ref="dropzone"
                                         class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 text-center cursor-pointer hover:border-yellow-400 dark:hover:border-yellow-500 transition-colors duration-200 relative"
                                         @dragover.prevent
                                         @dragenter.prevent="isOver = true"
                                         @dragleave.prevent="isOver = false"
                                         @drop.prevent="handleDrop($event)"
                                         style="pointer-events: auto;">
                                        <input type="file"
                                               wire:model="{{ $arch['key'] }}"
                                               id="fileInput_{{ $arch['key'] }}"
                                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                               accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="relative z-10 flex flex-col items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                            <p class="text-xs text-gray-500">
                                                <span class="font-medium text-yellow-600 dark:text-yellow-400" x-text="isOver ? 'Suelta para subir' : 'Click para seleccionar'"></span> o arrastra un archivo aquí
                                            </p>
                                            <p class="text-[9px] text-gray-400 mt-0.5">PDF, JPG, PNG</p>
                                        </div>
                                    </div>

                                    @error($arch['key'])
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror

                                    @if(${$arch['key']})
                                        <div class="mt-3 p-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded flex items-center justify-between">
                                            <div class="flex items-center truncate">
                                                <svg class="w-4 h-4 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="text-xs text-blue-800 dark:text-blue-300 truncate" title="{{ ${$arch['key']}->getClientOriginalName() }}">
                                                    {{ ${$arch['key']}->getClientOriginalName() }}
                                                </span>
                                            </div>
                                            <button type="button" @click="removeFile()" class="text-red-500 hover:text-red-700 text-xs">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer del Modal -->
        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
            <button type="button" wire:click="guardarEdicion" wire:loading.attr="disabled"
                    class="px-5 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition duration-200 shadow-sm flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Guardar
            </button>
            <button type="button" wire:click="$set('editSolicitudId', null)"
                    class="px-5 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition duration-200 shadow-sm flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancelar
            </button>
        </div>
    </div>
</div>
@endif
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dropzone', (property) => ({
        isOver: false,

        init() {
            // Escuchar cambios en el input oculto (cuando Livewire lo actualiza)
            const fileInput = document.getElementById(`fileInput_${property}`);
            if (fileInput) {
                fileInput.addEventListener('change', () => {
                    // No necesitamos hacer nada aquí, Livewire ya se encarga de la sincronización.
                    // Este listener es solo para depuración si es necesario.
                });
            }
        },

        handleDrop(event) {
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                const fileInput = document.getElementById(`fileInput_${property}`);
                if (fileInput) {
                    // Asignar el archivo al input
                    fileInput.files = files;
                    // Disparar evento 'change' para que Livewire lo detecte
                    fileInput.dispatchEvent(new Event('change'));
                }
            }
        },

        removeFile() {
            const fileInput = document.getElementById(`fileInput_${property}`);
            if (fileInput) {
                fileInput.value = null;
                fileInput.dispatchEvent(new Event('change'));
            }
        }
    }));
});
</script>
</div>
