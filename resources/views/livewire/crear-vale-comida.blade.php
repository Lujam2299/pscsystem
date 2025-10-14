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


        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Fecha <span class="text-red-500">*</span>
            </label>
            <input type="date"
                   wire:model="fecha"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
            @error('fecha') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

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
