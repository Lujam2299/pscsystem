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
                <div class="p-4 border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-3 rounded shadow text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Usuarios Activos</p>
                    <p id="contador-activos" class="text-xl font-bold text-green-500">0</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-3 rounded shadow text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Usuarios Inactivos</p>
                    <p id="contador-inactivos" class="text-xl font-bold text-red-500">0</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-3 rounded shadow text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Sin Reportar (10 min)</p>
                    <p id="contador-sin-reporte" class="text-xl font-bold text-yellow-500">0</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-3 rounded shadow text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Total Escoltas</p>
                    <p id="contador-total" class="text-xl font-bold text-blue-500">0</p>
                </div>
            </div>
        </div>
                <div
                    class="flex flex-wrap items-center justify-between w-full p-4 border-b border-gray-300 dark:border-gray-600 min-h-8">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Vista del Mapa</h2>
                    <div class="relative p-4 border-b border-gray-300 dark:border-gray-600">
                        <input type="text" id="buscador-usuarios" placeholder="Buscar usuario..."
                            class="w-full p-2 border border-gray-300 rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
                    </div>
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
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css    " />
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js  " crossorigin=""></script>
<!-- DayJS -->
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/dayjs.min.js  "></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/relativeTime.min.js  "></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/utc.min.js  "></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/timezone.min.js  "></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/updateLocale.min.js  "></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/locale/es.min.js  "></script>

<script>
// --- CONFIGURACIÓN DAYJS ---
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
let grupoGeocercas;
let grupoUsuariosEscolta;
let grupoRutasHistorial; // ✅ Nuevo grupo para rutas de historial
let marcadoresEscolta = {};
let geocercasActuales = {};
let todosLosUsuarios = [];

const estadoMapa = {
    inicializado: false
};

// --- FUNCIONES DE MARCADORES ---
const crearMarcadorEscolta = (user) => {
    const lat = parseFloat(user.latitude);
    const lng = parseFloat(user.longitude);
    if (isNaN(lat) || isNaN(lng)) {
        console.error('Coordenadas inválidas al crear marcador:', user);
        return null;
    }

    // Ícono con fondo verde y borde negro
    const escoltaIcon = L.divIcon({
        className: 'custom-escolta-icon',
        html: `<div style="
            background-color: #22c55e;
            color: white;
            border: 2px solid black;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 0 5px rgba(0,0,0,0.5);
            font-size: 14px;
        ">${user.name.charAt(0).toUpperCase()}</div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });

    const marker = L.marker([lat, lng], { icon: escoltaIcon });

    // Popup normal al hacer clic en el marcador
    marker.bindPopup(`
        <div>
            <strong>${user.name}</strong><br>
            <small>Rol: ${user.user_data?.rol || 'N/A'}</small><br>
            <small>Última actualización: ${dayjs(user.recorded_at).fromNow()}</small><br>
            <button onclick="mostrarHistorial(${user.id})" class="text-blue-500 underline text-sm mt-1">Ver historial</button>
        </div>
    `);

    return marker;
};

const mostrarHistorial = async (userId) => {
    console.log('Mostrando historial para usuario:', userId);

    try {
        const response = await fetch(`/api/realtime-position/user/${userId}/recent`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'include'
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Error al cargar historial');
        }

        const data = await response.json();
        console.log('Historial recibido:', data);

        // ✅ Limpiar rutas anteriores
        grupoRutasHistorial.clearLayers();

        // ✅ Dibujar ruta y marcadores si hay ubicaciones
        if (data.positions.length > 0) {
            const coords = data.positions
                .map(pos => [parseFloat(pos.latitude), parseFloat(pos.longitude)])
                .reverse(); // Más reciente al final

            // Dibujar línea de trayectoria
            const polyline = L.polyline(coords, {
                color: '#3b82f6', // Azul
                weight: 4,
                opacity: 0.7,
                smoothFactor: 1
            }).addTo(grupoRutasHistorial);

            // Dibujar marcadores pequeños en cada punto
            coords.forEach((coord, index) => {
                const markerSmall = L.circleMarker(coord, {
                    radius: 6, // Tamaño del círculo
                    color: '#1d4ed8', // Borde azul oscuro
                    fillColor: '#3b82f6', // Fondo azul
                    fillOpacity: 0.8,
                    weight: 1
                })
                .bindPopup(`
                    <div>
                        <strong>Punto ${index + 1}</strong><br>
                        ${dayjs(data.positions[data.positions.length - 1 - index].recorded_at).format('HH:mm:ss')}<br>
                        ${coord[0]}, ${coord[1]}
                    </div>
                `)
                .addTo(grupoRutasHistorial);
            });

            // Ajustar vista al trayecto
            mapa.fitBounds(polyline.getBounds(), { padding: [50, 50] });
        }

        // ✅ Contenido con scroll y filas clicables
        let historialHtml = `
            <div style="max-width: 400px;">
                <h3 class="font-bold mb-2">Historial de ${data.total} ubicaciones (últimas 24 hrs)</h3>
                <button onclick="limpiarRutaHistorial()" class="text-red-500 underline text-sm mb-2">Ocultar ruta</button>
                <div style="
                    max-height: 300px;
                    overflow-y: auto;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    padding: 8px;
                    background: white;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                ">
        `;

        if (data.positions.length === 0) {
            historialHtml += '<p>No hay registros recientes.</p>';
        } else {
            historialHtml += `
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="bg-gray-100">
                            <th>Fecha/Hora</th>
                            <th>Lat</th>
                            <th>Lng</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            data.positions.forEach(pos => {
                historialHtml += `
                    <tr onclick="centrarEnCoordenada(${pos.latitude}, ${pos.longitude})" style="cursor: pointer;" class="hover:bg-blue-50">
                        <td>${dayjs(pos.recorded_at).format('HH:mm:ss')}</td>
                        <td>${pos.latitude}</td>
                        <td>${pos.longitude}</td>
                    </tr>
                `;
            });
            historialHtml += '</tbody></table>';
        }

        historialHtml += '</div></div>';

        // Abrir popup
        const historialPopup = L.popup()
            .setLatLng([data.positions[0]?.latitude || 25.6866, data.positions[0]?.longitude || -100.3161])
            .setContent(historialHtml)
            .openOn(mapa);
    } catch (err) {
        console.error('Error al cargar historial:', err);
        alert('No se pudo cargar el historial: ' + err.message);
    }
};

