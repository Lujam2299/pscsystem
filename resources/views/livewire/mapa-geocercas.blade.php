<x-livewire.monitoreo-layout :breadcrumb-items="[
        ['icon' => 'ti-home', 'url' => route('dashboard'), 'label' => 'Dashboard'],
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
                        @if($mision->fecha_inicio > now() || $mision->fecha_fin < now())
                        @else
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
                        @endif
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
                    <p class="text-sm text-gray-600 dark:text-gray-300">En Movimiento</p>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js    " crossorigin=""></script>
<!-- DayJS -->
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/dayjs.min.js    "></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/relativeTime.min.js    "></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/utc.min.js    "></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/timezone.min.js    "></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/plugin/updateLocale.min.js    "></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.10/locale/es.min.js    "></script>

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
let animacionActiva = null; // ✅ Para controlar animación

const estadoMapa = {
    inicializado: false
};

const interpolarCurvaCatmullRom = (puntos, muestrasPorTramo = 6) => {
    if (puntos.length < 2) return puntos;

    const resultado = [];
    const puntoEn = (index) => puntos[Math.max(0, Math.min(index, puntos.length - 1))];

    for (let i = 0; i < puntos.length - 1; i++) {
        const p0 = puntoEn(i - 1);
        const p1 = puntoEn(i);
        const p2 = puntoEn(i + 1);
        const p3 = puntoEn(i + 2);

        for (let muestra = 0; muestra < muestrasPorTramo; muestra++) {
            const t = muestra / muestrasPorTramo;
            const t2 = t * t;
            const t3 = t2 * t;
            const lat = 0.5 * ((2 * p1[0])
                + (-p0[0] + p2[0]) * t
                + (2 * p0[0] - 5 * p1[0] + 4 * p2[0] - p3[0]) * t2
                + (-p0[0] + 3 * p1[0] - 3 * p2[0] + p3[0]) * t3);
            const lng = 0.5 * ((2 * p1[1])
                + (-p0[1] + p2[1]) * t
                + (2 * p0[1] - 5 * p1[1] + 4 * p2[1] - p3[1]) * t2
                + (-p0[1] + 3 * p1[1] - 3 * p2[1] + p3[1]) * t3);

            resultado.push([lat, lng]);
        }
    }

    resultado.push(puntos[puntos.length - 1]);
    return resultado;
};

const limitarPuntosVisuales = (elementos, maximo) => {
    if (elementos.length <= maximo) return elementos;

    const resultado = [];
    const salto = (elementos.length - 1) / (maximo - 1);
    for (let i = 0; i < maximo; i++) {
        resultado.push(elementos[Math.round(i * salto)]);
    }

    return resultado;
};

const limitarIndicesVisuales = (total, maximo) => {
    if (total <= maximo) return Array.from({ length: total }, (_, index) => index);

    const indices = [];
    const salto = (total - 1) / (maximo - 1);
    for (let i = 0; i < maximo; i++) {
        indices.push(Math.round(i * salto));
    }

    return indices;
};

const distanciaMetrosEntre = (inicio, fin) => {
    const radioTierra = 6371000;
    const lat1 = inicio[0] * Math.PI / 180;
    const lat2 = fin[0] * Math.PI / 180;
    const deltaLat = (fin[0] - inicio[0]) * Math.PI / 180;
    const deltaLng = (fin[1] - inicio[1]) * Math.PI / 180;
    const a = Math.sin(deltaLat / 2) ** 2
        + Math.cos(lat1) * Math.cos(lat2) * Math.sin(deltaLng / 2) ** 2;

    return radioTierra * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
};

const crearTrayectoEstimado = (coordenadasGps) => {
    if (coordenadasGps.length < 2) return coordenadasGps;

    const distanciaPromedio = coordenadasGps
        .slice(1)
        .reduce((total, punto, index) => total + distanciaMetrosEntre(coordenadasGps[index], punto), 0)
        / (coordenadasGps.length - 1);

    const coordenadasBase = limitarPuntosVisuales(coordenadasGps, 1500);

    if (distanciaPromedio <= 250) {
        return interpolarCurvaCatmullRom(coordenadasBase, 4);
    }

    const puntosGuia = [coordenadasBase[0]];

    for (let i = 0; i < coordenadasBase.length - 1; i++) {
        const inicio = coordenadasBase[i];
        const fin = coordenadasBase[i + 1];
        const latMedia = (inicio[0] + fin[0]) / 2;
        const lngMedia = (inicio[1] + fin[1]) / 2;

        if (i % 2 === 0) {
            puntosGuia.push([inicio[0], lngMedia], [fin[0], lngMedia]);
        } else {
            puntosGuia.push([latMedia, inicio[1]], [latMedia, fin[1]]);
        }

        puntosGuia.push(fin);
    }

    return interpolarCurvaCatmullRom(puntosGuia);
};

