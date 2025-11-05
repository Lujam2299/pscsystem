<x-app-layout>
    <x-navbar />

    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h2 class="text-2xl font-semibold mb-4">Confirma quiénes descansan</h2>

                <form action="{{ route('asistencias.finalizar') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($faltantes as $elemento)
                            @if ($elemento->rol != 'GUARDIA')
                            @else
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center space-x-4 mb-4">
                                        <img src="{{ $elemento->solicitudAlta?->documentacion?->arch_foto ? asset($elemento->solicitudAlta->documentacion->arch_foto) . '?v=' . now()->timestamp : asset('images/default-user.jpg') }}"
                                            alt="Foto de {{ $elemento->name }}"
                                            class="w-16 h-16 rounded-full object-cover border border-gray-300 dark:border-gray-600">
                                        <div>
                                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                                                {{ $elemento->name }}</h2>
                                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $elemento->empresa }}
                                                - {{ $elemento->punto }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $elemento->rol }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between mt-4">
                                        <label for="asistencia_{{ $elemento->id }}"
                                            class="text-sm text-gray-700 dark:text-gray-300">
                                            Descansó
                                        </label>
                                        <input type="checkbox" name="descansan[]" value="{{ $elemento->id }}"
                                            id="asistencia_{{ $elemento->id }}"
                                            class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                            onchange="toggleUploadButton(this, 'upload_container_{{ $elemento->id }}')">
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-6 text-center">
                        <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                            Finalizar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