// ✅ Función para centrar el mapa en una coordenada específica
const centrarEnCoordenada = (lat, lng) => {
    console.log('Centrando en coordenada:', lat, lng);

    // Centrar el mapa en la coordenada
    mapa.setView([lat, lng], 15); // Zoom nivel 15 para ver bien el detalle

    // Opcional: Añadir un marcador temporal que parpadee o destaque
    const tempMarker = L.circleMarker([lat, lng], {
        radius: 10,
        color: '#ef4444',    // Rojo
        fillColor: '#f87171', // Rojo claro
        fillOpacity: 0.9,
        weight: 2
    }).addTo(mapa);

    // Remover el marcador temporal después de 2 segundos
    setTimeout(() => {
        mapa.removeLayer(tempMarker);
    }, 2000);
};

// ✅ FUNCIÓN PARA LIMPIAR LA RUTA
const limpiarRutaHistorial = () => {
    console.log('Limpiando rutas de historial...');
    grupoRutasHistorial.clearLayers();
};

const actualizarOMarcarEscolta = (userId, name, nuevaLat, nuevaLng, nuevoRecordedAt, userData) => {
    const lat = parseFloat(nuevaLat);
    const lng = parseFloat(nuevaLng);
    if (isNaN(lat) || isNaN(lng)) {
        console.error('Coordenadas inválidas para usuario:', userId, { lat: nuevaLat, lng: nuevaLng });
        return;
    }

    // Si el mapa no está listo, reintentar en 500ms (máximo 3 intentos)
    if (!estadoMapa.inicializado) {
        console.log('⚠️ Mapa no inicializado. Reintentando en 500ms...');
        setTimeout(() => {
            actualizarOMarcarEscolta(userId, name, nuevaLat, nuevaLng, nuevoRecordedAt, userData);
        }, 500);
        return;
    }

    // Verificar que los grupos existan
    if (!grupoUsuariosEscolta || !(grupoUsuariosEscolta instanceof L.LayerGroup)) {
        console.error('❌ grupoUsuariosEscolta no es un LayerGroup válido');
        return;
    }

    let marker = marcadoresEscolta[userId];
    if (marker) {
        marker.setLatLng([lat, lng]);
        const popupContent = `
            <div>
                <strong>${name}</strong><br>
                <small>Rol: ${userData?.rol || 'N/A'}</small><br>
                <small>Última actualización: ${dayjs(nuevoRecordedAt).fromNow()}</small>
            </div>
        `;
        marker.getPopup().setContent(popupContent);
        console.log('🔄 Marcador actualizado para usuario:', userId);
    } else {
        const newUserObj = { id: userId, name, latitude: lat, longitude: lng, recorded_at: nuevoRecordedAt, user_data: userData };
        const newMarker = crearMarcadorEscolta(newUserObj);
        if (newMarker) {
            grupoUsuariosEscolta.addLayer(newMarker);
            marcadoresEscolta[userId] = newMarker;
            console.log('✅ Nuevo marcador creado para usuario:', userId, 'en', [lat, lng]);
        }
    }
};

