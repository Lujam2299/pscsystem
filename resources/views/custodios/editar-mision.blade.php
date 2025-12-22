<!-- resources/views/livewire/mapa-geocercas.blade.php -->
<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Editar Misión: {{ $mision->nombre_clave ?? $mision->id }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Actualice la información de la misión
                            </p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('misiones.update', $mision->id) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de Inicio
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                value="{{ old('fecha_inicio', $mision->fecha_inicio) }}" onchange="actualizarAgentes()" required>
                        </div>

                        <div>
                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de Fin
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                value="{{ old('fecha_fin', $mision->fecha_fin) }}" onchange="actualizarAgentes()" required>
                        </div>
                    </div>

                    <div>
                        <label for="agentes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Agentes Asignados
                        </label>
                        <select id="agentes" name="agentes_id[]"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                multiple required>
                            @foreach($agentesDisponibles as $agente)
                                <option value="{{ $agente->id }}"
                                    {{ in_array($agente->id, old('agentes_id', $mision->agentes_id ?? [])) ? 'selected' : '' }}>
                                    {{ $agente->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Usa Ctrl + Clic para seleccionar varios agentes
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="armados" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Agentes
                            </label>
                            <select name="armados" id="armados"
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    required>
                                <option value="" disabled>Selecciona tipo</option>
                                <option value="armado" {{ old('armados', $mision->armados) == 'armado' ? 'selected' : '' }}>Armados</option>
                                <option value="desarmado" {{ old('armados', $mision->armados) == 'desarmado' ? 'selected' : '' }}>Desarmados</option>
                            </select>
                        </div>

                        <div>
                            <label for="tipo_servicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Servicio
                            </label>
                            <input type="text" name="tipo_servicio" id="tipo_servicio"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                value="{{ old('tipo_servicio', $mision->tipo_servicio) }}" required>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Ubicaciones
                            </h2>
                        </div>

                        <div id="ubicaciones-container" class="space-y-4 mb-4">
                            @forelse(old('ubicaciones', $mision->ubicacion ?? []) as $index => $ubicacion)
                                <div class="ubicacion-item bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Dirección
                                    </label>
                                    <input type="text" name="ubicaciones[{{ $index }}][direccion]"
                                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white mb-3 geocoder-input"
                                        placeholder="Ej. Calle X, Ciudad, Estado"
                                        id="direccion-{{ $index }}"
                                        data-geocode-target="address-input"
                                        data-lat-input-id="latitud-{{ $index }}"
                                        data-lng-input-id="longitud-{{ $index }}"
                                        data-display-lat-id="display-latitud-{{ $index }}"
                                        data-display-lng-id="display-longitud-{{ $index }}"
                                        value="{!! $ubicacion['direccion'] ?? '' !!}"> <!-- CORREGIDO: Se usa !! para evitar escapado en el value -->

                                    <input type="hidden" name="ubicaciones[{{ $index }}][latitud]" id="latitud-{{ $index }}" value="{{ $ubicacion['latitud'] ?? '' }}">
                                    <input type="hidden" name="ubicaciones[{{ $index }}][longitud]" id="longitud-{{ $index }}" value="{{ $ubicacion['longitud'] ?? '' }}">

                                    <div class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                                        Latitud: <span id="display-latitud-{{ $index }}">{{ $ubicacion['latitud'] ?? '-' }}</span> |
                                        Longitud: <span id="display-longitud-{{ $index }}">{{ $ubicacion['longitud'] ?? '-' }}</span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Latitud
                                            </label>
                                            <input type="text" name="ubicaciones[{{ $index }}][latitud]"
                                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                id="manual-latitud-{{ $index }}" placeholder="Manual" value="{{ $ubicacion['latitud'] ?? '' }}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Longitud
                                            </label>
                                            <input type="text" name="ubicaciones[{{ $index }}][longitud]"
                                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                id="manual-longitud-{{ $index }}" placeholder="Manual" value="{{ $ubicacion['longitud'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <!-- Caso base si no hay ubicaciones -->
                                <div class="ubicacion-item bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Dirección
                                    </label>
                                    <input type="text" name="ubicaciones[0][direccion]"
                                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white mb-3 geocoder-input"
                                        placeholder="Ej. Calle X, Ciudad, Estado"
                                        id="direccion-0"
                                        data-geocode-target="address-input"
                                        data-lat-input-id="latitud-0"
                                        data-lng-input-id="longitud-0"
                                        data-display-lat-id="display-latitud-0"
                                        data-display-lng-id="display-longitud-0"
                                        value=""> <!-- Vacío aquí, no necesita !! -->

                                    <input type="hidden" name="ubicaciones[0][latitud]" id="latitud-0" value="">
                                    <input type="hidden" name="ubicaciones[0][longitud]" id="longitud-0" value="">

                                    <div class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                                        Latitud: <span id="display-latitud-0">-</span> |
                                        Longitud: <span id="display-longitud-0">-</span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Latitud
                                            </label>
                                            <input type="text" name="ubicaciones[0][latitud]"
                                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                id="manual-latitud-0" placeholder="Manual" value="">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Longitud
                                            </label>
                                            <input type="text" name="ubicaciones[0][longitud]"
                                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                id="manual-longitud-0" placeholder="Manual" value="">
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <button type="button" onclick="agregarUbicacion()"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Añadir otra ubicación
                        </button>
                    </div>

                    <div>
                        <label for="cliente" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Cliente
                        </label>
                        <input type="text" name="cliente" id="cliente"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               value="{{ old('cliente', $mision->cliente) }}">
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Información Adicional
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Hotel -->
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Hotel
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                        <input type="text" name="hotel[nombre]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            value="{{ old('hotel.nombre', $mision->datos_hotel['nombre'] ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Aeropuerto -->
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Aeropuerto
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                        <input type="text" name="aeropuerto[nombre]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            value="{{ old('aeropuerto.nombre', $mision->datos_aeropuerto['nombre'] ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Datos del Vuelo (Llegada) -->
                            <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Datos del Vuelo de Llegada
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                                        <input type="date" name="vuelo_llegada[fecha]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            value="{{ old('vuelo_llegada.fecha', $mision->datos_vuelo_llegada['fecha'] ?? '') }}">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Flight</label>
                                        <input type="text" name="vuelo_llegada[flight]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            value="{{ old('vuelo_llegada.flight', $mision->datos_vuelo_llegada['flight'] ?? '') }}">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hora</label>
                                        <input type="time" name="vuelo_llegada[hora]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            value="{{ old('vuelo_llegada.hora', $mision->datos_vuelo_llegada['hora'] ?? '') }}">
                                    </div>
                                </div>
                            </div>

                             <!-- Datos del Vuelo (Salida) -->
                             <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Datos del Vuelo de Salida
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                                        <input type="date" name="vuelo_salida[fecha]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            value="{{ old('vuelo_salida.fecha', $mision->datos_vuelo_salida['fecha'] ?? '') }}">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Flight</label>
                                        <input type="text" name="vuelo_salida[flight]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            value="{{ old('vuelo_salida.flight', $mision->datos_vuelo_salida['flight'] ?? '') }}">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hora</label>
                                        <input type="time" name="vuelo_salida[hora]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            value="{{ old('vuelo_salida.hora', $mision->datos_vuelo_salida['hora'] ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Actualizar Misión
                        </button>

                        <a href="{{ route('admin.mapaGeocercas') }}"
                           class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts para el autocompletado OSM (copiar desde nueva-mision.blade.php) -->
    <!-- Hojas de estilo de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css  " />
    <!-- Hoja de estilo del geocoder -->
    <link rel="stylesheet" href="https://unpkg.com/pelias-geocoder/dist/Control.Geocoder.css  " />

    <!-- Scripts de Leaflet y Geocoder -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js  "></script>
    <script src="https://unpkg.com/pelias-geocoder/dist/Control.Geocoder.min.js  "></script>

    <script>
        // --- VARIABLES GLOBALES ---
        let geocoders = [];
        let ubicacionIndex = {{ count(old('ubicaciones', $mision->ubicacion ?? [])) }}; // Iniciar el índice basado en las ubicaciones existentes

        // --- FUNCIONES DE AUTO-COMPLETADO Y GEOCODIFICACIÓN ---

        // Función para inicializar el autocompletado OSM en un input específico
        function initOsmautocomplete(inputElement) {
            const inputId = inputElement.id;
            const latInputId = inputElement.getAttribute('data-lat-input-id');
            const lngInputId = inputElement.getAttribute('data-lng-input-id');
            const displayLatId = inputElement.getAttribute('data-display-lat-id');
            const displayLngId = inputElement.getAttribute('data-display-lng-id');

            if (!inputElement || !latInputId || !lngInputId || !displayLatId || !displayLngId) {
                console.error("Faltan atributos data-* requeridos para initOsmautocomplete en:", inputElement);
                return;
            }

            let debounceTimer;
            inputElement.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                const query = e.target.value.trim();

                if (query.length < 3) {
                    hideSuggestions(inputElement.id);
                    return;
                }

                debounceTimer = setTimeout(() => {
                    // URL del servidor público de Nominatim con restricción de país
                    const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=5&addressdetails=1&countrycodes=MX,US`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            showSuggestions(data, inputElement, latInputId, lngInputId, displayLatId, displayLngId);
                        })
                        .catch(error => {
                            console.error('Error fetching suggestions from Nominatim:', error);
                        });
                }, 300);
            });

            // Función para mostrar sugerencias
            function showSuggestions(suggestions, inputElement, latInputId, lngInputId, displayLatId, displayLngId) {
                hideSuggestions(inputElement.id);

                if (suggestions.length === 0) return;

                const suggestionContainer = document.createElement('ul');
                suggestionContainer.id = `suggestions-${inputElement.id}`;
                suggestionContainer.className = 'absolute z-10 bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto w-full';

                suggestions.forEach((suggestion, index) => {
                    const li = document.createElement('li');
                    li.className = 'p-2 hover:bg-gray-100 cursor-pointer text-sm';
                    li.textContent = suggestion.display_name || `${suggestion.name}, ${suggestion.type}`;

                    li.addEventListener('click', function() {
                        inputElement.value = suggestion.display_name;
                        document.getElementById(latInputId).value = suggestion.lat;
                        document.getElementById(lngInputId).value = suggestion.lon;
                        document.getElementById(displayLatId).textContent = parseFloat(suggestion.lat).toFixed(6);
                        document.getElementById(displayLngId).textContent = parseFloat(suggestion.lon).toFixed(6);

                        // Opcional: Limpiar los campos manuales si se selecciona una sugerencia
                        const manualLatId = inputElement.id.replace('direccion-', 'manual-latitud-');
                        const manualLngId = inputElement.id.replace('direccion-', 'manual-longitud-');
                        const manualLatInput = document.getElementById(manualLatId);
                        const manualLngInput = document.getElementById(manualLngId);
                        if(manualLatInput) manualLatInput.value = '';
                        if(manualLngInput) manualLngInput.value = '';

                        suggestionContainer.remove();
                    });

                    suggestionContainer.appendChild(li);
                });

                inputElement.parentNode.appendChild(suggestionContainer);
            }

            // Función para ocultar sugerencias
            function hideSuggestions(inputId) {
                const list = document.getElementById(`suggestions-${inputId}`);
                if (list) list.remove();
            }

            // Limpiar sugerencias al perder foco
            inputElement.addEventListener('blur', function(e) {
                setTimeout(() => {
                    hideSuggestions(inputElement.id);
                }, 200);
            });

            // Limpiar sugerencias al enfocar (opcional)
            inputElement.addEventListener('focus', function(e) {
                hideSuggestions(inputElement.id);
            });
        }

        // Función para inicializar el autocompletado de Hotel (misma lógica, distinto estilo o filtro si es necesario)
        function initHotelAutocomplete(inputElement) {
            // Puedes reutilizar initOsmautocomplete si la lógica es la misma, o crear una específica
            // Por ahora, reutilizamos la base, pero puedes filtrar resultados si es necesario
            initOsmautocomplete(inputElement);
        }

        // Función para inicializar el autocompletado de Aeropuerto (misma lógica, distinto estilo o filtro si es necesario)
        function initAirportAutocomplete(inputElement) {
            // Puedes reutilizar initOsmautocomplete si la lógica es la misma, o crear una específica
            // Por ahora, reutilizamos la base, pero puedes filtrar resultados si es necesario
            initOsmautocomplete(inputElement);
        }

        // --- FUNCIONALIDAD PARA AGREGAR NUEVAS UBICACIONES ---
        function agregarUbicacion() {
            const container = document.getElementById('ubicaciones-container');
            const nuevaUbicacion = document.createElement('div');
            nuevaUbicacion.className = 'ubicacion-item bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg';

            nuevaUbicacion.innerHTML = `
                <div class="mb-3 relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dirección</label>
                    <input type="text" name="ubicaciones[${ubicacionIndex}][direccion]"
                           class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 geocoder-input"
                           placeholder="Ej. Calle X, Ciudad, Estado o Nombre de Lugar"
                           id="direccion-${ubicacionIndex}"
                           data-geocode-target="address-input"
                           data-lat-input-id="latitud-${ubicacionIndex}"
                           data-lng-input-id="longitud-${ubicacionIndex}"
                           data-display-lat-id="display-latitud-${ubicacionIndex}"
                           data-display-lng-id="display-longitud-${ubicacionIndex}"
                           value="">

                    <input type="hidden" name="ubicaciones[${ubicacionIndex}][latitud]" id="latitud-${ubicacionIndex}" value="">
                    <input type="hidden" name="ubicaciones[${ubicacionIndex}][longitud]" id="longitud-${ubicacionIndex}" value="">
                </div>

                <div class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                    Latitud: <span id="display-latitud-${ubicacionIndex}">-</span> |
                    Longitud: <span id="display-longitud-${ubicacionIndex}">-</span>
                </div>

                <div class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                    O ingresa latitud y longitud directamente:
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Latitud</label>
                        <input type="text" name="ubicaciones[${ubicacionIndex}][latitud]"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               id="manual-latitud-${ubicacionIndex}" placeholder="Manual" value="">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Longitud</label>
                        <input type="text" name="ubicaciones[${ubicacionIndex}][longitud]"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               id="manual-longitud-${ubicacionIndex}" placeholder="Manual" value="">
                    </div>
                </div>

                <div class="mt-4 flex items-end">
                    <button type="button" onclick="removeUbicacion(this)" class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded-md transition duration-200 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar
                    </button>
                </div>
            `;
            container.appendChild(nuevaUbicacion);

            // Inicializar geocoder para la nueva ubicación
            const newInputElement = nuevaUbicacion.querySelector('.geocoder-input');
            if (newInputElement) {
                 initOsmautocomplete(newInputElement);
                 newInputElement.dataset.osmInitialized = 'true';
            }

            // Añadir listener para limpiar campos automáticos si se editan los manuales
            const newManualLat = nuevaUbicacion.querySelector(`#manual-latitud-${ubicacionIndex}`);
            const newManualLng = nuevaUbicacion.querySelector(`#manual-longitud-${ubicacionIndex}`);
            const newAutoLat = nuevaUbicacion.querySelector(`#latitud-${ubicacionIndex}`);
            const newAutoLng = nuevaUbicacion.querySelector(`#longitud-${ubicacionIndex}`);

            if(newManualLat && newManualLng && newAutoLat && newAutoLng) {
                newManualLat.addEventListener('input', function() {
                    newAutoLat.value = this.value;
                    document.getElementById(`display-latitud-${ubicacionIndex}`).textContent = this.value;
                });
                newManualLng.addEventListener('input', function() {
                    newAutoLng.value = this.value;
                    document.getElementById(`display-longitud-${ubicacionIndex}`).textContent = this.value;
                });
            }

            ubicacionIndex++;
        }

        // --- FUNCIONALIDAD PARA ELIMINAR UBICACIONES ---
        function removeUbicacion(button) {
            const item = button.closest('.ubicacion-item');
            if (document.querySelectorAll('.ubicacion-item').length > 1) {
                item.remove();
            }
        }

        // --- FUNCIONALIDAD PARA ACTUALIZAR AGENTES (si aplica) ---
        function actualizarAgentes() {
            const inicio = document.getElementById('fecha_inicio').value;
            const fin = document.getElementById('fecha_fin').value;
            const select = document.getElementById('agentes');

            if (!inicio || !fin) return;

            fetch('/agentes-disponibles', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    fecha_inicio: inicio,
                    fecha_fin: fin
                })
            })
            .then(res => res.json())
            .then(agentes => {
                // Opcional: Actualizar el select de agentes si es necesario al cambiar fechas
                // select.innerHTML = '';
                // agentes.forEach(agente => {
                //     const option = document.createElement('option');
                //     option.value = agente.id;
                //     option.textContent = agente.name;
                //     if (mision->agentes_id.includes(agente.id)) option.selected = true; // Pre-seleccionar
                //     select.appendChild(option);
                // });
            });
        }

        // --- INICIALIZACIÓN ---
        // Función para inicializar geocoders en inputs existentes
        function initAllOsmautocompletes() {
            // Inicializar inputs de dirección (ubicaciones)
            document.querySelectorAll('.geocoder-input').forEach(inputElement => {
                // Verificar si ya está inicializado para evitar duplicados
                if (!inputElement.dataset.osmInitialized) {
                     initOsmautocomplete(inputElement);
                     // Marcar como inicializado
                     inputElement.dataset.osmInitialized = 'true';
                } else {
                    //console.log("Input ya inicializado:", inputElement.id);
                }
            });

            // Inicializar inputs de hotel (si aplica y las funciones existen)
            document.querySelectorAll('.geocoder-hotel-input').forEach(inputElement => {
                if (!inputElement.dataset.osmInitialized) {
                     initHotelAutocomplete(inputElement);
                     inputElement.dataset.osmInitialized = 'true';
                }
            });

            // Inicializar inputs de aeropuerto (si aplica y las funciones existen)
            document.querySelectorAll('.geocoder-airport-input').forEach(inputElement => {
                if (!inputElement.dataset.osmInitialized) {
                     initAirportAutocomplete(inputElement);
                     inputElement.dataset.osmInitialized = 'true';
                }
            });
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            initAllOsmautocompletes();
        });

    </script>
</x-app-layout>
