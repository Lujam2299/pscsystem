<!-- resources/views/misiones/nueva-mision.blade.php -->
<x-app-layout>
    <x-navbar></x-navbar>

    {{-- Fondo general suave para resaltar la tarjeta blanca --}}
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-10 px-4 sm:px-6 lg:px-8 flex justify-center">

        {{-- CONTENEDOR PRINCIPAL BLANCO UNIFICADO --}}
        <div class="w-full max-w-5xl bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">

            {{-- HEADER DEL FORMULARIO (Dentro de la tarjeta blanca) --}}
            <div class="px-8 py-8 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-2xl text-blue-600 dark:text-blue-400 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 7m0 13V7" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                                Nueva Misión Operativa
                            </h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Complete los detalles logísticos y asigne al personal disponible.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-xl transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancelar
                    </a>
                </div>
            </div>

            <form action="{{ route('misiones.store') }}" method="POST" class="p-8 space-y-10">
                @csrf

                {{-- SECCIÓN 1: DETALLES GENERALES --}}
                <section>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Información General
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Fechas -->
                        <div class="space-y-6">
                            <div>
                                <label for="fecha_inicio" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Fecha de Inicio</label>
                                <div class="relative">
                                    <input type="date" name="fecha_inicio" id="fecha_inicio"
                                        class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 text-base transition-all"
                                        onchange="actualizarAgentes()" required>
                                </div>
                            </div>
                            <div>
                                <label for="fecha_fin" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Fecha de Fin</label>
                                <div class="relative">
                                    <input type="date" name="fecha_fin" id="fecha_fin"
                                        class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 text-base transition-all"
                                        onchange="actualizarAgentes()" required>
                                </div>
                            </div>
                        </div>

                        <!-- Tipo de Servicio y Perfil -->
                        <div class="space-y-6">
                            <div>
                                <label for="tipo_servicio" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tipo de Servicio</label>
                                <input type="text" name="tipo_servicio" id="tipo_servicio" placeholder="Ej. Custodia de mercancía sensible"
                                    class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 text-base placeholder-gray-400 transition-all"
                                    required>
                            </div>
                            <div>
                                <label for="armados" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Perfil Requerido</label>
                                <div class="relative">
                                    <select name="armados" id="armados"
                                            class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 text-base appearance-none transition-all cursor-pointer"
                                            required>
                                        <option value="" disabled selected>Seleccione perfil...</option>
                                        <option value="armado">Armados</option>
                                        <option value="desarmado">Desarmados</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cliente (Full Width) -->
                        <div class="md:col-span-2">
                            <label for="cliente" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Cliente / Empresa Solicitante</label>
                            <input type="text" name="cliente" id="cliente" placeholder="Nombre completo del cliente"
                                   class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 text-base placeholder-gray-400 transition-all">
                        </div>

                        <!-- Agentes (Full Width) -->
                        <div class="md:col-span-2">
                            <label for="agentes" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Agentes Asignados
                                <span class="text-xs font-normal text-gray-500 ml-2 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-md">Selecciona una o varias tarjetas</span>
                            </label>
                            <x-custodios-agent-selector :selected="old('agentes_id', [])" />
                        </div>
                    </div>
                </section>

                {{-- SECCIÓN 2: UBICACIONES --}}
                <section class="pt-6 border-t border-dashed border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Ubicaciones Operativas
                    </h2>

                    <div id="ubicaciones-container" class="space-y-6">
                        <!-- Item Inicial -->
                        <div class="ubicacion-item relative bg-gray-50 dark:bg-gray-700/30 p-6 rounded-2xl border border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all shadow-sm">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Dirección Principal</label>
                            <div class="relative">
                                <input type="text" name="ubicaciones[0][direccion]"
                                       class="geocoder-input block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-white dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 pl-4 pr-10 text-base"
                                       placeholder="Escribe ciudad, calle o punto de referencia..."
                                       id="direccion-0"
                                       data-lat-input-id="latitud-0"
                                       data-lng-input-id="longitud-0"
                                       data-display-lat-id="display-latitud-0"
                                       data-display-lng-id="display-longitud-0"
                                       autocomplete="off">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 font-mono bg-white dark:bg-gray-800 px-3 py-2 rounded-lg inline-block border border-gray-200 dark:border-gray-600 shadow-sm">
                                <span>Lat: <span id="display-latitud-0" class="font-bold text-gray-700 dark:text-gray-300">-</span></span>
                                <span class="w-px h-3 bg-gray-300"></span>
                                <span>Lng: <span id="display-longitud-0" class="font-bold text-gray-700 dark:text-gray-300">-</span></span>
                            </div>
                            <input type="hidden" name="ubicaciones[0][latitud]" id="latitud-0" value="">
                            <input type="hidden" name="ubicaciones[0][longitud]" id="longitud-0" value="">
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="button" onclick="agregarUbicacion()"
                                class="inline-flex items-center px-5 py-3 border border-transparent text-sm font-semibold rounded-xl shadow-sm text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Añadir otra ubicación
                        </button>
                    </div>
                </section>

                {{-- SECCIÓN 3: LOGÍSTICA DE VIAJE --}}
                <section class="pt-6 border-t border-dashed border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Logística y Hospedaje
                    </h2>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <!-- Columna Izquierda: Hoteles y Vuelo Llegada -->
                        <div class="space-y-6">
                            <!-- Hoteles -->
                            <div class="bg-indigo-50/50 dark:bg-indigo-900/10 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-800/30">
                                <h3 class="text-sm font-bold text-indigo-900 dark:text-indigo-300 mb-4 uppercase tracking-wide flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Hospedaje
                                </h3>
                                <div id="hoteles-container" class="space-y-4">
                                    <div class="hotel-item relative bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                                        <label class="block text-xs text-gray-500 mb-1 font-medium">Nombre del Hotel</label>
                                        <input type="text" name="hoteles[0][nombre]"
                                            class="geocoder-hotel-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm"
                                            placeholder="Buscar hotel..."
                                            id="hotel-nombre-0"
                                            data-lat-input-id="hotel-lat-0"
                                            data-lng-input-id="hotel-lng-0"
                                            data-display-lat-id="display-hotel-lat-0"
                                            data-display-lng-id="display-hotel-lng-0"
                                            autocomplete="off">
                                        <div class="mt-2 flex justify-between items-center">
                                            <div class="text-[10px] text-gray-400 font-mono">
                                                Lat: <span id="display-hotel-lat-0">-</span> | Lng: <span id="display-hotel-lng-0">-</span>
                                            </div>
                                        </div>
                                        <input type="hidden" name="hoteles[0][latitud]" id="hotel-lat-0" value="">
                                        <input type="hidden" name="hoteles[0][longitud]" id="hotel-lng-0" value="">
                                    </div>
                                </div>
                                <button type="button" onclick="agregarHotel()"
                                    class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Añadir Hotel
                                </button>
                            </div>

                            <!-- Vuelo Llegada -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                                    Vuelo de Llegada
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Fecha</label>
                                        <input type="date" name="vuelo_llegada[fecha]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Flight #</label>
                                            <input type="text" name="vuelo_llegada[flight]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm uppercase">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Hora</label>
                                            <input type="time" name="vuelo_llegada[hora]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Aeropuertos y Vuelo Salida -->
                        <div class="space-y-6">
                            <!-- Aeropuertos -->
                            <div class="bg-sky-50/50 dark:bg-sky-900/10 rounded-2xl p-6 border border-sky-100 dark:border-sky-800/30">
                                <h3 class="text-sm font-bold text-sky-900 dark:text-sky-300 mb-4 uppercase tracking-wide flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Aeropuertos
                                </h3>
                                <div id="aeropuertos-container" class="space-y-4">
                                    <div class="aeropuerto-item relative bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                                        <label class="block text-xs text-gray-500 mb-1 font-medium">Nombre del Aeropuerto</label>
                                        <input type="text" name="aeropuertos[0][nombre]"
                                            class="geocoder-airport-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 py-2 text-sm"
                                            placeholder="Buscar aeropuerto..."
                                            id="aeropuerto-nombre-0"
                                            data-lat-input-id="aeropuerto-lat-0"
                                            data-lng-input-id="aeropuerto-lng-0"
                                            data-display-lat-id="display-aeropuerto-lat-0"
                                            data-display-lng-id="display-aeropuerto-lng-0"
                                            autocomplete="off">
                                        <div class="mt-2 flex justify-between items-center">
                                            <div class="text-[10px] text-gray-400 font-mono">
                                                Lat: <span id="display-aeropuerto-lat-0">-</span> | Lng: <span id="display-aeropuerto-lng-0">-</span>
                                            </div>
                                        </div>
                                        <input type="hidden" name="aeropuertos[0][latitud]" id="aeropuerto-lat-0" value="">
                                        <input type="hidden" name="aeropuertos[0][longitud]" id="aeropuerto-lng-0" value="">
                                    </div>
                                </div>
                                <button type="button" onclick="agregarAeropuerto()"
                                    class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium rounded-lg text-sky-700 bg-sky-100 hover:bg-sky-200 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Añadir Aeropuerto
                                </button>
                            </div>

                            <!-- Vuelo Salida -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                                    Vuelo de Salida
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Fecha</label>
                                        <input type="date" name="vuelo_salida[fecha]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Flight #</label>
                                            <input type="text" name="vuelo_salida[flight]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm uppercase">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Hora</label>
                                            <input type="time" name="vuelo_salida[hora]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                {{-- FOOTER: ACCIONES --}}
                <div class="pt-8 mt-8 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row items-center justify-end gap-4">
                    <button type="submit"
                            class="w-full sm:w-auto px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Registrar Misión
                    </button>
                    <a href="{{ route('dashboard') }}"
                       class="w-full sm:w-auto px-8 py-3.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPTS ORIGINALES INTACTOS --}}
    <script>
        let ubicacionIndex = 1;
        let hotelIndex = 1;
        let aeropuertoIndex = 1;

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
                    const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=5&addressdetails=1&countrycodes=MX,US`;
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            const filtered = data.filter(filterFn);
                            showSuggestions(filtered, inputElement, latInputId, lngInputId, displayLatId, displayLngId);
                        })
                        .catch(console.error);
                }, 300);
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
                container.className = 'absolute z-50 bg-white border border-gray-200 rounded-xl shadow-xl mt-1 max-h-60 overflow-y-auto w-full text-left';
                suggestions.forEach(s => {
                    const li = document.createElement('li');
                    li.className = 'p-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-0 transition-colors';
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
            div.className = 'ubicacion-item group relative bg-gray-50 dark:bg-gray-700/30 p-6 rounded-2xl border border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all shadow-sm';
            div.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Ubicación Adicional</label>
                    <button type="button" onclick="removeUbicacion(this)" class="text-red-400 hover:text-red-600 transition-colors p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
                <div class="relative mb-2">
                    <input type="text" name="ubicaciones[${ubicacionIndex}][direccion]"
                           class="geocoder-input block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-white dark:text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 pl-4 pr-10 text-base"
                           placeholder="Escribe para buscar..."
                           id="direccion-${ubicacionIndex}"
                           data-lat-input-id="latitud-${ubicacionIndex}"
                           data-lng-input-id="longitud-${ubicacionIndex}"
                           data-display-lat-id="display-latitud-${ubicacionIndex}"
                           data-display-lng-id="display-longitud-${ubicacionIndex}"
                           autocomplete="off">
                     <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 font-mono bg-white dark:bg-gray-800 px-3 py-2 rounded-lg inline-block border border-gray-200 dark:border-gray-600 shadow-sm">
                    <span>Lat: <span id="display-latitud-${ubicacionIndex}" class="font-bold text-gray-700 dark:text-gray-300">-</span></span>
                    <span class="w-px h-3 bg-gray-300"></span>
                    <span>Lng: <span id="display-longitud-${ubicacionIndex}" class="font-bold text-gray-700 dark:text-gray-300">-</span></span>
                </div>
                <input type="hidden" name="ubicaciones[${ubicacionIndex}][latitud]" id="latitud-${ubicacionIndex}" value="">
                <input type="hidden" name="ubicaciones[${ubicacionIndex}][longitud]" id="longitud-${ubicacionIndex}" value="">
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
             btn.closest('.ubicacion-item').remove();
        }

        function agregarHotel() {
            const container = document.getElementById('hoteles-container');
            const div = document.createElement('div');
            div.className = 'hotel-item relative bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700';
            div.innerHTML = `
                <button type="button" onclick="eliminarHotel(this)"
                    class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition-colors p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Nombre del Hotel</label>
                <input type="text" name="hoteles[${hotelIndex}][nombre]"
                    class="geocoder-hotel-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm"
                    placeholder="Buscar hotel..."
                    id="hotel-nombre-${hotelIndex}"
                    data-lat-input-id="hotel-lat-${hotelIndex}"
                    data-lng-input-id="hotel-lng-${hotelIndex}"
                    data-display-lat-id="display-hotel-lat-${hotelIndex}"
                    data-display-lng-id="display-hotel-lng-${hotelIndex}"
                    autocomplete="off">
                <div class="mt-2 flex justify-between items-center">
                    <div class="text-[10px] text-gray-400 font-mono">
                        Lat: <span id="display-hotel-lat-${hotelIndex}">-</span> | Lng: <span id="display-hotel-lng-${hotelIndex}">-</span>
                    </div>
                </div>
                <input type="hidden" name="hoteles[${hotelIndex}][latitud]" id="hotel-lat-${hotelIndex}" value="">
                <input type="hidden" name="hoteles[${hotelIndex}][longitud]" id="hotel-lng-${hotelIndex}" value="">
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
            btn.closest('.hotel-item').remove();
        }

        function agregarAeropuerto() {
            const container = document.getElementById('aeropuertos-container');
            const div = document.createElement('div');
            div.className = 'aeropuerto-item relative bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700';
            div.innerHTML = `
                <button type="button" onclick="eliminarAeropuerto(this)"
                    class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition-colors p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Nombre del Aeropuerto</label>
                <input type="text" name="aeropuertos[${aeropuertoIndex}][nombre]"
                    class="geocoder-airport-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-50 dark:text-gray-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 py-2 text-sm"
                    placeholder="Buscar aeropuerto..."
                    id="aeropuerto-nombre-${aeropuertoIndex}"
                    data-lat-input-id="aeropuerto-lat-${aeropuertoIndex}"
                    data-lng-input-id="aeropuerto-lng-${aeropuertoIndex}"
                    data-display-lat-id="display-aeropuerto-lat-${aeropuertoIndex}"
                    data-display-lng-id="display-aeropuerto-lng-${aeropuertoIndex}"
                    autocomplete="off">
                <div class="mt-2 flex justify-between items-center">
                    <div class="text-[10px] text-gray-400 font-mono">
                        Lat: <span id="display-aeropuerto-lat-${aeropuertoIndex}">-</span> | Lng: <span id="display-aeropuerto-lng-${aeropuertoIndex}">-</span>
                    </div>
                </div>
                <input type="hidden" name="aeropuertos[${aeropuertoIndex}][latitud]" id="aeropuerto-lat-${aeropuertoIndex}" value="">
                <input type="hidden" name="aeropuertos[${aeropuertoIndex}][longitud]" id="aeropuerto-lng-${aeropuertoIndex}" value="">
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
            btn.closest('.aeropuerto-item').remove();
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
                body: JSON.stringify({ fecha_inicio: inicio, fecha_fin: fin })
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
