<x-livewire.monitoreo-layout :breadcrumb-items="[
        ['icon' => 'ti-home', 'url' => route('admin.monitoreoDashboard')],
        ['icon' => 'ti-map', 'label' => 'Mapa de Geocercas']
    ]" title-main="Mapa de Geocercas" help-text="Visualización de geocercas por misión">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <!-- Panel de Misiones -->
        <div class="border border-gray-200 rounded-lg md:col-span-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
            <div class="p-4 border-b border-gray-300 dark:border-gray-600">
                <div class="flex items-center justify-between w-full min-h-8">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Misiones Recientes</h2>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span
                            class="inline-block px-3 py-1 text-base font-semibold text-white bg-gray-800 rounded-full shadow dark:bg-white/80 dark:text-gray-900">
                            {{ count($misionesRecientes) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <div class="space-y-3 max-h-[420px] overflow-y-auto overflow-x-hidden relative bg-slate-50 dark:bg-gray-800">
                    @if(count($misionesRecientes) > 0)
                        @foreach($misionesRecientes as $mision)
    <!-- Div principal ahora es un contenedor clickable via <a> -->
    <div class="p-3 transition-all duration-200 border-l-4 border-blue-500 bg-blue-50 dark:bg-blue-900/20 rounded hover:shadow-md hover:scale-[1.02] mision-card" wire:key="mision-{{ $mision->id }}">
        <!-- Enlace que cubre toda la tarjeta -->
        <a href="{{ route('admin.detalleMision', $mision->id) }}" class="block">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-gray-900 dark:text-white card-mision-name">{{ $mision->nombre_clave ?? $mision->id }}</p>
                        </div>
                        <p class="text-sm text-blue-700 dark:text-blue-300 font-medium">
                            <i class="mr-1 ti ti-map-pin"></i>
                            <span class="card-location">
                                Cliente: {{ $mision->cliente ?? 'N/A' }}
                            </span>
                        </p>
                        <p class="text-xs text-gray-500">
                            <i class="mr-1 ti ti-calendar"></i>
                            <span class="card-date">{{ $mision->created_at->format('d/m/y') }}</span>
                            <i class="mr-1 ti ti-clock"></i>
                            <span class="card-time">{{ $mision->created_at->format('h:i A') }}</span>
                        </p>
                        <!-- Mostrar tipos de geocercas disponibles -->
                        @php
                            $geofences = $mision->geofences; // Asumiendo relación
                            $tipos = $geofences->pluck('tipo')->unique()->join(', ');
                        @endphp
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                            <i class="mr-1 ti ti-shape"></i>
                            <span>Geocercas: {{ $tipos ?: 'Ninguna' }}</span>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block w-4 h-4 bg-blue-500 rounded-full"></span>
                    <p class="mt-1 text-xs font-bold text-blue-700 dark:text-blue-300">
                        {{ $mision->estatus ?? 'Desconocido' }}
                    </p>
                </div>
            </div>
        </a>
    </div>
@endforeach
                    @else
                        <div class="flex flex-col items-center justify-center p-5 rounded bg-gray-50 dark:bg-gray-700">
                            <div class="mb-3">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-600">
                                    <i class="ti ti-clock-question text-3xl text-gray-500 dark:text-gray-300"></i>
                                </span>
                            </div>
                            <p class="font-semibold text-gray-900 dark:text-white">No hay misiones
                                recientes</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                                Las misiones recientes aparecerán aquí.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- Mapa -->
        <div class="border border-gray-200 rounded-lg md:col-span-2 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
            <div class="relative">
                <div
                    class="flex flex-wrap items-center justify-between w-full p-4 border-b border-gray-300 dark:border-gray-600 min-h-8">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Vista del Mapa</h2>
                    <div class="flex flex-wrap items-center gap-4 mt-2 md:mt-0 text-xs">
                        <span class="font-medium text-gray-600 dark:text-gray-400">Misión Seleccionada:</span>
                        @if($misionSeleccionadaId)
                            <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $misionesRecientes->firstWhere('id', $misionSeleccionadaId)->nombre_clave ?? 'Misión ID: '.$misionSeleccionadaId }}</span>
                        @else
                            <span class="font-semibold text-gray-500 dark:text-gray-400">Ninguna</span>
                        @endif
                    </div>
                    <button id="btn-centrar-mapa"
                            class="p-1 mt-2 ml-0 md:mt-0 md:ml-4 bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400"
                            title="Ajustar a geocercas">
                        <i class="p-2 text-sm text-white ti ti-map-pin-2"></i>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <div id="mapaContainer" class="w-full bg-gray-200 rounded h-96 dark:bg-gray-700"></div>
                <div id="mapaEstado" class="mt-2 text-xs text-gray-500">Inicializando mapa...</div>
            </div>
        </div>
    </div>

    @push('styles')
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <!-- Tus estilos personalizados (reutilizados del mapa anterior) -->
        <style>
            #mapaContainer {
                height: 400px;
                min-height: 400px;
                width: 100%;
            }
            .leaflet-container {
                height: 100%;
                width: 100%;
            }
            /* Añade aquí otros estilos personalizados si los necesitas */
        </style>
    @endpush

    @push('scripts')
        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <!-- DayJS y plugins (si los usas para fechas en geocercas, aunque probablemente no aquí) -->
        <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/dayjs.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/relativeTime.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/utc.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/timezone.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/updateLocale.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/locale/es.min.js"></script>
        <script>
            // --- CONFIGURACIÓN INICIAL DAYJS (opcional para geocercas) ---
            dayjs.extend(window.dayjs_plugin_utc);
            dayjs.extend(window.dayjs_plugin_timezone);
            dayjs.extend(window.dayjs_plugin_updateLocale);
            dayjs.extend(window.dayjs_plugin_relativeTime);
            dayjs.locale('es');
            dayjs.updateLocale('es', {
                relativeTime: {
                    future: 'en %s',
                    past: 'hace %s',
                    s: 'un momento',
                    m: '1 min',
                    mm: '%d min',
                    h: '1 h',
                    hh: '%d hrs',
                    d: '1 día',
                    dd: '%d días',
                    M: '1 mes',
                    MM: '%d meses',
                    y: '1 año',
                    yy: '%d años'
                }
            });

            // --- VARIABLES GLOBALES DEL MAPA ---
            let mapa;
            let grupoGeocercas; // Grupo para manejar todas las geocercas
            let geocercasActuales = {}; // Almacenar geocercas por ID para poder removerlas
            // let geocercasActualesData = []; // Datos globales de geocercas actuales para centrar (Ya no es estrictamente necesario si usamos getLayers)

            const estadoMapa = {
                inicializado: false,
                cargando: false
            };

            // --- FUNCIONALIDAD PARA GEOCERCAS ---
            const crearGeocerca = (centro, radioKm, tipo, nombre) => {
                if (!centro || isNaN(parseFloat(centro.lat)) || isNaN(parseFloat(centro.lng))) {
                    console.error('Coordenadas inválidas:', centro);
                    return null; // No crea la capa
                }
                const lat = parseFloat(centro.lat);
                const lng = parseFloat(centro.lng);
                const radioMetros = radioKm * 1000; // Convertir km a metros

                // Definir un color base según el tipo
                let color = 'blue';
                let fillColor = 'lightblue';
                if (tipo === 'hotel') {
                    color = 'green';
                    fillColor = 'lightgreen';
                } else if (tipo === 'aeropuerto') {
                    color = 'orange';
                    fillColor = 'wheat';
                }

                // Crear un círculo
                const circulo = L.circle([lat, lng], {
                    radius: radioMetros, // Radio en metros
                    color: color,
                    fillColor: fillColor,
                    fillOpacity: 0.2,
                    weight: 2
                });

                // Opcional: Añadir un popup con información
                circulo.bindPopup(`<b>${nombre}</b><br>Tipo: ${tipo}<br>Radio: ${radioKm} km`);

                console.log('Creando geocerca para:', {nombre, tipo, centro, radioKm});
                return circulo;
            };

            const cargarGeocercas = (geocercasData) => {
                console.log("Cargando geocercas:", geocercasData);

                // Verificar que grupoGeocercas esté definido y sea un LayerGroup
                if (!grupoGeocercas || !(grupoGeocercas instanceof L.LayerGroup)) {
                    console.error("grupoGeocercas no está inicializado como un LayerGroup o es null/undefined.");
                    actualizarEstadoMapa('Error: Grupo de geocercas no disponible.');
                    return;
                }

                // Limpiar geocercas anteriores
                grupoGeocercas.clearLayers();
                geocercasActuales = {}; // Resetear el objeto de geocercas actuales
                // geocercasActualesData = []; // Resetear datos globales (ya no se usa si se calcula bounds dinámicamente)

                if (!geocercasData || geocercasData.length === 0) {
                    console.log("No hay geocercas para cargar.");
                    actualizarEstadoMapa('No hay geocercas para mostrar.');
                    return;
                }

                geocercasData.forEach(geofence => {
                    const layer = crearGeocerca(geofence.centro, geofence.radio_km, geofence.tipo, geofence.nombre_referencia);
                    if (layer) {
                        grupoGeocercas.addLayer(layer);
                        geocercasActuales[geofence.id] = layer;
                        console.log("Geocerca añadida al grupo:", geofence.nombre_referencia);
                    }
                });

                // Ajustar vista al grupo de geocercas
                if (grupoGeocercas.getLayers().length > 0) { // Usar getLayers() en lugar de getBounds()
                    try {
                        // Usar el mapa para ajustar los bounds, ya que LayerGroup no lo tiene directamente
                        // Necesitamos calcular los bounds manualmente o usar el mapa
                        const layers = grupoGeocercas.getLayers();
                        if (layers.length > 0) {
                            // Crear un FeatureGroup temporal para calcular bounds
                            const tempGroup = L.featureGroup(layers);
                            const bounds = tempGroup.getBounds();
                            if (bounds.isValid()) {
                                mapa.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });
                                actualizarEstadoMapa(`Cargadas ${grupoGeocercas.getLayers().length} geocercas.`);
                                console.log("Vista ajustada a las geocercas.");
                            } else {
                                console.warn("Bounds calculados no son válidos.");
                                 // Opcional: centrar en la primera geocerca
                                 const primeraGeofence = geocercasData[0];
                                 if (primeraGeofence && primeraGeofence.centro) {
                                     const lat = parseFloat(primeraGeofence.centro.lat);
                                     const lng = parseFloat(primeraGeofence.centro.lng);
                                     if (!isNaN(lat) && !isNaN(lng)) {
                                         mapa.setView([lat, lng], 13);
                                         console.log("Vista centrada en la primera geocerca.");
                                     }
                                 }
                            }
                        }
                    } catch (e) {
                        console.warn("No se pudo ajustar la vista a las geocercas:", e);
                        // Opcional: centrar en la primera geocerca
                        const primeraGeofence = geocercasData[0];
                        if (primeraGeofence && primeraGeofence.centro) {
                            const lat = parseFloat(primeraGeofence.centro.lat);
                            const lng = parseFloat(primeraGeofence.centro.lng);
                            if (!isNaN(lat) && !isNaN(lng)) {
                                mapa.setView([lat, lng], 13);
                                console.log("Vista centrada en la primera geocerca (fallback).");
                            }
                        }
                    }
                } else {
                     console.warn("No se añadió ninguna geocerca válida al grupo.");
                     actualizarEstadoMapa('No se pudieron cargar las geocercas válidas.');
                }
            };

            const limpiarGeocercas = () => {
                console.log("Limpiando geocercas del mapa.");
                if (grupoGeocercas) {
                    grupoGeocercas.clearLayers();
                    geocercasActuales = {};
                    // geocercasActualesData = []; // (ya no se usa si se calcula bounds dinámicamente)
                }
                actualizarEstadoMapa('Geocercas limpiadas.');
            };

            // --- FUNCIONALIDAD DEL MAPA ---
            const actualizarEstadoMapa = (mensaje) => {
                const el = document.getElementById('mapaEstado');
                if (el) {
                    el.className = 'mt-2 text-xs'; // Resetear clases
                    if (mensaje.toLowerCase().includes('error')) {
                        el.classList.add('text-red-500', 'font-bold');
                    } else if (mensaje.toLowerCase().includes('cargando') || mensaje.toLowerCase().includes('inicializando')) {
                        el.classList.add('text-blue-500');
                    } else {
                        el.classList.add('text-gray-500');
                    }
                    el.textContent = mensaje;
                }
            };

            // Función para inicializar el mapa
            const inicializarMapa = () => {
                if (mapa) {
                    console.log('Mapa ya existe, reutilizando.');
                    mapa.invalidateSize(); // Refresca tamaño por si DOM cambió
                    return Promise.resolve();
                }

                // Resetear banderas de estado
                estadoMapa.inicializado = false;
                estadoMapa.cargando = false;

                try {
                    const contenedor = document.getElementById('mapaContainer');
                    if (!contenedor) {
                        console.error('❌ Contenedor del mapa (#mapaContainer) no encontrado en inicializarMapa');
                        actualizarEstadoMapa('Error: Contenedor del mapa no encontrado.');
                        return Promise.resolve();
                    }
                    // Limpiar explícitamente el contenedor antes de crear el mapa
                    contenedor.innerHTML = ''; // Vaciar completamente
                    // Reafirmar estilos básicos si es necesario
                    contenedor.style.height = '400px';
                    contenedor.style.minHeight = '400px';
                    contenedor.style.width = '100%';
                    // Crear el nuevo mapa
                    mapa = L.map(contenedor, {
                        center: [25.6866, -100.3161], // Coordenadas de Monterrey
                        zoom: 13,
                        zoomControl: true,
                        attributionControl: true
                    });
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 18
                    }).addTo(mapa);
                    // Crear o reutilizar grupoGeocercas
                    if (!grupoGeocercas) {
                        grupoGeocercas = L.layerGroup(); // Grupo para geocercas
                    }
                    // Asegurarse de que el grupo esté añadido al mapa
                    if (!mapa.hasLayer(grupoGeocercas)) {
                        grupoGeocercas.addTo(mapa);
                        console.log("grupoGeocercas añadido al mapa.");
                    }

                    estadoMapa.inicializado = true;
                    // Forzar una actualización del tamaño del mapa
                    setTimeout(() => {
                        if (mapa) {
                            try {
                                mapa.invalidateSize();
                            } catch (e) {
                                console.warn("No se pudo invalidar el tamaño del mapa:", e);
                            }
                        }
                    }, 50);
                    console.log('✅ Mapa inicializado desde cero en inicializarMapa');
                    actualizarEstadoMapa('Mapa listo');
                    return Promise.resolve();
                } catch (error) {
                    console.error('❌ Error crítico al inicializar mapa desde cero en inicializarMapa:', error);
                    actualizarEstadoMapa(`Error de inicialización: ${error.message}`);
                    estadoMapa.inicializado = false;
                    mapa = undefined;
                    return Promise.resolve();
                }
            };

            // Centrar vista del mapa en las geocercas
            const centrarVistaMapa = () => {
                if (!mapa || !grupoGeocercas) {
                    console.warn("Mapa o grupo de geocercas no disponible para centrar.");
                    return;
                }
                try {
                    const layers = grupoGeocercas.getLayers();
                    if (layers.length > 0) {
                        const tempGroup = L.featureGroup(layers);
                        const bounds = tempGroup.getBounds();
                        if (bounds.isValid()) {
                            mapa.fitBounds(bounds, { padding: [50, 50] });
                        } else {
                            // Si no hay bounds válidos pero hay geocercas, centrar en la primera
                            const primeraGeofence = Object.values(geocercasActuales)[0]; // Usar geocercasActuales en lugar de geocercasActualesData
                            if (primeraGeofence) {
                                const latlng = primeraGeofence.getLatLng();
                                mapa.setView([latlng.lat, latlng.lng], 13);
                            }
                        }
                    }
                    console.log("Vista del mapa centrada en geocercas.");
                } catch (e) {
                    console.error("Error al centrar la vista del mapa en geocercas:", e);
                }
            };

            // Inicializar sistema completo
            const inicializarSistema = async () => {
                if (typeof L === 'undefined') {
                    console.warn("Leaflet no cargado, reintentando...");
                    setTimeout(inicializarSistema, 100);
                    return Promise.resolve();
                }
                await inicializarMapa();
                console.log("✅ inicializarSistema completado (mapa de geocercas)");
            };

            // --- LISTENERS ---
            document.addEventListener('DOMContentLoaded', () => {
                inicializarSistema();
            });

            // Listener para actualizaciones de geocercas desde Livewire
            window.addEventListener('geocercasActualizadas', (event) => {
                console.log("🔔 Evento 'geocercasActualizadas' recibido:", event.detail);
                const nuevasGeocercas = event.detail && event.detail.geocercas ? event.detail.geocercas : [];
                // Validar que se recibieron datos
                if (!Array.isArray(nuevasGeocercas)) {
                    console.error("❌ Los datos recibidos en 'geocercasActualizadas' no son un array válido:", nuevasGeocercas);
                    actualizarEstadoMapa('Error: Datos de geocercas recibidos inválidos.');
                    return;
                }

                if (mapa && estadoMapa.inicializado) {
                    console.log("Mapa inicializado, cargando nuevas geocercas...");
                    cargarGeocercas(nuevasGeocercas);
                } else {
                    console.log("Mapa no inicializado en 'geocercasActualizadas', iniciando sistema...");
                    inicializarSistema().then(() => {
                        console.log("Sistema iniciado, cargando geocercas...");
                        cargarGeocercas(nuevasGeocercas);
                    });
                }
            });

            // Listener para actualizaciones de Livewire (cambio de misión)
            document.addEventListener('livewire:updated', () => {
                console.log("🔔 Evento 'livewire:updated' recibido en mapa geocercas (puede ser redundante).");
            });

            // Listener para botón de centrar mapa
            document.addEventListener('click', function(e) {
                if (e.target.closest('#btn-centrar-mapa')) {
                    centrarVistaMapa();
                }
            });

        </script>
    @endpush
</x-livewire.monitoreo-layout>
