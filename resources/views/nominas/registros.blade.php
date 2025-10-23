<x-app-layout>
    <x-navbar/>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Registros de Nómina</h2>
                    <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                        ← Regresar
                    </a>
                </div>

                @livewire('registros-nominas-tabs')
            </div>
        </div>
    </div>
</x-app-layout>
