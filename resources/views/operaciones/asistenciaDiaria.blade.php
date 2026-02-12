<x-app-layout>
    <x-navbar />

    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                @if (session('success'))
                    <div class="px-4 py-3 text-green-900 bg-green-100 border-t-4 border-green-500 rounded-b shadow-md" role="alert">
                        <div class="flex">
                            <div>
                                <p class="text-sm">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    @if(session('error'))
                        <div class="px-4 py-3 text-red-900 bg-red-100 border-t-4 border-red-500 rounded-b shadow-md" role="alert">
                            <div class="flex">
                                <div>
                                    <p class="text-sm">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                    @endif
                @endif

                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center justify-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        Registro de Asistencia – Operaciones
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Selecciona un punto o subpunto para registrar la asistencia del personal asignado.
                    </p>
                </div>

                <form method="GET" id="form-seleccion-punto">
                    <div class="mb-6">
                        <label for="punto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Punto
                        </label>
                        <select
                            name="punto"
                            id="punto"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 py-3"
                            required
                        >
                            <option value="">Seleccione un subpunto</option>

                            @foreach ($subpuntosMap as $puntoGeneral => $subpuntos)
                                <optgroup label="{{ $puntoGeneral }}">
                                    @foreach ($subpuntos as $subpunto)
                                        <option value="{{ $subpunto['nombre'] }}"
                                            {{ in_array(trim($subpunto['nombre']), array_map('trim', $puntosConAsistenciaHoy)) ? 'data-asistido="true"' : '' }}>
                                            @if ($subpunto['codigo'])
                                                ({{ str_pad($subpunto['codigo'], 3, '0', STR_PAD_LEFT) }})
                                            @endif
                                            {{ $subpunto['nombre'] }}
                                            @if (in_array(trim($subpunto['nombre']), array_map('trim', $puntosConAsistenciaHoy)))
                                                <span class="ml-1 text-green-600 font-bold">✔️</span>
                                            @endif
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-center">
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm"
                                id="btn-continuar"
                                disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                            Continuar con el Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('punto').addEventListener('change', function() {
            const btn = document.getElementById('btn-continuar');
            const form = document.getElementById('form-seleccion-punto');
            if (this.value) {
                btn.disabled = false;
                const baseUrl = "{{ url('/operaciones/asistencia/punto') }}";
                form.action = baseUrl + '/' + encodeURIComponent(this.value);
            } else {
                btn.disabled = true;
                form.action = '';
            }
        });

        // Opcional: Mostrar un mensaje si el punto ya tiene asistencia registrada hoy
        document.getElementById('punto').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option && option.dataset.asistido === 'true') {
                console.log('Este punto ya tiene asistencia registrada hoy');
            }
        });
    </script>
</x-app-layout>
