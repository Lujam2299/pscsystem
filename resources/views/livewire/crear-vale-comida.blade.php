<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
    <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
        <div class="flex items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Crear Vale de Comida
            </h1>
        </div>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Complete el formulario para crear una nueva solicitud de vale de comida
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-100 border-l-4 border-green-500 rounded-r text-green-900 px-4 py-3 shadow-md" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        <!-- Búsqueda de usuario -->
        <div class="relative">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Usuario <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   wire:model.live="search"
                   placeholder="Buscar por nombre..."
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white"
                   autocomplete="off">

            <!-- Resultados de búsqueda -->
            @if(count($usuarios) > 0)
                <div class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto">
                    @foreach($usuarios as $usuario)
                        <div wire:click="selectUser({{ $usuario->id }}, '{{ addslashes($usuario->name) }}')"
                             class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-sm text-gray-900 dark:text-white">
                            {{ $usuario->name }}
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Usuario seleccionado -->
            @if($selectedUserId)
                <div class="mt-2 flex items-center bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-sm text-blue-800 dark:text-blue-200">{{ $selectedUserName }}</span>
                    <button type="button"
                            wire:click="$set('selectedUserId', ''); $set('selectedUserName', ''); $set('search', '');"
                            class="ml-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                        ×
                    </button>
                </div>
            @endif

            @error('selectedUserId')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Fecha -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Fecha <span class="text-red-500">*</span>
            </label>
            <input type="date"
                   wire:model="fecha"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
            @error('fecha') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Monto -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Monto <span class="text-red-500">*</span>
            </label>
            <input type="number"
                   step="0.01"
                   min="0.01"
                   wire:model="monto"
                   placeholder="0.00"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
            @error('monto') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Número de elementos -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Número de Elementos <span class="text-red-500">*</span>
            </label>
            <input type="number"
                   min="1"
                   wire:model="num_elementos"
                   placeholder="1"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
            @error('num_elementos') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Botones -->
        <div class="flex flex-wrap gap-3 pt-4">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Crear Vale
            </button>

            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Cancelar
            </a>
        </div>
    </form>
</div>
