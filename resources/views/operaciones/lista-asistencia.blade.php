<x-app-layout>
    <x-navbar />

    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">

                @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 rounded-r text-red-900 px-4 py-3 shadow-md mb-6"
                        role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">Error: {{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($yaRegistrado)
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-r">
                        <p><strong>Advertencia:</strong> Ya se registró asistencia para el punto
                            <strong>{{ $punto }}</strong> hoy. Este registro reemplazará el anterior.</p>
                    </div>
                @endif

                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-green-600"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Registro de Asistencia – {{ $punto }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Marque la asistencia de los usuarios y adjunte evidencias cuando sea necesario.
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div
                                class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-3 py-1 rounded-full">
                                <span class="text-sm font-medium">{{ $elementos->count() }}</span>
                                <span class="text-xs">usuarios</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('operaciones.guardarAsistencias') }}" method="POST"
                    enctype="multipart/form-data" id="form-asistencias">
                    @csrf

                    <input type="hidden" name="punto_seleccionado" value="{{ $punto }}">

                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                        <label for="fecha_registro"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 px-3 py-2">
                            Fecha de Registro
                        </label>
                        <input type="date" name="fecha_registro" id="fecha_registro"
                            value="{{ now()->toDateString() }}"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            required>
                    </div>

                    @if ($elementos->isEmpty())
                        <div
                            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 text-center mb-6">
                            <p class="text-blue-800 dark:text-blue-200">No hay personal activo asignado al punto
                                <strong>{{ $punto }}</strong>.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach ($elementos as $elemento)
                                <div
                                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-md">
                                    <div class="p-5">
                                        <div class="flex items-center space-x-4 mb-4">
                                            @if ($elemento->solicitudAlta?->documentacion?->arch_foto)
                                                <img src="{{ asset('storage/' . str_replace('storage/', '', $elemento->solicitudAlta->documentacion->arch_foto)) }}"
                                                    alt="Foto de {{ $elemento->name }}"
                                                    class="w-16 h-16 rounded-full object-cover border-2 border-white dark:border-gray-600 shadow-sm">
                                            @else
                                                <div
                                                    class="flex-shrink-0 w-16 h-16 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center">
                                                    <span class="text-white font-medium text-lg">
                                                        {{ substr($elemento->name ?? '', 0, 2) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <h2
                                                    class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                                    {{ $elemento->name }}
                                                </h2>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                    {{ $elemento->empresa }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ $elemento->punto }}
                                                </p>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 mt-1">
                                                    {{ $elemento->rol }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                            <label for="asistencia_{{ $elemento->id }}"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Asistió
                                            </label>
                                            <input type="checkbox" name="asistencias[]" value="{{ $elemento->id }}"
                                                id="asistencia_{{ $elemento->id }}"
                                                class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded cursor-pointer"
                                                onchange="toggleAsistenciaPanel(this, {{ $elemento->id }})">
                                        </div>

                                        {{-- Panel condicional: evidencia + turnos + retardo --}}
                                        <div id="panel_{{ $elemento->id }}" class="mt-3 hidden space-y-3">
                                            <!-- Subida de evidencia -->
                                            <label
                                                class="flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition cursor-pointer text-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                Adjuntar evidencia
                                                <input type="file" name="foto_evidencia[{{ $elemento->id }}]"
                                                    class="hidden" accept="image/*"
                                                    onchange="previewEvidence(this, '{{ $elemento->id }}')">
                                            </label>

                                            <div id="evidence_preview_{{ $elemento->id }}" class="hidden mt-2">
                                                <div class="relative">
                                                    <img id="evidence_img_{{ $elemento->id }}"
                                                        class="h-20 w-full object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">
                                                    Vista previa</p>
                                            </div>

                                            <!-- Selección de turnos (checkboxes múltiples) -->
                                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Turno(s) de asistencia
                                                </label>
                                                <div class="flex flex-wrap gap-2">
                                                    <label
                                                        class="inline-flex items-center bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs">
                                                        <input type="checkbox" name="turnos[{{ $elemento->id }}][]"
                                                            value="dia" class="h-3 w-3 text-blue-600 rounded"
                                                            onchange="toggleTiempoExtra({{ $elemento->id }})">
                                                        <span class="ml-1 text-gray-700 dark:text-gray-300">Día</span>
                                                    </label>
                                                    <label
                                                        class="inline-flex items-center bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs">
                                                        <input type="checkbox" name="turnos[{{ $elemento->id }}][]"
                                                            value="tarde" class="h-3 w-3 text-blue-600 rounded"
                                                            onchange="toggleTiempoExtra({{ $elemento->id }})">
                                                        <span
                                                            class="ml-1 text-gray-700 dark:text-gray-300">Tarde</span>
                                                    </label>
                                                    <label
                                                        class="inline-flex items-center bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs">
                                                        <input type="checkbox" name="turnos[{{ $elemento->id }}][]"
                                                            value="noche" class="h-3 w-3 text-blue-600 rounded"
                                                            onchange="toggleTiempoExtra({{ $elemento->id }})">
                                                        <span
                                                            class="ml-1 text-gray-700 dark:text-gray-300">Noche</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Campo de minutos de retardo -->
                                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Minutos de Retardo (opcional)
                                                </label>
                                                <input type="number"
                                                       name="minutos_retardo[{{ $elemento->id }}]"
                                                       id="minutos_retardo_{{ $elemento->id }}"
                                                       min="0"
                                                       max="599"
                                                       placeholder="0"
                                                       class="block w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                            </div>

                                            <!-- Panel de Tiempo Extra -->
                                            <div id="te_panel_{{ $elemento->id }}" class="mt-3 hidden space-y-2 bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Tiempo Extra (opcional)
                                                </label>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <label for="te_horas_{{ $elemento->id }}" class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Horas Extras</label>
                                                        <input type="number"
                                                               name="tiempo_extra_horas[{{ $elemento->id }}]"
                                                               id="te_horas_{{ $elemento->id }}"
                                                               min="0.01"
                                                               step="0.01"
                                                               placeholder="0.00"
                                                               class="block w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                    </div>
                                                    <div>
                                                        <label for="te_observaciones_{{ $elemento->id }}" class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Obs.</label>
                                                        <input type="text"
                                                               name="tiempo_extra_obs[{{ $elemento->id }}]"
                                                               id="te_observaciones_{{ $elemento->id }}"
                                                               placeholder="Observaciones"
                                                               class="block w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-8">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                            <label for="observaciones"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Observaciones
                            </label>
                            <textarea name="observaciones" id="observaciones" rows="4"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Escribe tus observaciones aquí..."></textarea>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Coberturas de Turno
                            </label>
                            @livewire('seleccioncoberturas')
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Guardar Asistencias
                            </button>
                            <a href="{{ route('operaciones.asistenciaDiaria') }}"
                                class="inline-flex items-center px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Cambiar de Punto
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function toggleAsistenciaPanel(checkbox, userId) {
        const panel = document.getElementById('panel_' + userId);
        const tePanel = document.getElementById('te_panel_' + userId);
        if (checkbox.checked) {
            panel.classList.remove('hidden');
        } else {
            panel.classList.add('hidden');
            // Resetear campos
            const preview = document.getElementById('evidence_preview_' + userId);
            if (preview) preview.classList.add('hidden');

            const turnoCheckboxes = panel.querySelectorAll('input[type="checkbox"][name="turnos[' + userId + '][]"]');
            turnoCheckboxes.forEach(cb => cb.checked = false);
            tePanel.classList.add('hidden'); // Ocultar tiempo extra también
        }
    }

    // Función para mostrar/ocultar tiempo extra según turnos
    function toggleTiempoExtra(userId) {
        const turnos = document.querySelectorAll('input[name="turnos[' + userId + '][]"]:checked');
        const tePanel = document.getElementById('te_panel_' + userId);

        if (turnos.length > 0) {
            tePanel.classList.remove('hidden');
        } else {
            tePanel.classList.add('hidden');
        }
    }

    // Asignar eventos a los checkboxes de turno
    document.addEventListener('change', function(e) {
        if (e.target.name.startsWith('turnos[')) {
            const userId = e.target.name.match(/turnos\[(\d+)\]/)[1];
            toggleTiempoExtra(userId);
        }
    });
</script>