const cargarUsuariosEscolta = (users) => {
    if (!grupoUsuariosEscolta || !(grupoUsuariosEscolta instanceof L.LayerGroup)) {
        console.error('❌ No se puede cargar usuarios: grupoUsuariosEscolta no válido');
        return;
    }

    grupoUsuariosEscolta.clearLayers();
    marcadoresEscolta = {};

    // Guardar todos los usuarios para el buscador
    todosLosUsuarios = [...users];

    // Aplicar filtro de búsqueda si hay texto
    const textoBusqueda = document.getElementById('buscador-usuarios')?.value?.toLowerCase() || '';
    const usuariosFiltrados = textoBusqueda
        ? users.filter(u => u.name.toLowerCase().includes(textoBusqueda))
        : users;

    if (!usuariosFiltrados.length) return;

    usuariosFiltrados.forEach(user => {
        const marker = crearMarcadorEscolta(user);
        if (marker) {
            grupoUsuariosEscolta.addLayer(marker);
            marcadoresEscolta[user.id] = marker;
        }
    });

    // Actualizar estadísticas
    actualizarEstadisticas(users);
};

const actualizarEstadisticas = (users) => {
    // Total de usuarios en el sistema con estatus Activo
    const total = users.length;

    // Usuarios sin reportar en los últimos 10 minutos
    const sinReportar = users.filter(u => {
        const ultima = dayjs(u.recorded_at);
        return dayjs().diff(ultima, 'minute') > 10;
    }).length;

    // Usuarios con reporte reciente (menos de 10 min)
    const activos = total - sinReportar;

    // Inactivos (sin ubicación reciente en las últimas 24 hrs)
    const inactivos = todosLosUsuarios.length - total;

    // Actualizar contadores en el DOM
    document.getElementById('contador-activos').textContent = activos;
    document.getElementById('contador-inactivos').textContent = inactivos;
    document.getElementById('contador-sin-reporte').textContent = sinReportar;
    document.getElementById('contador-total').textContent = todosLosUsuarios.length;
};

// --- FUNCIONES DE GEOCERCAS ---
const crearGeocerca = (centro, radioKm, tipo, nombre) => {
    const lat = parseFloat(centro.lat);
    const lng = parseFloat(centro.lng);
    if (isNaN(lat) || isNaN(lng)) return null;

    const radioMetros = radioKm * 1000;
    let color = 'blue', fillColor = 'lightblue';
    if (tipo === 'hotel') { color = 'green'; fillColor = 'lightgreen'; }
    else if (tipo === 'aeropuerto') { color = 'orange'; fillColor = 'wheat'; }

    return L.circle([lat, lng], {
        radius: radioMetros,
        color: color,
        fillColor: fillColor,
        fillOpacity: 0.2,
        weight: 2
    }).bindPopup(`<b>${nombre}</b><br>Tipo: ${tipo}<br>Radio: ${radioKm} km`);
};

const cargarGeocercas = (geocercasData) => {
    if (!grupoGeocercas || !(grupoGeocercas instanceof L.LayerGroup)) {
        console.error('❌ No se puede cargar geocercas: grupoGeocercas no válido');
        return;
    }

    grupoGeocercas.clearLayers();
    geocercasActuales = {};
    if (!geocercasData?.length) return;

    geocercasData.forEach(gf => {
        const layer = crearGeocerca(gf.centro, gf.radio_km, gf.tipo, gf.nombre_referencia);
        if (layer) {
            grupoGeocercas.addLayer(layer);
            geocercasActuales[gf.id] = layer;
        }
    });

    const layers = grupoGeocercas.getLayers();
    if (layers.length > 0) {
        const bounds = L.featureGroup(layers).getBounds();
        if (bounds.isValid()) {
            mapa.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });
        }
    }
};

