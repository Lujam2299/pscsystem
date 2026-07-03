<!-- resources/views/livewire/editar-mision.php -->
<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="mx-auto max-w-7xl">
            <div class="bg-white rounded-lg p-6">
                <div class="border-b pb-6 mb-6">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h1 class="text-xl font-semibold text-gray-900">Editar Misión</h1>
                    </div>
                    <p class="mt-1 text-sm text-gray-600">
                        Modifica la información de la misión
                    </p>
                </div>
                <form action="{{ route('misiones.update', $mision->id) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Inicio
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio"
                                value="{{ old('fecha_inicio', $mision->fecha_inicio) }}"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="actualizarAgentes()" required>
                        </div>
                        <div>
                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Fin
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin"
                                value="{{ old('fecha_fin', $mision->fecha_fin) }}"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="actualizarAgentes()" required>
                        </div>
                    </div>
                    <div>
                        <label for="agentes" class="block text-sm font-medium text-gray-700 mb-2">
                            Agentes Asignados
                        </label>
                        <x-custodios-agent-selector :selected="old('agentes_id', $mision->agentes_id ?? [])" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="armados" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Agentes
                            </label>
                            <select name="armados" id="armados"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="" disabled>Selecciona tipo</option>
                                <option value="armado" {{ old('armados', $mision->armados) == 'armado' ? 'selected' : '' }}>Armados</option>
                                <option value="desarmado" {{ old('armados', $mision->armados) == 'desarmado' ? 'selected' : '' }}>Desarmados</option>
                            </select>
                        </div>
                        <div>
                            <label for="tipo_servicio" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Servicio
                            </label>
                            <input type="text" name="tipo_servicio" id="tipo_servicio"
                                value="{{ old('tipo_servicio', $mision->tipo_servicio) }}"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 01-6 0 3 3 0 016 0z" />
                            </svg>
                            Ubicaciones
                        </h2>
                        <div id="ubicaciones-container" class="space-y-4 mb-4">
                            @forelse(old('ubicaciones', $mision->ubicacion ?? []) as $index => $ubicacion)
                                <div class="ubicacion-item bg-gray-50 p-4 rounded-lg">
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                                        <input type="text" name="ubicaciones[{{ $index }}][direccion]"
                                               value="{{ $ubicacion['direccion'] }}"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 geocoder-input"
                                               placeholder="Ej. Ciudad, Estado"
                                               id="direccion-{{ $index }}"
                                               data-lat-input-id="latitud-{{ $index }}"
                                               data-lng-input-id="longitud-{{ $index }}"
                                               data-display-lat-id="display-latitud-{{ $index }}"
                                               data-display-lng-id="display-longitud-{{ $index }}">
                                        <input type="hidden" name="ubicaciones[{{ $index }}][latitud]" id="latitud-{{ $index }}" value="{{ $ubicacion['lat'] }}">
                                        <input type="hidden" name="ubicaciones[{{ $index }}][longitud]" id="longitud-{{ $index }}" value="{{ $ubicacion['lng'] }}">
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Lat: <span id="display-latitud-{{ $index }}">{{ $ubicacion['lat'] ?? '-' }}</span> | Lng: <span id="display-longitud-{{ $index }}">{{ $ubicacion['lng'] ?? '-' }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="ubicacion-item bg-gray-50 p-4 rounded-lg">
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                                        <input type="text" name="ubicaciones[0][direccion]"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 geocoder-input"
                                               placeholder="Ej. Ciudad, Estado"
                                               id="direccion-0"
                                               data-lat-input-id="latitud-0"
                                               data-lng-input-id="longitud-0"
                                               data-display-lat-id="display-latitud-0"
                                               data-display-lng-id="display-longitud-0">
                                        <input type="hidden" name="ubicaciones[0][latitud]" id="latitud-0" value="">
                                        <input type="hidden" name="ubicaciones[0][longitud]" id="longitud-0" value="">
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Lat: <span id="display-latitud-0">-</span> | Lng: <span id="display-longitud-0">-</span>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" onclick="agregarUbicacion()"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Añadir otra ubicación
                        </button>
                    </div>
                    <!-- Bloque Cliente -->
                    <div>
                        <label for="cliente" class="block text-sm font-medium text-gray-700 mb-2">
                            Cliente
                        </label>
                        <input type="text" name="cliente" id="cliente"
                               value="{{ old('cliente', $mision->cliente) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <!-- Bloque Información Adicional -->
                    <div class="border-t pt-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Información Adicional
                        </h2>

                        <!-- Hoteles -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Hoteles
                            </h3>
                            <div id="hoteles-container" class="space-y-4">
                                @forelse(old('hoteles', $mision->datos_hotel ?? []) as $index => $hotel)
                                    <div class="hotel-item bg-white p-3 rounded border relative">
                                        <input type="text" name="hoteles[{{ $index }}][nombre]"
                                            value="{{ $hotel['nombre'] }}"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 geocoder-hotel-input"
                                            placeholder="Nombre del Hotel"
                                            id="hotel-nombre-{{ $index }}"
                                            data-lat-input-id="hotel-lat-{{ $index }}"
                                            data-lng-input-id="hotel-lng-{{ $index }}"
                                            data-display-lat-id="display-hotel-lat-{{ $index }}"
                                            data-display-lng-id="display-hotel-lng-{{ $index }}">
                                        <input type="hidden" name="hoteles[{{ $index }}][latitud]" id="hotel-lat-{{ $index }}" value="{{ $hotel['latitud'] }}">
                                        <input type="hidden" name="hoteles[{{ $index }}][longitud]" id="hotel-lng-{{ $index }}" value="{{ $hotel['longitud'] }}">
                                        <div class="text-xs text-gray-500 mt-1">
                                            Lat: <span id="display-hotel-lat-{{ $index }}">{{ $hotel['latitud'] ?? '-' }}</span> | Lng: <span id="display-hotel-lng-{{ $index }}">{{ $hotel['longitud'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="hotel-item bg-white p-3 rounded border relative">
                                        <input type="text" name="hoteles[0][nombre]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 geocoder-hotel-input"
                                            placeholder="Nombre del Hotel"
                                            id="hotel-nombre-0"
                                            data-lat-input-id="hotel-lat-0"
                                            data-lng-input-id="hotel-lng-0"
                                            data-display-lat-id="display-hotel-lat-0"
                                            data-display-lng-id="display-hotel-lng-0">
                                        <input type="hidden" name="hoteles[0][latitud]" id="hotel-lat-0" value="">
                                        <input type="hidden" name="hoteles[0][longitud]" id="hotel-lng-0" value="">
                                        <div class="text-xs text-gray-500 mt-1">
                                            Lat: <span id="display-hotel-lat-0">-</span> | Lng: <span id="display-hotel-lng-0">-</span>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" onclick="agregarHotel()"
                                class="mt-3 inline-flex items-center px-3 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Añadir otro hotel
                            </button>
                        </div>

                        <!-- Aeropuertos -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Aeropuertos
                            </h3>
                            <div id="aeropuertos-container" class="space-y-4">
                                @forelse(old('aeropuertos', $mision->datos_aeropuerto ?? []) as $index => $aeropuerto)
                                    <div class="aeropuerto-item bg-white p-3 rounded border relative">
                                        <input type="text" name="aeropuertos[{{ $index }}][nombre]"
                                            value="{{ $aeropuerto['nombre'] }}"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 geocoder-airport-input"
                                            placeholder="Nombre del Aeropuerto"
                                            id="aeropuerto-nombre-{{ $index }}"
                                            data-lat-input-id="aeropuerto-lat-{{ $index }}"
                                            data-lng-input-id="aeropuerto-lng-{{ $index }}"
                                            data-display-lat-id="display-aeropuerto-lat-{{ $index }}"
                                            data-display-lng-id="display-aeropuerto-lng-{{ $index }}">
                                        <input type="hidden" name="aeropuertos[{{ $index }}][latitud]" id="aeropuerto-lat-{{ $index }}" value="{{ $aeropuerto['latitud'] }}">
                                        <input type="hidden" name="aeropuertos[{{ $index }}][longitud]" id="aeropuerto-lng-{{ $index }}" value="{{ $aeropuerto['longitud'] }}">
                                        <div class="text-xs text-gray-500 mt-1">
                                            Lat: <span id="display-aeropuerto-lat-{{ $index }}">{{ $aeropuerto['latitud'] ?? '-' }}</span> | Lng: <span id="display-aeropuerto-lng-{{ $index }}">{{ $aeropuerto['longitud'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="aeropuerto-item bg-white p-3 rounded border relative">
                                        <input type="text" name="aeropuertos[0][nombre]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 geocoder-airport-input"
                                            placeholder="Nombre del Aeropuerto"
                                            id="aeropuerto-nombre-0"
                                            data-lat-input-id="aeropuerto-lat-0"
                                            data-lng-input-id="aeropuerto-lng-0"
                                            data-display-lat-id="display-aeropuerto-lat-0"
                                            data-display-lng-id="display-aeropuerto-lng-0">
                                        <input type="hidden" name="aeropuertos[0][latitud]" id="aeropuerto-lat-0" value="">
                                        <input type="hidden" name="aeropuertos[0][longitud]" id="aeropuerto-lng-0" value="">
                                        <div class="text-xs text-gray-500 mt-1">
                                            Lat: <span id="display-aeropuerto-lat-0">-</span> | Lng: <span id="display-aeropuerto-lng-0">-</span>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" onclick="agregarAeropuerto()"
                                class="mt-3 inline-flex items-center px-3 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Añadir otro aeropuerto
                            </button>
                        </div>

                        <!-- Datos del Vuelo -->
                        @php
                            $datosVuelo = json_decode($mision->datos_vuelo, true) ?? [];
                            $llegada = $datosVuelo['llegada'] ?? [];
                            $salida = $datosVuelo['salida'] ?? [];
                        @endphp
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Datos del Vuelo de Llegada
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                                    <input type="date" name="vuelo_llegada[fecha]"
                                        value="{{ old('vuelo_llegada.fecha', $llegada['fecha'] ?? '') }}"
                                        class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Flight</label>
                                    <input type="text" name="vuelo_llegada[flight]"
                                        value="{{ old('vuelo_llegada.flight', $llegada['flight'] ?? '') }}"
                                        class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hora</label>
                                    <input type="time" name="vuelo_llegada[hora]"
                                        value="{{ old('vuelo_llegada.hora', $llegada['hora'] ?? '') }}"
                                        class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Datos del Vuelo de Salida
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                                    <input type="date" name="vuelo_salida[fecha]"
                                        value="{{ old('vuelo_salida.fecha', $salida['fecha'] ?? '') }}"
                                        class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Flight</label>
                                    <input type="text" name="vuelo_salida[flight]"
                                        value="{{ old('vuelo_salida.flight', $salida['flight'] ?? '') }}"
                                        class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hora</label>
                                    <input type="time" name="vuelo_salida[hora]"
                                        value="{{ old('vuelo_salida.hora', $salida['hora'] ?? '') }}"
                                        class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3 pt-6 border-t">
                        <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Actualizar Misión
                        </button>
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Regresar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variables para los índices dinámicos
        let ubicacionIndex = document.querySelectorAll('.ubicacion-item').length;
        let hotelIndex = document.querySelectorAll('.hotel-item').length;
        let aeropuertoIndex = document.querySelectorAll('.aeropuerto-item').length;

        // Función unificada para autocompletado
        function createSuggestionHandler(inputElement, latInputId, lngInputId, displayLatId, displayLngId, filterFn) {
            let debounceTimer;
            inputElement.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                const query = e.target.value.trim();
                if (query.length < 3) {
                    hideSuggestions(inputElement.id);
                    return;
                }
                debounceTimer = setTimeout(() => {
                    const url = `https://nominatim.openstreetmap.org/search?q=  ${encodeURIComponent(query)}&format=json&limit=5&addressdetails=1&countrycodes=MX,US`;
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            const filtered = data.filter(filterFn);
                            showSuggestions(filtered, inputElement, latInputId, lngInputId, displayLatId, displayLngId);
                        })
                        .catch(console.error);
                }, 300);
            });

            // Evento para actualizar manualmente lat/lng y visualización
            inputElement.addEventListener('change', function(e) {
                const latInput = document.getElementById(latInputId);
                const lngInput = document.getElementById(lngInputId);
                const displayLat = document.getElementById(displayLatId);
                const displayLng = document.getElementById(displayLngId);

                if (!latInput.value && !lngInput.value) {
                    // Si no hay lat/lng, intenta geocodificar la dirección
                    const direccion = e.target.value.trim();
                    if (direccion) {
                        const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(direccion)}&format=json&limit=1&addressdetails=1&countrycodes=MX,US`;
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                if (data.length > 0) {
                                    const coords = data[0];
                                    latInput.value = coords.lat;
                                    lngInput.value = coords.lon;
                                    displayLat.textContent = parseFloat(coords.lat).toFixed(6);
                                    displayLng.textContent = parseFloat(coords.lon).toFixed(6);
                                }
                            })
                            .catch(console.error);
                    }
                }
            });

            function hideSuggestions(id) {
                const el = document.getElementById(`suggestions-${id}`);
                if (el) el.remove();
            }

            function showSuggestions(suggestions, input, latId, lngId, dispLatId, dispLngId) {
                hideSuggestions(input.id);
                if (!suggestions.length) return;
                const container = document.createElement('ul');
                container.id = `suggestions-${input.id}`;
                container.className = 'absolute z-10 bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto w-full';
                suggestions.forEach(s => {
                    const li = document.createElement('li');
                    li.className = 'p-2 hover:bg-gray-100 cursor-pointer text-sm';
                    li.textContent = s.display_name || `${s.name}, ${s.type}`;
                    li.addEventListener('click', () => {
                        input.value = s.display_name;
                        document.getElementById(latId).value = s.lat;
                        document.getElementById(lngId).value = s.lon;
                        document.getElementById(dispLatId).textContent = parseFloat(s.lat).toFixed(6);
                        document.getElementById(dispLngId).textContent = parseFloat(s.lon).toFixed(6);
                        container.remove();
                    });
                    container.appendChild(li);
                });
                input.parentNode.appendChild(container);
            }

            inputElement.addEventListener('blur', () => setTimeout(() => hideSuggestions(inputElement.id), 200));
            inputElement.addEventListener('focus', () => hideSuggestions(inputElement.id));
        }

        function initOsmautocomplete(inputElement) {
            const latInputId = inputElement.getAttribute('data-lat-input-id');
            const lngInputId = inputElement.getAttribute('data-lng-input-id');
            const displayLatId = inputElement.getAttribute('data-display-lat-id');
            const displayLngId = inputElement.getAttribute('data-display-lng-id');
            const filter = s => ['city', 'town', 'village'].includes(s.addresstype);
            createSuggestionHandler(inputElement, latInputId, lngInputId, displayLatId, displayLngId, filter);
        }

        function initHotelAutocomplete(inputElement) {
            const latInputId = inputElement.getAttribute('data-lat-input-id');
            const lngInputId = inputElement.getAttribute('data-lng-input-id');
            const displayLatId = inputElement.getAttribute('data-display-lat-id');
            const displayLngId = inputElement.getAttribute('data-display-lng-id');
            const filter = s => (s.type === 'hotel' && s.class === 'tourism') ||
                                (s.tags && s.tags.tourism === 'hotel');
            createSuggestionHandler(inputElement, latInputId, lngInputId, displayLatId, displayLngId, filter);
        }

        function initAirportAutocomplete(inputElement) {
            const latInputId = inputElement.getAttribute('data-lat-input-id');
            const lngInputId = inputElement.getAttribute('data-lng-input-id');
            const displayLatId = inputElement.getAttribute('data-display-lat-id');
            const displayLngId = inputElement.getAttribute('data-display-lng-id');
            const filter = s => (s.type === 'airport' && s.class === 'aeroway') ||
                                (s.type === 'aerodrome' && s.class === 'aeroway') ||
                                (s.tags && (s.tags.aeroway === 'airport' || s.tags.aeroway === 'aerodrome'));
            createSuggestionHandler(inputElement, latInputId, lngInputId, displayLatId, displayLngId, filter);
        }

        // ====== AGREGAR UBICACIONES / HOTELES / AEROPUERTOS ======
        function agregarUbicacion() {
            const container = document.getElementById('ubicaciones-container');
            const div = document.createElement('div');
            div.className = 'ubicacion-item bg-gray-50 p-4 rounded-lg';
            div.innerHTML = `
                <div class="mb-3 relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                    <input type="text" name="ubicaciones[${ubicacionIndex}][direccion]"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 geocoder-input"
                           placeholder="Ciudad"
                           id="direccion-${ubicacionIndex}"
                           data-lat-input-id="latitud-${ubicacionIndex}"
                           data-lng-input-id="longitud-${ubicacionIndex}"
                           data-display-lat-id="display-latitud-${ubicacionIndex}"
                           data-display-lng-id="display-longitud-${ubicacionIndex}">
                    <input type="hidden" name="ubicaciones[${ubicacionIndex}][latitud]" id="latitud-${ubicacionIndex}" value="">
                    <input type="hidden" name="ubicaciones[${ubicacionIndex}][longitud]" id="longitud-${ubicacionIndex}" value="">
                </div>
                <div class="text-sm text-gray-500">
                    Lat: <span id="display-latitud-${ubicacionIndex}">-</span> | Lng: <span id="display-longitud-${ubicacionIndex}">-</span>
                </div>
                <div class="mt-4">
                    <button type="button" onclick="removeUbicacion(this)" class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded-md transition duration-200 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar
                    </button>
                </div>
            `;
            container.appendChild(div);
            const input = div.querySelector('.geocoder-input');
            if (input && !input.dataset.osmInitialized) {
                initOsmautocomplete(input);
                input.dataset.osmInitialized = 'true';
            }
            ubicacionIndex++;
        }

        function removeUbicacion(btn) {
            if (document.querySelectorAll('.ubicacion-item').length > 1) {
                btn.closest('.ubicacion-item').remove();
            }
        }

        function agregarHotel() {
            const container = document.getElementById('hoteles-container');
            const div = document.createElement('div');
            div.className = 'hotel-item bg-white p-3 rounded border relative';
            div.innerHTML = `
                <input type="text" name="hoteles[${hotelIndex}][nombre]"
                    class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 geocoder-hotel-input"
                    placeholder="Nombre del Hotel"
                    id="hotel-nombre-${hotelIndex}"
                    data-lat-input-id="hotel-lat-${hotelIndex}"
                    data-lng-input-id="hotel-lng-${hotelIndex}"
                    data-display-lat-id="display-hotel-lat-${hotelIndex}"
                    data-display-lng-id="display-hotel-lng-${hotelIndex}">
                <input type="hidden" name="hoteles[${hotelIndex}][latitud]" id="hotel-lat-${hotelIndex}" value="">
                <input type="hidden" name="hoteles[${hotelIndex}][longitud]" id="hotel-lng-${hotelIndex}" value="">
                <div class="text-xs text-gray-500 mt-1">
                    Lat: <span id="display-hotel-lat-${hotelIndex}">-</span> | Lng: <span id="display-hotel-lng-${hotelIndex}">-</span>
                </div>
                <button type="button" onclick="eliminarHotel(this)"
                    class="absolute top-1 right-1 inline-flex items-center px-2 py-0.5 bg-red-500 hover:bg-red-600 text-white text-xs rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            container.appendChild(div);
            const input = div.querySelector('.geocoder-hotel-input');
            if (input && !input.dataset.osmInitialized) {
                initHotelAutocomplete(input);
                input.dataset.osmInitialized = 'true';
            }
            hotelIndex++;
        }

        function eliminarHotel(btn) {
            if (document.querySelectorAll('.hotel-item').length > 1) {
                btn.closest('.hotel-item').remove();
            }
        }

        function agregarAeropuerto() {
            const container = document.getElementById('aeropuertos-container');
            const div = document.createElement('div');
            div.className = 'aeropuerto-item bg-white p-3 rounded border relative';
            div.innerHTML = `
                <input type="text" name="aeropuertos[${aeropuertoIndex}][nombre]"
                    class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 geocoder-airport-input"
                    placeholder="Nombre del Aeropuerto"
                    id="aeropuerto-nombre-${aeropuertoIndex}"
                    data-lat-input-id="aeropuerto-lat-${aeropuertoIndex}"
                    data-lng-input-id="aeropuerto-lng-${aeropuertoIndex}"
                    data-display-lat-id="display-aeropuerto-lat-${aeropuertoIndex}"
                    data-display-lng-id="display-aeropuerto-lng-${aeropuertoIndex}">
                <input type="hidden" name="aeropuertos[${aeropuertoIndex}][latitud]" id="aeropuerto-lat-${aeropuertoIndex}" value="">
                <input type="hidden" name="aeropuertos[${aeropuertoIndex}][longitud]" id="aeropuerto-lng-${aeropuertoIndex}" value="">
                <div class="text-xs text-gray-500 mt-1">
                    Lat: <span id="display-aeropuerto-lat-${aeropuertoIndex}">-</span> | Lng: <span id="display-aeropuerto-lng-${aeropuertoIndex}">-</span>
                </div>
                <button type="button" onclick="eliminarAeropuerto(this)"
                    class="absolute top-1 right-1 inline-flex items-center px-2 py-0.5 bg-red-500 hover:bg-red-600 text-white text-xs rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            container.appendChild(div);
            const input = div.querySelector('.geocoder-airport-input');
            if (input && !input.dataset.osmInitialized) {
                initAirportAutocomplete(input);
                input.dataset.osmInitialized = 'true';
            }
            aeropuertoIndex++;
        }

        function eliminarAeropuerto(btn) {
            if (document.querySelectorAll('.aeropuerto-item').length > 1) {
                btn.closest('.aeropuerto-item').remove();
            }
        }

        // Inicializar al cargar
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.geocoder-input').forEach(el => {
                if (!el.dataset.osmInitialized) {
                    initOsmautocomplete(el);
                    el.dataset.osmInitialized = 'true';
                }
            });
            document.querySelectorAll('.geocoder-hotel-input').forEach(el => {
                if (!el.dataset.osmInitialized) {
                    initHotelAutocomplete(el);
                    el.dataset.osmInitialized = 'true';
                }
            });
            document.querySelectorAll('.geocoder-airport-input').forEach(el => {
                if (!el.dataset.osmInitialized) {
                    initAirportAutocomplete(el);
                    el.dataset.osmInitialized = 'true';
                }
            });
        });

        // Función para actualizar agentes
        function actualizarAgentes() {
            const inicio = document.getElementById('fecha_inicio').value;
            const fin = document.getElementById('fecha_fin').value;
            const select = document.getElementById('agentes');
            if (!inicio || !fin) {
                window.renderCustodiosAgentSelector([], [], 'Selecciona las fechas para consultar disponibilidad.');
                return;
            }
            const selectedOptions = Array.from(select.selectedOptions).map(opt => opt.value);
            fetch('{{ route('custodios.agentesDisponibles') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    fecha_inicio: inicio,
                    fecha_fin: fin,
                    mision_id: {{ $mision->id }}
                })
            })
            .then(async res => {
                if (!res.ok) throw new Error((await res.json()).message || 'No fue posible consultar disponibilidad.');
                return res.json();
            })
            .then(agentes => window.renderCustodiosAgentSelector(
                agentes,
                selectedOptions.length ? selectedOptions : null
            ))
            .catch(error => window.renderCustodiosAgentSelector([], [], error.message));
        }

        document.addEventListener('DOMContentLoaded', actualizarAgentes);
    </script>
</x-app-layout>
