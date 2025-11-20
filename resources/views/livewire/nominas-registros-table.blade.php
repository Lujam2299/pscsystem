<div>
    <div class="flex flex-wrap items-end gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <div class="relative">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por periodo..."
                    class="w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
        </div>

        <div class="flex-1 min-w-[120px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
            <select
                wire:model.live="anio"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Todos</option>
                @foreach($anios as $anioDisponible)
                    <option value="{{ $anioDisponible }}">{{ $anioDisponible }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[120px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
            <select
                wire:model.live="mes"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Todos</option>
                @foreach(['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'] as $mesNombre)
                    <option value="{{ $mesNombre }}">{{ ucfirst($mesNombre) }}</option>
                @endforeach
            </select>
        </div>

        <div class="pb-1">
            <button
                wire:click="$refresh"
                class="h-10 w-10 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center justify-center"
                title="Filtrar"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th wire:click="ordenarPor('periodo')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Periodo
                        @if($orden === 'periodo')
                            <span>{{ $direccion === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Archivos
                    </th>
                    <th wire:click="ordenarPor('subtotal')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Subtotal
                        @if($orden === 'subtotal')
                            <span>{{ $direccion === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th wire:click="ordenarPor('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Fecha de Carga
                        @if($orden === 'created_at')
                            <span>{{ $direccion === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($registros as $registro)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $registro->periodo }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">
                                @if($registro->arch_nomina)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Nómina
                                    </div>
                                @endif
                                @if($registro->arch_nomina_spyt)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Nómina SPyT
                                    </div>
                                @endif
                                @if($registro->arch_nomina_montana)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Nómina Montana
                                    </div>
                                @endif
                                @if($registro->arch_destajo)
                                    <div class="flex items-center mt-1">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Destajo
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                @if($registro->subtotal && $registro->subtotal > 0)
                                    ${{ number_format($registro->subtotal, 2) }}
                                @elseif($registro->total_destajos && $registro->total_destajos > 0)
                                    ${{ number_format($registro->total_destajos, 2) }}
                                @else
                                    $0.00
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $registro->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                @if($registro->arch_nomina)
                                    <a
                                        href="{{ asset('storage/' . $registro->arch_nomina) }}"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Ver archivo de nómina"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>PSC
                                    </a>
                                @endif
                                @if($registro->arch_nomina_spyt)
                                    <a
                                        href="{{ asset('storage/' . $registro->arch_nomina_spyt) }}"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Ver archivo de nómina SPyT"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>SPYT
                                    </a>
                                @endif
                                @if($registro->arch_nomina_montana)
                                    <a
                                        href="{{ asset('storage/' . $registro->arch_nomina_montana) }}"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Ver archivo de nómina Montana"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>Montana
                                    </a>
                                @endif

                                @if($registro->arch_destajo)
                                    <a
                                        href="{{ asset('storage/' . $registro->arch_destajo) }}"
                                        target="_blank"
                                        class="text-green-600 hover:text-green-900"
                                        title="Ver archivo de destajo"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>Destajos
                                    </a>
                                @endif

                                <!-- Botón de edición -->
                                <button
                                    wire:click="abrirModalEdicion({{ $registro->id }})"
                                    class="text-yellow-600 hover:text-yellow-900"
                                    title="Editar"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>Editar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            No se encontraron registros.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $registros->links() }}
    </div>

    @if(session()->has('error'))
        <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Modal de edición -->
@if($editandoId)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Editar Registro</h3>
                    <button
                        wire:click="cerrarModal"
                        class="text-gray-400 hover:text-gray-500"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Dentro del modal, reemplaza los inputs de subtotal por esto -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Periodo</label>
        <input
            wire:model="periodo"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
        @error('periodo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal (Nóminas)</label>
        <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">
            ${{ number_format($subtotalCalculado, 2) }}
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Total Destajos</label>
        <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">
            ${{ number_format($totalDestajosCalculado, 2) }}
        </div>
    </div>
</div>
                <!-- Archivos -->
<div class="mt-6 pt-4 border-t border-gray-200">
    <h4 class="text-sm font-medium text-gray-900 mb-3">Reemplazar archivos (opcional)</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @php
            $registroActual = $editandoId ? \App\Models\Archivonomina::find($editandoId) : null;
        @endphp

        <div>
            <label class="block text-sm text-gray-700 mb-1">Nómina PSC</label>
            @if($registroActual?->arch_nomina)
                <div class="text-xs text-gray-500 mb-1">
                    Archivo actual: <span class="font-medium">{{ basename($registroActual->arch_nomina) }}</span>
                </div>
            @endif
            <input
                wire:model="arch_nomina"
                type="file"
                accept=".xlsx,.xls,.csv"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            >
            @error('arch_nomina') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm text-gray-700 mb-1">Nómina SPyT</label>
            @if($registroActual?->arch_nomina_spyt)
                <div class="text-xs text-gray-500 mb-1">
                    Archivo actual: <span class="font-medium">{{ basename($registroActual->arch_nomina_spyt) }}</span>
                </div>
            @endif
            <input
                wire:model="arch_nomina_spyt"
                type="file"
                accept=".xlsx,.xls,.csv"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            >
            @error('arch_nomina_spyt') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm text-gray-700 mb-1">Nómina Montana</label>
            @if($registroActual?->arch_nomina_montana)
                <div class="text-xs text-gray-500 mb-1">
                    Archivo actual: <span class="font-medium">{{ basename($registroActual->arch_nomina_montana) }}</span>
                </div>
            @endif
            <input
                wire:model="arch_nomina_montana"
                type="file"
                accept=".xlsx,.xls,.csv"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            >
            @error('arch_nomina_montana') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm text-gray-700 mb-1">Destajos</label>
            @if($registroActual?->arch_destajo)
                <div class="text-xs text-gray-500 mb-1">
                    Archivo actual: <span class="font-medium">{{ basename($registroActual->arch_destajo) }}</span>
                </div>
            @endif
            <input
                wire:model="arch_destajo"
                type="file"
                accept=".xlsx,.xls,.csv"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
            >
            @error('arch_destajo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button
                        wire:click="cerrarModal"
                        type="button"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 font-medium"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="guardarEdicion"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium disabled:opacity-50 flex items-center"
                    >
                        <span wire:loading.remove>Guardar Cambios</span>
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
