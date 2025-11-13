<div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Justificar Faltas
    </h1>

    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="punto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Punto</label>
                <select wire:model.live="punto"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Selecciona un punto</option>
                    @foreach($subpuntosMap as $puntoGeneral => $subpuntos)
                        <optgroup label="{{ $puntoGeneral }}">
                            <option value="{{ $puntoGeneral }}">{{ $puntoGeneral }} (Todos)</option>
                            @foreach($subpuntos as $subpunto)
                                <option value="{{ $subpunto['nombre'] }}">
                                    {{ $subpunto['nombre'] }}
                                    @if($subpunto['codigo'])
                                        ({{ str_pad($subpunto['codigo'], 3, '0', STR_PAD_LEFT) }})
                                    @endif
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="fecha" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha</label>
                <input type="date" wire:model.live="fecha"
                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
            </div>
        </div>
    </div>

    @if(count($usuariosFaltas) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Justificar</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archivo</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($usuariosFaltas as $user)
                        @if(!in_array($user->id, $faltasJustificadas))
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" wire:model="usuariosAJustificar" value="{{ $user->id }}"
                                               class="h-4 w-4 text-blue-600 rounded">
                                    </label>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <input type="text" wire:model="motivos.{{ $user->id }}" placeholder="Motivo..."
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <input type="file" wire:model="archivos.{{ $user->id }}"
                                           accept="image/*,.pdf"
                                           class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 px-3 py-2">
                                    @error('archivos.' . $user->id) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end">
            <button wire:click="justificar"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Justificar Seleccionadas
            </button>
        </div>

        @if(session()->has('message'))
            <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('message') }}
            </div>
        @endif
    @elseif($punto && $fecha)
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            No hay faltas registradas para este punto y fecha.
        </div>
    @endif
</div>
