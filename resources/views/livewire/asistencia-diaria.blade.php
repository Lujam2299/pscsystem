<div>
    <style>
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-completed {
            background-color: #10B981;
        }

        .status-pending {
            background-color: #F59E0B;
        }
    </style>

    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">

                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Registro Diario de Asistencias
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Registra las asistencias diarias de los guardias por punto
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            @if (session('success'))
                                <div class="bg-green-100 border-l-4 border-green-500 rounded-r text-green-900 px-4 py-3 shadow-md">
                                    <p class="text-sm font-medium">{{ session('success') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Filtro de punto -->
                <div class="mb-6">
                    <label for="punto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Punto
                    </label>
                    <select wire:model.live="punto"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-800 dark:text-white">
                        <option value="">Selecciona un punto</option>

                        @foreach ($subpuntosMap as $puntoGeneral => $subpuntos)
                            <optgroup label="{{ $puntoGeneral }}">
                                <option value="{{ $puntoGeneral }}">
                                    (Todos) {{ $puntoGeneral }}
                                    @if (in_array($puntoGeneral, $puntosConAsistencia))
                                        <span class="ml-1">✔️</span>
                                    @endif
                                </option>
                                @foreach ($subpuntos as $subpunto)
                                    <option value="{{ $subpunto['nombre'] }}">
                                        {{ $subpunto['nombre'] }}
                                        @if ($subpunto['codigo'])
                                            ({{ str_pad($subpunto['codigo'], 3, '0', STR_PAD_LEFT) }})
                                        @endif
                                        @if (in_array($subpunto['nombre'], $puntosConAsistencia))
                                            <span class="ml-1">✔️</span>
                                        @endif
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha -->
                @if ($punto)
                    <div class="mb-6">
                        <label for="fecha" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fecha
                        </label>
                        <input type="date" wire:model.live="fecha"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-800 dark:text-white">
                    </div>

                    <!-- Estado de asistencia -->
                    @if ($asistenciaExiste)
                        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
                            <div class="flex items-center">
                                <span class="status-indicator status-completed"></span>
                                <span class="text-sm font-medium text-green-800 dark:text-green-200">
                                    ✅ Asistencia ya registrada para este punto y fecha.
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="mb-6 p-4 bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <div class="flex items-center">
                                <span class="status-indicator status-pending"></span>
                                <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                    ⏳ No hay asistencia registrada aún para este punto y fecha.
                                </span>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Lista de usuarios -->
                @if ($punto && $usuarios->isNotEmpty())
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Usuarios de {{ $punto }}
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach ($usuarios as $usuario)
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-md">
                                    <div class="p-5">
                                        <div class="flex items-center space-x-4 mb-4">
                                            @if ($usuario->solicitudAlta?->documentacion?->arch_foto)
                                                <img src="{{ asset('storage/' . str_replace('storage/', '', $usuario->solicitudAlta->documentacion->arch_foto)) }}"
                                                     alt="Foto de {{ $usuario->name }}"
                                                     class="w-16 h-16 rounded-full object-cover border-2 border-white dark:border-gray-600 shadow-sm">
                                            @else
                                                <div class="flex-shrink-0 w-16 h-16 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center">
                                                    <span class="text-white font-medium text-lg">
                                                        {{ substr($usuario->name ?? '', 0, 2) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                                    {{ $usuario->name }}
                                                </h2>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                    {{ $usuario->empresa }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ $usuario->punto }}
                                                </p>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 mt-1">
                                                    {{ $usuario->rol }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="space-y-3">
                                            <!-- Estatus -->
                                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Estatus
                                                </label>
                                                <div class="flex space-x-2">
                                                    <label class="flex items-center">
                                                        <input type="radio"
                                                               wire:model.live="estatusPorUsuario.{{ $usuario->id }}"
                                                               value="asistio"
                                                               class="h-4 w-4 text-green-600 focus:ring-green-500"
                                                               @if($asistenciaExiste) disabled @endif>
                                                        <span class="ml-1 text-sm text-green-700 dark:text-green-300">Asistió</span>
                                                    </label>
                                                    <label class="flex items-center">
                                                        <input type="radio"
                                                               wire:model.live="estatusPorUsuario.{{ $usuario->id }}"
                                                               value="falto"
                                                               class="h-4 w-4 text-red-600 focus:ring-red-500"
                                                               @if($asistenciaExiste) disabled @endif>
                                                        <span class="ml-1 text-sm text-red-700 dark:text-red-300">Faltó</span>
                                                    </label>
                                                    <label class="flex items-center">
                                                        <input type="radio"
                                                               wire:model.live="estatusPorUsuario.{{ $usuario->id }}"
                                                               value="descanso"
                                                               class="h-4 w-4 text-yellow-600 focus:ring-yellow-500"
                                                               @if($asistenciaExiste) disabled @endif>
                                                        <span class="ml-1 text-sm text-yellow-700 dark:text-yellow-300">Descansó</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Turnos (solo si asistió) -->
                                            @if(!$asistenciaExiste && ($estatusPorUsuario[$usuario->id] ?? '') === 'asistio')
                                                <div class="mt-3">
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Selecciona turno(s):</p>
                                                    <div class="flex space-x-4">
                                                        <label class="flex items-center">
                                                            <input type="checkbox"
                                                                   wire:model.live="turnosPorUsuario.{{ $usuario->id }}"
                                                                   value="dia"
                                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Día</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input type="checkbox"
                                                                   wire:model.live="turnosPorUsuario.{{ $usuario->id }}"
                                                                   value="tarde"
                                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Tarde</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input type="checkbox"
                                                                   wire:model.live="turnosPorUsuario.{{ $usuario->id }}"
                                                                   value="noche"
                                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Noche</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Botón de guardar -->
                    @if (!$asistenciaExiste)
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex justify-center">
                                <button wire:click="guardarAsistencia"
                                        class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Guardar Asistencia
                                </button>
                            </div>
                        </div>
                    @endif
                @endif

            </div>
        </div>
    </div>
</div>
