<x-app-layout>
    <x-navbar />

    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="mb-6">
                    <a href="{{ route('operaciones.asistenciaDiaria') }}"
                       class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Regresar a selección de punto
                    </a>
                </div>

                <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">Confirma quiénes descansan</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Los siguientes usuarios no fueron marcados como asistentes. Indica quiénes estaban en descanso programado.
                    Los que no marques se registrarán como <strong>faltas</strong>.
                </p>

                @if($faltantes->isEmpty())
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 text-center">
                        <p class="text-green-800 dark:text-green-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            ¡Todos los usuarios asistieron! No hay descansos ni faltas.
                        </p>
                    </div>

                    <div class="mt-6 text-center">
                        <form action="{{ route('operaciones.finalizarAsistencia') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium shadow-sm transition">
                                Finalizar Registro
                            </button>
                        </form>
                    </div>
                @else
                    <form action="{{ route('operaciones.finalizarAsistencia') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($faltantes as $elemento)
                                @if ($elemento->rol == 'GUARDIA')
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-center space-x-4 mb-4">
                                                @if($elemento->solicitudAlta?->documentacion?->arch_foto)
                                                    <img src="{{ asset('storage/' . str_replace('storage/', '', $elemento->solicitudAlta->documentacion->arch_foto)) }}"
                                                        alt="Foto de {{ $elemento->name }}"
                                                        class="w-16 h-16 rounded-full object-cover border border-gray-300 dark:border-gray-600">
                                                @else
                                                    <div class="flex-shrink-0 w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                                        <span class="text-white font-medium text-lg">
                                                            {{ substr($elemento->name ?? '', 0, 2) }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                                                        {{ $elemento->name }}
                                                    </h2>
                                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $elemento->empresa }}
                                                        - {{ $elemento->punto }}</p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $elemento->rol }}</p>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between mt-4">
                                                <label for="descanso_{{ $elemento->id }}"
                                                    class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Descansó
                                                </label>
                                                <input type="checkbox" name="descansan[]" value="{{ $elemento->id }}"
                                                    id="descanso_{{ $elemento->id }}"
                                                    class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium shadow-sm transition duration-200">
                                Finalizar Registro de Asistencia
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