const limitarPuntosAnimacion = (coordenadas, maximo = 300) => {
    if (coordenadas.length <= maximo) return coordenadas;

    const resultado = [];
    const salto = (coordenadas.length - 1) / (maximo - 1);
    for (let i = 0; i < maximo; i++) {
        resultado.push(coordenadas[Math.round(i * salto)]);
    }

    return resultado;
};

const obtenerSesionActual = (posicionesOrdenDescendente) => {
    if (posicionesOrdenDescendente.length < 2) return [...posicionesOrdenDescendente];

    const sesion = [posicionesOrdenDescendente[0]];
    for (let i = 1; i < posicionesOrdenDescendente.length; i++) {
        const posicionMasNueva = posicionesOrdenDescendente[i - 1];
        const posicionAnterior = posicionesOrdenDescendente[i];
        const diferenciaMinutos = (
            new Date(posicionMasNueva.recorded_at).getTime()
            - new Date(posicionAnterior.recorded_at).getTime()
        ) / 60000;

        if (diferenciaMinutos > 30) break;
        sesion.push(posicionAnterior);
    }

    return sesion;
};

// --- FUNCIONES DE MARCADORES ---
const crearMarcadorEscolta = (user) => {
    const lat = parseFloat(user.latitude);
    const lng = parseFloat(user.longitude);
    if (isNaN(lat) || isNaN(lng)) {
        console.error('Coordenadas inválidas al crear marcador:', user);
        return null;
    }

    // Calcular minutos desde la última actualización
    const tiempoUltimo = dayjs(user.recorded_at);
    const minutos = dayjs().diff(tiempoUltimo, 'minute');

    // Determinar color según tiempo
    let bgColor = '#22c55e'; // Verde
    let borderColor = 'black';
    if (minutos >= 10 && minutos < 60) {
        bgColor = '#f59e0b'; // Amarillo
    } else if (minutos >= 60 && minutos < 180) {
        bgColor = '#ef4444'; // Rojo
    } else if (minutos >= 180) {
        bgColor = '#9ca3af'; // Gris
    }

    // Ícono con color dinámico
    const escoltaIcon = L.divIcon({
        className: 'custom-escolta-icon',
        html: `<div style="
            background-color: ${bgColor};
            color: white;
            border: 2px solid ${borderColor};
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
            <small>Hace: ${minutos} min</small><br>
            <button onclick="mostrarHistorial(${user.id})" class="text-blue-500 underline text-sm mt-1">Ver historial</button>
        </div>
    `);

    // Opcional: Añadir tooltip con nombre
    marker.bindTooltip(`${user.name} (${minutos} min)`, { permanent: false, direction: 'top' });

    return marker;
};

// ✅ FUNCIÓN MODIFICADA: Mostrar historial con botón de animación
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
            const posicionesTrayecto = obtenerSesionActual(data.positions);
            const coordsGps = posicionesTrayecto
                .map(pos => [parseFloat(pos.latitude), parseFloat(pos.longitude)])
                .reverse(); // Más reciente al final

            // Dibujar línea de trayectoria
            const coordsEstimadas = crearTrayectoEstimado(coordsGps);

            const polyline = L.polyline(coordsEstimadas, {
                color: '#3b82f6', // Azul
                weight: 4,
                opacity: 0.7,
                smoothFactor: 0.5
            })
            .bindTooltip('Trayecto estimado', { sticky: true })
            .addTo(grupoRutasHistorial);

            // Dibujar marcadores pequeños en cada punto
            limitarIndicesVisuales(coordsGps.length, 200).forEach((index) => {
                const coord = coordsGps[index];
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
                        ${dayjs(posicionesTrayecto[posicionesTrayecto.length - 1 - index].recorded_at).format('HH:mm:ss')}<br>
                        ${coord[0]}, ${coord[1]}
                    </div>
                `)
                .addTo(grupoRutasHistorial);
            });

            // Ajustar vista al trayecto
            mapa.fitBounds(polyline.getBounds(), { padding: [50, 50] });
        }

        // ✅ Contenido con scroll y botón de animación
        let historialHtml = `
            <div style="max-width: 400px;">
                <h3 class="font-bold mb-2">Historial de ${data.total} ubicaciones (últimas 24 hrs)</h3>
                <div class="flex gap-2 mb-2">
                    <button onclick="limpiarRutaHistorial()" class="text-red-500 underline text-sm">Ocultar ruta</button>
                    <button onclick="animarTrayecto(${userId})" class="text-green-500 underline text-sm">Ver trayecto animado</button>
                </div>
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

// ✅ FUNCIÓN DE ANIMACIÓN DE TRAYECTO
const animarTrayecto = async (userId) => {
    console.log('Animando trayecto para usuario:', userId);

    // Cancelar animación previa si existe
    if (animacionActiva) {
        clearInterval(animacionActiva);
        animacionActiva = null;
    }

    // Obtener historial de ubicaciones
    try {
        const response = await fetch(`/api/realtime-position/user/${userId}/recent`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'include'
        });

        if (!response.ok) throw new Error('Error al cargar historial');

        const data = await response.json();
        const posicionesSesionActual = obtenerSesionActual(data.positions);
        if (posicionesSesionActual.length < 2) {
            alert('No hay suficientes puntos para animar el trayecto.');
            return;
        }

        // Coordenadas en orden (más antiguo a más reciente)
        const coordsGps = posicionesSesionActual
            .sort((a, b) => new Date(a.recorded_at) - new Date(b.recorded_at))
            .map(pos => [parseFloat(pos.latitude), parseFloat(pos.longitude)]);

        const coords = limitarPuntosAnimacion(crearTrayectoEstimado(coordsGps));

        // Crear ícono de animación
        const animIcon = L.divIcon({
            html: '<div style="background-color: #ff0000; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-weight: bold; box-shadow: 0 0 8px rgba(255,0,0,0.8);">●</div>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const markerAnim = L.marker(coords[0], { icon: animIcon }).addTo(mapa);
        let currentIndex = 0;

        animacionActiva = setInterval(() => {
            if (currentIndex >= coords.length) {
                clearInterval(animacionActiva);
                animacionActiva = null;
                // Opcional: remover el ícono al final
                setTimeout(() => mapa.removeLayer(markerAnim), 3000);
                return;
            }

            const [lat, lng] = coords[currentIndex];
            markerAnim.setLatLng([lat, lng]);
            if (!mapa.getBounds().pad(-0.15).contains([lat, lng])) {
                mapa.panTo([lat, lng], { animate: true, duration: 0.25 });
            }

            console.log(`Moviendo a punto ${currentIndex + 1}: ${lat}, ${lng}`);

            currentIndex++;
        }, 50);
    } catch (err) {
        console.error('Error en animación:', err);
        alert('No se pudo iniciar la animación del trayecto.');
    }
};

const actualizarOMarcarEscolta = (userId, name, nuevaLat, nuevaLng, nuevoRecordedAt, userData) => {
    const lat = parseFloat(nuevaLat);
    const lng = parseFloat(nuevaLng);
    if (isNaN(lat) || isNaN(lng)) {
        console.error('Coordenadas inválidas para usuario:', userId, { lat: nuevaLat, lng: nuevaLng });
        return;
    }

    // Si el mapa no está listo, reintentar en 500ms
    if (!estadoMapa.inicializado) {
        console.log('⚠️ Mapa no inicializado. Reintentando en 500ms...');
        setTimeout(() => {
            actualizarOMarcarEscolta(userId, name, nuevaLat, nuevaLng, nuevoRecordedAt, userData);
        }, 500);
        return;
    }

    if (!grupoUsuariosEscolta || !(grupoUsuariosEscolta instanceof L.LayerGroup)) {
        console.error('❌ grupoUsuariosEscolta no es un LayerGroup válido');
        return;
    }

    let marker = marcadoresEscolta[userId];
    if (marker) {
        // Actualizar posición
        marker.setLatLng([lat, lng]);

        // Recalcular color según nuevo tiempo
        const tiempoUltimo = dayjs(nuevoRecordedAt);
        const minutos = dayjs().diff(tiempoUltimo, 'minute');

        let bgColor = '#22c55e'; // Verde
        let borderColor = 'black';
        if (minutos >= 10 && minutos < 60) {
            bgColor = '#f59e0b'; // Amarillo
        } else if (minutos >= 60 && minutos < 180) {
            bgColor = '#ef4444'; // Rojo
        } else if (minutos >= 180) {
            bgColor = '#9ca3af'; // Gris
        }

        // Actualizar ícono con nuevo color
        const newIcon = L.divIcon({
            className: 'custom-escolta-icon',
            html: `<div style="
                background-color: ${bgColor};
                color: white;
                border: 2px solid ${borderColor};
                border-radius: 50%;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                box-shadow: 0 0 5px rgba(0,0,0,0.5);
                font-size: 14px;
            ">${name.charAt(0).toUpperCase()}</div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        marker.setIcon(newIcon);

        // Actualizar popup
        const popupContent = `
            <div>
                <strong>${name}</strong><br>
                <small>Rol: ${userData?.rol || 'N/A'}</small><br>
                <small>Última actualización: ${dayjs(nuevoRecordedAt).fromNow()}</small><br>
                <small>Hace: ${minutos} min</small><br>
                <button onclick="mostrarHistorial(${userId})" class="text-blue-500 underline text-sm mt-1">Ver historial</button>
            </div>
        `;
        marker.getPopup().setContent(popupContent);

        // Actualizar tooltip
        marker.getTooltip()?.setContent(`${name} (${minutos} min)`);

        console.log('🔄 Marcador actualizado para usuario:', userId, 'color:', bgColor);
    } else {
        // Crear nuevo marcador
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
