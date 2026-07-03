<x-app-layout>
    <x-navbar />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- HEADER DE NAVEGACIÓN Y TÍTULO --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <nav class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-2">
                        <a href="{{ route('admin.monitoreoDashboard') }}" class="hover:text-blue-600 transition-colors">Monitoreo</a>
                        <svg class="h-4 w-4 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <span class="text-gray-900 dark:text-white font-medium">Detalle de Misión</span>
                    </nav>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 7m0 13V7" />
                            </svg>
                        </span>
                        {{ $mision->nombre_clave ?? 'Misión #' . $mision->id }}
                    </h1>
                </div>

                <a href="{{ route('admin.mapaGeocercas') }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Regresar al Mapa General
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- COLUMNA IZQUIERDA: DETALLES DE LA MISIÓN --}}
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white">Información General</h2>
                            <span class="px-3 py-1 text-xs font-bold text-white bg-blue-600 rounded-full shadow-sm">
                                ID: {{ $mision->id }}
                            </span>
                        </div>

                        <div class="p-6 space-y-6">
                            {{-- Estatus --}}
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Estatus actual</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \App\Support\Custodios\MissionStatus::tone($mision->estatus) }}">
                                        {{ $mision->estado_normalizado }}
                                    </span>
                                </div>

                                @if(count($mision->transiciones_estado) > 0)
                                    <form method="POST" action="{{ route('misiones.estado.update', $mision) }}" class="flex flex-col sm:flex-row gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label for="estatus" class="sr-only">Nuevo estado</label>
                                        <select id="estatus" name="estatus" required
                                                class="flex-1 rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                            <option value="" selected disabled>Seleccionar nuevo estado</option>
                                            @foreach($mision->transiciones_estado as $estado)
                                                <option value="{{ $estado }}">{{ $estado }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                            Actualizar
                                        </button>
                                    </form>
                                @else
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Este estado es terminal y no admite más cambios.</p>
                                @endif

                                @error('estatus')
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Datos Principales --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Cliente</label>
                                    <p class="text-base font-medium text-gray-900 dark:text-white">{{ $mision->cliente ?? 'No definido' }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Inicio</label>
                                        <p class="text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-900/50 px-2 py-1 rounded border border-gray-100 dark:border-gray-700 inline-block">
                                            {{ $mision->fecha_inicio ? \Carbon\Carbon::parse($mision->fecha_inicio)->format('d/m/Y') : '-' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Fin</label>
                                        <p class="text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-900/50 px-2 py-1 rounded border border-gray-100 dark:border-gray-700 inline-block">
                                            {{ $mision->fecha_fin ? \Carbon\Carbon::parse($mision->fecha_fin)->format('d/m/Y') : '-' }}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tipo de Servicio</label>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $mision->tipo_servicio ?? 'No definido' }}</p>
                                </div>
                            </div>

                            {{-- Sección Dinámica: Hoteles --}}
                            @if ($mision->datos_hotel)
                                @php $hoteles = json_decode($mision->datos_hotel, true); @endphp
                                @if (is_array($hoteles) && count($hoteles) > 0)
                                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                        <h3 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            Hospedaje
                                        </h3>
                                        <ul class="space-y-2">
                                            @foreach ($hoteles as $hotel)
                                                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 bg-indigo-50/50 dark:bg-indigo-900/10 p-2 rounded-lg">
                                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-indigo-400 flex-shrink-0"></span>
                                                    <span>{{ $hotel['nombre'] ?? 'Nombre no definido' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endif

                            {{-- Sección Dinámica: Aeropuertos --}}
                            @if ($mision->datos_aeropuerto)
                                @php
                                    $aeropuertos = json_decode($mision->datos_aeropuerto, true);
                                    $aeropuertosSimplificados = [];
                                    if (is_array($aeropuertos)) {
                                        foreach ($aeropuertos as $aeropuerto) {
                                            $nombre_completo = $aeropuerto['nombre'] ?? '';
                                            if ($nombre_completo) {
                                                $partes = explode(',', $nombre_completo);
                                                $nombre_simplificado = trim($partes[0]);
                                                $municipio = '';
                                                foreach ($partes as $parte) {
                                                    $parte = trim($parte);
                                                    if (str_starts_with($parte, 'Municipio de ')) {
                                                        $municipio = $parte;
                                                        break;
                                                    }
                                                }
                                                $nombre_final = $nombre_simplificado;
                                                if ($municipio) $nombre_final .= ', ' . $municipio;
                                                $aeropuertosSimplificados[] = $nombre_final;
                                            }
                                        }
                                    }
                                @endphp
                                @if (count($aeropuertosSimplificados) > 0)
                                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                        <h3 class="text-xs font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                            Aeropuertos
                                        </h3>
                                        <ul class="space-y-2">
                                            @foreach ($aeropuertosSimplificados as $aeropuerto_simplificado)
                                                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 bg-sky-50/50 dark:bg-sky-900/10 p-2 rounded-lg">
                                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-sky-400 flex-shrink-0"></span>
                                                    <span>{{ $aeropuerto_simplificado }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endif

                            {{-- Agentes Asignados --}}
                            @if ($agentes && count($agentes) > 0)
                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Equipo Asignado
                                    </h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($agentes as $agente)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                                                {{ $agente->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: MAPA --}}
                <div class="lg:col-span-8">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden h-full flex flex-col">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Geocercas Operativas
                            </h2>
                            <button id="btn-centrar-mapa-detalle"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                                <i class="ti ti-map-pin-2 mr-1.5"></i>
                                Centrar Vista
                            </button>
                        </div>

                        <div class="relative flex-1 p-4 bg-gray-100 dark:bg-gray-900">
                            <div id="mapaDetalleContainer" class="w-full h-[500px] rounded-xl shadow-inner border border-gray-200 dark:border-gray-700 z-0">
                                <!-- El mapa se renderiza aquí -->
                            </div>
                            <div id="mapaDetalleEstado" class="absolute bottom-6 left-6 z-[1000] bg-white/90 dark:bg-gray-800/90 backdrop-blur px-3 py-1 rounded-md text-xs font-medium text-gray-500 shadow-sm border border-gray-200 dark:border-gray-700">
                                Cargando geocercas...
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            #mapaDetalleContainer {
                height: 100%;
                min-height: 400px;
                width: 100%;
                z-index: 1;
            }
            .leaflet-container {
                height: 100%;
                width: 100%;
                border-radius: 0.75rem; /* rounded-xl */
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            let mapaDetalle, grupoGeocercasDetalle, grupoAgentesDetalle;
            const marcadoresAgentesDetalle = {};
            const estadosGeocercasDetalle = {};
            const geocercasDetalleData = @json($geocercas->values());
            const posicionesAgentesDetalleData = @json($posicionesAgentes);
            const agentesAsignadosDetalle = new Set(@json($agentes->pluck('id')->values()));
            const misionDetalleActiva = @json($misionActiva);
            const nombreMisionDetalle = @json($mision->nombre_clave ?? 'Misión #' . $mision->id);
            const estadoMapaDetalle = {
                inicializado: false
            };

            function formatearNombreGeocercaDetalle(nombreCompleto) {
                if (typeof nombreCompleto !== 'string' || !nombreCompleto.trim()) {
                    return 'Geocerca de la misión';
                }

                const partes = nombreCompleto
                    .split(',')
                    .map(parte => parte.trim())
                    .filter(Boolean);
                const nombreLugar = partes[0];
                const municipio = partes.find(parte => parte.toLowerCase().startsWith('municipio de '));
                const ciudad = municipio
                    ? municipio.replace(/^municipio de\s+/i, '').trim()
                    : null;

                return ciudad ? `${nombreLugar}, ${ciudad}` : nombreLugar;
            }

            function crearGeocercaDetalle(centro, radioKm, tipo, nombre) {
                const lat = parseFloat(centro.lat);
                const lng = parseFloat(centro.lng);
                const radioMetros = radioKm * 1000;

                let color = '#3b82f6'; // blue-500
                let fillColor = '#93c5fd'; // blue-300

                if (tipo === 'hotel') {
                    color = '#10b981'; // emerald-500
                    fillColor = '#6ee7b7'; // emerald-300
                } else if (tipo === 'aeropuerto') {
                    color = '#f59e0b'; // amber-500
                    fillColor = '#fcd34d'; // amber-300
                }

                const circulo = L.circle([lat, lng], {
                    radius: radioMetros,
                    color: color,
                    fillColor: fillColor,
                    fillOpacity: 0.25,
                    weight: 2
                });

                // Popup estilizado
                const popupContent = `
                    <div class="p-1 min-w-[150px]">
                        <h3 class="font-bold text-gray-800 text-sm mb-1">${escaparHtmlDetalle(formatearNombreGeocercaDetalle(nombre))}</h3>
                        <div class="text-xs text-gray-600 space-y-1">
                            <p><span class="font-semibold">Tipo:</span> ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</p>
                            <p><span class="font-semibold">Radio:</span> ${radioKm} km</p>
                        </div>
                    </div>
                `;
                circulo.bindPopup(popupContent);
                return circulo;
            }

            function escaparHtmlDetalle(valor) {
                const elemento = document.createElement('div');
                elemento.textContent = valor ?? '';
                return elemento.innerHTML;
            }

            function distanciaMetrosDetalle(lat1, lng1, lat2, lng2) {
                const radioTierra = 6371000;
                const aLat1 = lat1 * Math.PI / 180;
                const aLat2 = lat2 * Math.PI / 180;
                const deltaLat = (lat2 - lat1) * Math.PI / 180;
                const deltaLng = (lng2 - lng1) * Math.PI / 180;
                const a = Math.sin(deltaLat / 2) ** 2
                    + Math.cos(aLat1) * Math.cos(aLat2) * Math.sin(deltaLng / 2) ** 2;

                return radioTierra * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            }

            function crearIconoAgenteDetalle(nombre) {
                const inicial = (nombre || 'A').charAt(0).toUpperCase();
                return L.divIcon({
                    className: 'custom-escolta-icon',
                    html: `<div style="
                        background-color: #22c55e;
                        color: white;
                        border: 2px solid #111827;
                        border-radius: 50%;
                        width: 32px;
                        height: 32px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        box-shadow: 0 0 6px rgba(0,0,0,0.45);
                    ">${escaparHtmlDetalle(inicial)}</div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
            }

            function actualizarMarcadorAgenteDetalle(posicion) {
                const userId = Number(posicion.user_id);
                const lat = parseFloat(posicion.latitude);
                const lng = parseFloat(posicion.longitude);
                const nombre = posicion.user?.name || 'Agente';

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                const contenidoPopup = `
                    <div class="p-1 min-w-[150px]">
                        <h3 class="font-bold text-gray-800 text-sm mb-1">${escaparHtmlDetalle(nombre)}</h3>
                        <p class="text-xs text-gray-600">Última ubicación: ${escaparHtmlDetalle(posicion.recorded_at || '')}</p>
                    </div>
                `;

                if (marcadoresAgentesDetalle[userId]) {
                    marcadoresAgentesDetalle[userId]
                        .setLatLng([lat, lng])
                        .setPopupContent(contenidoPopup);
                    return;
                }

                const marker = L.marker([lat, lng], {
                    icon: crearIconoAgenteDetalle(nombre)
                }).bindPopup(contenidoPopup);

                grupoAgentesDetalle.addLayer(marker);
                marcadoresAgentesDetalle[userId] = marker;
            }

            function mostrarToastIngresoDetalle(posicion, geocerca) {
                const rolesPermitidos = [
                    'CUSTODIOS',
                    'AUXILIAR MONITORISTA',
                    'ADMIN',
                    'ADMINISTRADOR',
                    'JEFE'
                ];
                if (!rolesPermitidos.includes(window.userRoleUpper || '')) return;
                if (typeof window.Swal === 'undefined') return;

                const nombreAgente = posicion.user?.name || 'Un agente';
                const nombreGeocercaFormateado = formatearNombreGeocercaDetalle(geocerca.nombre_referencia);
                window.Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `${nombreAgente} ingresó a ${nombreGeocercaFormateado}`,
                    text: nombreMisionDetalle,
                    showConfirmButton: false,
                    timer: 6000,
                    timerProgressBar: true
                });
            }

            function procesarPosicionAgenteDetalle(posicion, permitirNotificacion = true) {
                const userId = Number(posicion?.user_id);
                if (!agentesAsignadosDetalle.has(userId)) return;

                const lat = parseFloat(posicion.latitude);
                const lng = parseFloat(posicion.longitude);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                actualizarMarcadorAgenteDetalle(posicion);

                geocercasDetalleData.forEach(geocerca => {
                    const centroLat = parseFloat(geocerca.centro?.lat);
                    const centroLng = parseFloat(geocerca.centro?.lng);
                    const radioMetros = parseFloat(geocerca.radio_km) * 1000;
                    if (!Number.isFinite(centroLat) || !Number.isFinite(centroLng) || !Number.isFinite(radioMetros)) {
                        return;
                    }

                    const distancia = distanciaMetrosDetalle(lat, lng, centroLat, centroLng);
                    const claveEstado = `${userId}:${geocerca.id}`;
                    const estadoAnterior = estadosGeocercasDetalle[claveEstado];
                    const estaDentro = distancia <= radioMetros;
                    const margenSalida = Math.min(200, Math.max(50, radioMetros * 0.05));

                    if (estadoAnterior === undefined) {
                        estadosGeocercasDetalle[claveEstado] = estaDentro;
                        return;
                    }

                    if (!estadoAnterior && estaDentro) {
                        estadosGeocercasDetalle[claveEstado] = true;
                        if (permitirNotificacion) {
                            mostrarToastIngresoDetalle(posicion, geocerca);
                        }
                    } else if (estadoAnterior && distancia > radioMetros + margenSalida) {
                        estadosGeocercasDetalle[claveEstado] = false;
                    }
                });
            }

            function escucharPosicionesMisionDetalle() {
                if (!misionDetalleActiva || typeof window.Echo === 'undefined') return;

                window.Echo.channel('realtime-positions.all')
                    .listen('.NuevaUbicacionRealtime', event => {
                        procesarPosicionAgenteDetalle(event?.position, false);
                    });
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
                grupoAgentesDetalle = L.layerGroup().addTo(mapaDetalle);
                estadoMapaDetalle.inicializado = true;

                cargarGeocercasDetalle(geocercasDetalleData);
                posicionesAgentesDetalleData.forEach(posicion => {
                    procesarPosicionAgenteDetalle(posicion, false);
                });

                document.getElementById('mapaDetalleEstado').textContent = misionDetalleActiva
                    ? 'Seguimiento en tiempo real activo'
                    : 'Misión fuera de vigencia';
                setTimeout(() => {
                    document.getElementById('mapaDetalleEstado').classList.add('opacity-0', 'transition-opacity', 'duration-500');
                }, 3000);
            }

            function cargarGeocercasDetalle(geocercasData) {
                if (!grupoGeocercasDetalle || !Array.isArray(geocercasData)) return;

                grupoGeocercasDetalle.clearLayers();

                geocercasData.forEach(geofence => {
                    const layer = crearGeocercaDetalle(geofence.centro, geofence.radio_km, geofence.tipo, geofence.nombre_referencia);
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
                        const primera = geocercasData[0];
                        if (primera && primera.centro) {
                            mapaDetalle.setView([parseFloat(primera.centro.lat), parseFloat(primera.centro.lng)], 13);
                        }
                    }
                }
            }

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

            document.addEventListener('DOMContentLoaded', function () {
                inicializarMapaDetalle();
                escucharPosicionesMisionDetalle();
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('#btn-centrar-mapa-detalle')) {
                    centrarVistaDetalle();
                }
            });
        </script>
    @endpush
</x-app-layout>
