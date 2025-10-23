<div class="mb-6">
    <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button
            wire:click="switchTo('quincenal')"
            class="px-4 py-2 font-medium text-sm {{ $activeTab === 'quincenal'
                ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-400 dark:border-blue-400'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Nóminas Quincenales
        </button>
        <button
            wire:click="switchTo('semanal')"
            class="px-4 py-2 font-medium text-sm {{ $activeTab === 'semanal'
                ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-400 dark:border-blue-400'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Pagos Semanales
        </button>
    </div>

    <div class="mt-6">
        @if($activeTab === 'quincenal')
            @livewire('nominas-registros-table')
        @elseif($activeTab === 'semanal')
            @livewire('pagos-semanales-table')
        @endif
    </div>
</div>