// --- INICIALIZACIÓN DEL MAPA ---
const actualizarEstadoMapa = (mensaje) => {
    const el = document.getElementById('mapaEstado');
    if (el) el.textContent = mensaje;
};

const inicializarMapa = () => {
    if (mapa) {
        mapa.invalidateSize();
        return Promise.resolve();
    }

    const contenedor = document.getElementById('mapaContainer');
    if (!contenedor) {
        actualizarEstadoMapa('Error: Contenedor del mapa no encontrado.');
        return Promise.resolve();
    }

    contenedor.innerHTML = '';
    mapa = L.map(contenedor, {
        center: [25.6866, -100.3161],
        zoom: 13
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);

    grupoGeocercas = L.layerGroup().addTo(mapa);
    grupoUsuariosEscolta = L.layerGroup().addTo(mapa);
    grupoRutasHistorial = L.layerGroup().addTo(mapa); // ✅ Nuevo grupo añadido al mapa

    estadoMapa.inicializado = true;

    console.log('✅ Mapa inicializado. Grupos creados:');
    console.log('grupoGeocercas:', grupoGeocercas);
    console.log('grupoUsuariosEscolta:', grupoUsuariosEscolta);
    console.log('grupoRutasHistorial:', grupoRutasHistorial);

    actualizarEstadoMapa('Mapa listo');
    return Promise.resolve();
};

const centrarVistaMapa = () => {
    if (!mapa || !grupoGeocercas) return;
    const layers = grupoGeocercas.getLayers();
    if (layers.length > 0) {
        const bounds = L.featureGroup(layers).getBounds();
        if (bounds.isValid()) mapa.fitBounds(bounds, { padding: [50, 50] });
    }
};

// --- INICIALIZACIÓN PRINCIPAL ---
const inicializarSistema = async () => {
    if (typeof L === 'undefined') {
        setTimeout(inicializarSistema, 100);
        return;
    }
    await inicializarMapa();
};

// --- ESCUCHADORES ---
document.addEventListener('DOMContentLoaded', () => {
    inicializarSistema();

    // ✅ ESCUCHAR EVENTO DE WEBSOCKET CORRECTAMENTE
    if (typeof window.Echo !== 'undefined') {
        window.Echo.channel('realtime-positions.all')
            .listen('.NuevaUbicacionRealtime', (e) => {
                console.log('📍 Ubicación en tiempo real recibida:', e);
                const { user_id, latitude, longitude, recorded_at, user } = e.position;
                if (user && user.rol && user.rol.toLowerCase().includes('escolta')) {
                    actualizarOMarcarEscolta(user_id, user.name, latitude, longitude, recorded_at, user);
                } else {
                    console.log('ℹ️ Usuario no es escolta, ignorado:', user_id);
                }
            });
    } else {
        console.error('❌ Echo no está disponible. Verifica tu archivo echo.js');
    }
});

// Eventos desde Livewire
window.addEventListener('escortUsersLoaded', (e) => {
    console.log("🔔 Evento 'escortUsersLoaded' recibido:", e.detail);
    if (estadoMapa.inicializado) {
        cargarUsuariosEscolta(e.detail.users || []);
    } else {
        inicializarSistema().then(() => cargarUsuariosEscolta(e.detail.users || []));
    }
});

window.addEventListener('geocercasActualizadas', (e) => {
    console.log("🔔 Evento 'geocercasActualizadas' recibido:", e.detail);
    if (estadoMapa.inicializado) {
        cargarGeocercas(e.detail.geocercas || []);
    } else {
        inicializarSistema().then(() => cargarGeocercas(e.detail.geocercas || []));
    }
});

// Botón centrar
document.addEventListener('click', (e) => {
    if (e.target.closest('#btn-centrar-mapa')) centrarVistaMapa();
});

// Listener para buscador
document.getElementById('buscador-usuarios')?.addEventListener('input', (e) => {
    if (estadoMapa.inicializado) {
        cargarUsuariosEscolta(todosLosUsuarios);
    }
});
</script>
@endpush
</x-livewire.monitoreo-layout>
