<x-app-layout>
    <x-navbar />
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="mx-auto max-w-7xl">
            <x-livewire.monitoreo-layout :breadcrumb-items="[
                ['icon' => 'ti-home', 'url' => route('admin.monitoreoDashboard')],
                ['icon' => 'ti-map', 'label' => 'Mapa de Monitoreo'],
                ['icon' => 'ti-map', 'label' => 'Detalle Misión: ' . ($mision->nombre_clave ?? $mision->id)],
            ]" title-main="Detalle de Misión"
                help-text="Información y geocercas de la misión seleccionada">
                <div class="mb-4">
                    <a href="{{ route('admin.mapaGeocercas') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Regresar a Misiones
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div
                        class="border border-gray-200 rounded-lg md:col-span-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                        <div class="p-4 border-b border-gray-300 dark:border-gray-600">
                            <div class="flex items-center justify-between w-full min-h-8">
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Detalles de la Misión
                                </h2>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span
                                        class="inline-block px-3 py-1 text-base font-semibold text-white bg-gray-800 rounded-full shadow dark:bg-white/80 dark:text-gray-900">
                                        {{ $mision->id }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre Clave:</h3>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $mision->nombre_clave ?? 'No definido' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Cliente:</h3>
                                    <p class="text-gray-900 dark:text-white">{{ $mision->cliente ?? 'No definido' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Inicio:</h3>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $mision->fecha_inicio ? \Carbon\Carbon::parse($mision->fecha_inicio)->format('d/m/Y') : 'No definida' }}
                                    </p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Fin:</h3>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $mision->fecha_fin ? \Carbon\Carbon::parse($mision->fecha_fin)->format('d/m/Y') : 'No definida' }}
                                    </p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Servicio:
                                    </h3>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $mision->tipo_servicio ?? 'No definido' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Estatus:</h3>
                                    <p class="text-gray-900 dark:text-white">{{ $mision->estatus ?? 'Desconocido' }}</p>
                                </div>
                                @if ($mision->datos_hotel)
                                    @php $hotel = json_decode($mision->datos_hotel, true); @endphp
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Hotel:</h3>
                                        <p class="text-gray-900 dark:text-white">{{ $hotel['nombre'] ?? 'No definido' }}
                                        </p>
                                    </div>
                                @endif
                                @if ($mision->datos_aeropuerto)
                                    @php $aeropuerto = json_decode($mision->datos_aeropuerto, true); @endphp
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Aeropuerto:
                                        </h3>
                                        <p class="text-gray-900 dark:text-white">
                                            {{ $aeropuerto['nombre'] ?? 'No definido' }}</p>
                                    </div>
                                @endif

                                @if ($agentes)
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Agentes
                                            Asignados:</h3>
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($agentes as $agente)
                                                <li class="text-gray-900 dark:text-white">{{ $agente->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div
                        class="border border-gray-200 rounded-lg md:col-span-2 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                        <div class="relative">
                            <div
                                class="flex flex-wrap items-center justify-between w-full p-4 border-b border-gray-300 dark:border-gray-600 min-h-8">
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Mapa de Geocercas</h2>
                                <button id="btn-centrar-mapa-detalle"
                                    class="p-1 mt-2 ml-0 md:mt-0 md:ml-4 bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400"
                                    title="Ajustar a geocercas">
                                    <i class="p-2 text-sm text-white ti ti-map-pin-2"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-4">
                            <div id="mapaDetalleContainer" class="w-full bg-gray-200 rounded h-96 dark:bg-gray-700">
                            </div>
                            <div id="mapaDetalleEstado" class="mt-2 text-xs text-gray-500">Cargando geocercas...</div>
                        </div>
                    </div>
                </div>

                @push('styles')
                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                    <style>
                        #mapaDetalleContainer {
                            height: 400px;
                            min-height: 400px;
                            width: 100%;
                        }

                        .leaflet-container {
                            height: 100%;
                            width: 100%;
                        }
                    </style>
                @endpush

                @push('scripts')
                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                    <script>
                        let mapaDetalle, grupoGeocercasDetalle;
                        const estadoMapaDetalle = {
                            inicializado: false
                        };

                        function crearGeocercaDetalle(centro, radioKm, tipo, nombre) {
                            const lat = parseFloat(centro.lat);
                            const lng = parseFloat(centro.lng);
                            const radioMetros = radioKm * 1000;

                            let color = 'blue';
                            let fillColor = 'lightblue';
                            if (tipo === 'hotel') {
                                color = 'green';
                                fillColor = 'lightgreen';
                            } else if (tipo === 'aeropuerto') {
                                color = 'orange';
                                fillColor = 'wheat';
                            }

                            const circulo = L.circle([lat, lng], {
                                radius: radioMetros,
                                color: color,
                                fillColor: fillColor,
                                fillOpacity: 0.2,
                                weight: 2
                            });

                            circulo.bindPopup(`<b>${nombre}</b><br>Tipo: ${tipo}<br>Radio: ${radioKm} km`);
                            return circulo;
                        }

                        function inicializarMapaDetalle() {
                            if (estadoMapaDetalle.inicializado) return;

                            const contenedor = document.getElementById('mapaDetalleContainer');
                            if (!contenedor) {
                                console.error('Contenedor del mapa de detalle no encontrado.');
                                return;
                            }

                            mapaDetalle = L.map(contenedor).setView([25.6866, -100.3161], 13);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© OpenStreetMap contributors'
                            }).addTo(mapaDetalle);

                            grupoGeocercasDetalle = L.layerGroup().addTo(mapaDetalle);
                            estadoMapaDetalle.inicializado = true;

                            const geocercasData = @json($geocercas);
                            cargarGeocercasDetalle(geocercasData);

                            document.getElementById('mapaDetalleEstado').textContent = 'Geocercas cargadas.';
                        }

                        function cargarGeocercasDetalle(geocercasData) {
                            if (!grupoGeocercasDetalle || !Array.isArray(geocercasData)) return;

                            grupoGeocercasDetalle.clearLayers();

                            geocercasData.forEach(geofence => {
                                const layer = crearGeocercaDetalle(geofence.centro, geofence.radio_km, geofence.tipo, geofence
                                    .nombre_referencia);
                                grupoGeocercasDetalle.addLayer(layer);
                            });

                            // Ajustar vista a las geocercas
                            if (grupoGeocercasDetalle.getLayers().length > 0) {
                                try {
                                    const tempGroup = L.featureGroup(grupoGeocercasDetalle.getLayers());
                                    mapaDetalle.fitBounds(tempGroup.getBounds(), {
                                        padding: [50, 50]
                                    });
                                } catch (e) {
                                    console.warn("No se pudo ajustar la vista a las geocercas:", e);
                                    // Opcional: centrar en la primera geocerca
                                    const primera = geocercasData[0];
                                    if (primera && primera.centro) {
                                        mapaDetalle.setView([parseFloat(primera.centro.lat), parseFloat(primera.centro.lng)], 13);
                                    }
                                }
                            }
                        }

                        // Centrar vista
                        function centrarVistaDetalle() {
                            if (!mapaDetalle || !grupoGeocercasDetalle) return;
                            const layers = grupoGeocercasDetalle.getLayers();
                            if (layers.length > 0) {
                                const tempGroup = L.featureGroup(layers);
                                mapaDetalle.fitBounds(tempGroup.getBounds(), {
                                    padding: [50, 50]
                                });
                            }
                        }

                        // Inicializar cuando el DOM esté listo
                        document.addEventListener('DOMContentLoaded', inicializarMapaDetalle);

                        // Listener para botón de centrar
                        document.addEventListener('click', function(e) {
                            if (e.target.closest('#btn-centrar-mapa-detalle')) {
                                centrarVistaDetalle();
                            }
                        });
                    </script>
                @endpush
            </x-livewire.monitoreo-layout>
        </div>
    </div>
</x-app-layout>
