<x-app-layout>
    <x-navbar />

    @push('styles')
        <style>
            #traccar-map {
                min-height: 34rem;
                z-index: 0;
            }

            #gps-map-panel:fullscreen #traccar-map {
                height: 100vh;
            }

            .gps-marker {
                align-items: center;
                border: 3px solid white;
                border-radius: 9999px;
                box-shadow: 0 4px 12px rgb(15 23 42 / 35%);
                color: white;
                display: flex;
                height: 2.25rem;
                justify-content: center;
                width: 2.25rem;
            }

            .gps-marker--moving { background: #16a34a; }
            .gps-marker--idle { background: #f59e0b; }
            .gps-marker--parked { background: #2563eb; }
            .gps-marker--offline { background: #64748b; }
            .gps-marker--unknown { background: #dc2626; }

            .gps-marker i {
                font-size: 1.15rem;
            }

            .gps-marker__direction {
                align-items: center;
                display: flex;
                height: 100%;
                justify-content: center;
                transition: transform 250ms ease;
                width: 100%;
            }

            .gps-marker__vehicle {
                color: white;
                filter: drop-shadow(0 1px 1px rgb(15 23 42 / 45%));
                height: 1.5rem;
                width: 1.5rem;
            }

            .gps-marker__window {
                fill: rgb(15 23 42 / 55%);
            }

            .gps-marker-label {
                background: rgb(15 23 42 / 88%);
                border-radius: .35rem;
                color: white;
                font-size: .7rem;
                font-weight: 700;
                left: 50%;
                max-width: 10rem;
                overflow: hidden;
                padding: .2rem .45rem;
                position: absolute;
                text-overflow: ellipsis;
                top: 2.55rem;
                transform: translateX(-50%);
                white-space: nowrap;
            }

            .gps-following {
                box-shadow: 0 0 0 4px rgb(59 130 246 / 30%), 0 4px 12px rgb(15 23 42 / 35%);
            }
        </style>
    @endpush

    <div class="px-4 py-6 mx-auto max-w-7xl">
        <x-livewire.monitoreo-layout
            :breadcrumb-items="[
                ['label' => 'Monitoreo', 'url' => route('admin.monitoreoDashboard'), 'icon' => 'ti-dashboard'],
                ['label' => 'Unidades GPS', 'icon' => 'ti-satellite'],
            ]"
            title-main="Unidades GPS"
            help-text="Seguimiento en tiempo real de las unidades registradas en Traccar"
        >
            <div class="space-y-5">
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                    <div class="p-4 border border-gray-200 rounded-xl bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40">
                        <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Unidades visibles</p>
                        <p id="gps-visible-count" class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">0</p>
                    </div>
                    <div class="p-4 border border-emerald-200 rounded-xl bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-900/20">
                        <p class="text-xs font-semibold tracking-wide uppercase text-emerald-700 dark:text-emerald-300">En movimiento</p>
                        <p id="gps-moving-count" class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">0</p>
                    </div>
                    <div class="p-4 border border-amber-200 rounded-xl bg-amber-50 dark:border-amber-900 dark:bg-amber-900/20">
                        <p class="text-xs font-semibold tracking-wide text-amber-700 uppercase dark:text-amber-300">Detenidas</p>
                        <p id="gps-idle-count" class="mt-1 text-2xl font-bold text-amber-700 dark:text-amber-300">0</p>
                    </div>
                    <div class="p-4 border border-blue-200 rounded-xl bg-blue-50 dark:border-blue-900 dark:bg-blue-900/20">
                        <p class="text-xs font-semibold tracking-wide text-blue-700 uppercase dark:text-blue-300">Estacionadas</p>
                        <p id="gps-parked-count" class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-300">0</p>
                    </div>
                    <div class="p-4 border border-gray-200 rounded-xl bg-white dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Conexión</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span id="gps-connection-dot" class="w-3 h-3 bg-gray-400 rounded-full"></span>
                            <span id="gps-connection-label" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Preparando mapa…</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 p-4 border border-gray-200 rounded-xl bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 lg:flex-row lg:items-end">
                    <label class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                        Buscar unidad
                        <input
                            id="gps-search"
                            type="search"
                            placeholder="Nombre, identificador o modelo"
                            class="w-full mt-1 border-gray-300 rounded-lg shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                        >
                    </label>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200 lg:w-52">
                        Estado
                        <select id="gps-status-filter" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="all">Todos</option>
                            <option value="moving">En movimiento</option>
                            <option value="idle">Detenida con ignición</option>
                            <option value="parked">Estacionada</option>
                            <option value="offline">Fuera de línea</option>
                            <option value="unknown">Desconocido</option>
                        </select>
                    </label>
                    <div class="flex gap-2">
                        <button id="gps-refresh" type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white transition bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="ti ti-refresh"></i>
                            Actualizar
                        </button>
                        <button id="gps-fit" type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                            <i class="ti ti-focus-2"></i>
                            Centrar
                        </button>
                        <button id="gps-fullscreen" type="button" title="Pantalla completa" class="inline-flex items-center justify-center px-3 py-2 text-gray-700 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                            <i class="ti ti-maximize"></i>
                            <span class="sr-only">Pantalla completa</span>
                        </button>
                    </div>
                </div>

                <div id="gps-error" class="hidden p-4 text-sm text-red-800 border border-red-200 rounded-xl bg-red-50 dark:border-red-900 dark:bg-red-900/20 dark:text-red-200" role="alert"></div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_21rem]">
                    <div id="gps-map-panel" class="relative overflow-hidden bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-900">
                        <div id="traccar-map" aria-label="Mapa de unidades GPS"></div>
                        <div id="gps-follow-banner" class="absolute z-[500] hidden top-3 left-1/2 -translate-x-1/2 items-center gap-2 px-3 py-2 text-sm font-semibold text-blue-800 bg-blue-50 border border-blue-200 rounded-lg shadow-lg">
                            <i class="ti ti-navigation"></i>
                            <span id="gps-follow-label">Siguiendo unidad</span>
                            <button id="gps-stop-follow" type="button" class="ml-2 text-blue-700 underline">Detener</button>
                        </div>
                    </div>

                    <aside class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-700">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50">
                            <h2 class="font-semibold text-gray-900 dark:text-white">Unidades</h2>
                            <p id="gps-last-update" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sin datos todavía</p>
                        </div>
                        <div id="gps-device-list" class="overflow-y-auto divide-y divide-gray-100 max-h-[34rem] dark:divide-gray-700">
                            <div class="p-6 text-sm text-center text-gray-500 dark:text-gray-400">Cargando unidades…</div>
                        </div>
                    </aside>
                </div>

                <section id="gps-detail" class="hidden overflow-hidden bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800" aria-live="polite">
                    <div class="flex flex-col gap-3 px-5 py-4 border-b border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Unidad seleccionada</p>
                            <h2 id="gps-detail-name" class="mt-1 text-xl font-bold text-gray-900 dark:text-white"></h2>
                            <p id="gps-detail-identity" class="mt-1 text-sm text-gray-500 dark:text-gray-400"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="gps-detail-status" class="px-3 py-1 text-sm font-semibold rounded-full"></span>
                            <button id="gps-follow" type="button" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                <i class="ti ti-navigation"></i>
                                Seguir unidad
                            </button>
                            <button id="gps-close-detail" type="button" title="Cerrar detalle" class="p-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                    <dl id="gps-detail-grid" class="grid grid-cols-2 gap-px bg-gray-200 dark:bg-gray-700 md:grid-cols-4"></dl>
                </section>
            </div>
        </x-livewire.monitoreo-layout>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const endpoints = {
                    data: @json(route('monitoreo.unidades-gps.data')),
                    socketToken: @json(route('monitoreo.unidades-gps.socket-token')),
                    websocket: @json($websocketUrl),
                };

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                const devices = new Map();
                const positions = new Map();
                const markers = new Map();
                const markerLayer = L.layerGroup();
                let socket = null;
                let reconnectTimer = null;
                let reconnectAttempts = 0;
                let closingPage = false;
                let selectedDeviceId = null;
                let followedDeviceId = null;

                const map = L.map('traccar-map', {
                    center: [25.6866, -100.3161],
                    zoom: 11,
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);
                markerLayer.addTo(map);

                const elements = {
                    connectionDot: document.getElementById('gps-connection-dot'),
                    connectionLabel: document.getElementById('gps-connection-label'),
                    deviceList: document.getElementById('gps-device-list'),
                    error: document.getElementById('gps-error'),
                    fit: document.getElementById('gps-fit'),
                    follow: document.getElementById('gps-follow'),
                    followBanner: document.getElementById('gps-follow-banner'),
                    followLabel: document.getElementById('gps-follow-label'),
                    fullscreen: document.getElementById('gps-fullscreen'),
                    idleCount: document.getElementById('gps-idle-count'),
                    lastUpdate: document.getElementById('gps-last-update'),
                    mapPanel: document.getElementById('gps-map-panel'),
                    movingCount: document.getElementById('gps-moving-count'),
                    parkedCount: document.getElementById('gps-parked-count'),
                    refresh: document.getElementById('gps-refresh'),
                    search: document.getElementById('gps-search'),
                    statusFilter: document.getElementById('gps-status-filter'),
                    stopFollow: document.getElementById('gps-stop-follow'),
                    detail: document.getElementById('gps-detail'),
                    detailGrid: document.getElementById('gps-detail-grid'),
                    detailIdentity: document.getElementById('gps-detail-identity'),
                    detailName: document.getElementById('gps-detail-name'),
                    detailStatus: document.getElementById('gps-detail-status'),
                    closeDetail: document.getElementById('gps-close-detail'),
                    visibleCount: document.getElementById('gps-visible-count'),
                };

                const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#039;',
                    '"': '&quot;',
                })[character]);

                const booleanValue = (value) => {
                    if (typeof value === 'boolean') return value;
                    if (typeof value === 'number') return value !== 0;
                    if (typeof value === 'string') {
                        if (['true', '1', 'on', 'yes'].includes(value.toLowerCase())) return true;
                        if (['false', '0', 'off', 'no'].includes(value.toLowerCase())) return false;
                    }
                    return null;
                };

                const positionAttribute = (position, ...names) => {
                    for (const name of names) {
                        if (position?.attributes?.[name] !== undefined) return position.attributes[name];
                        if (position?.[name] !== undefined) return position[name];
                    }
                    return null;
                };

                const deviceAttribute = (device, ...names) => {
                    for (const name of names) {
                        if (device?.attributes?.[name] !== undefined) return device.attributes[name];
                        if (device?.[name] !== undefined) return device[name];
                    }
                    return null;
                };

                const speedKmhValue = (position) => {
                    const speed = Number(position?.speed);
                    return Number.isFinite(speed) ? speed * 1.852 : null;
                };

                const operationalStatus = (device, position) => {
                    if (device?.status === 'offline') return 'offline';
                    if (device?.status !== 'online') return 'unknown';

                    const speed = speedKmhValue(position);
                    const motion = booleanValue(positionAttribute(position, 'motion'));
                    const ignition = booleanValue(positionAttribute(position, 'ignition'));

                    if (motion === true || (speed !== null && speed > 1)) return 'moving';
                    if (ignition === true) return 'idle';
                    if (ignition === false) return 'parked';
                    return 'unknown';
                };

                const statusLabel = (status) => ({
                    moving: 'En movimiento',
                    idle: 'Detenida con ignición',
                    parked: 'Estacionada',
                    offline: 'Fuera de línea',
                    unknown: 'Desconocido',
                })[status] ?? 'Desconocido';

                const statusClasses = (status) => ({
                    moving: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                    idle: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                    parked: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                    offline: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                    unknown: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
                })[status] ?? 'bg-gray-200 text-gray-700';

                const setConnectionStatus = (status, label) => {
                    const colors = {
                        connected: 'bg-emerald-500',
                        connecting: 'bg-amber-500 animate-pulse',
                        disconnected: 'bg-red-500',
                    };
                    elements.connectionDot.className = `w-3 h-3 rounded-full ${colors[status] ?? 'bg-gray-400'}`;
                    elements.connectionLabel.textContent = label;
                };

                const showError = (message = '') => {
                    elements.error.textContent = message;
                    elements.error.classList.toggle('hidden', message === '');
                };

                const formatDate = (value) => {
                    if (!value) return 'Sin fecha';
                    const date = new Date(value);
                    return Number.isNaN(date.getTime())
                        ? 'Sin fecha'
                        : date.toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'medium' });
                };

                const formatNumber = (value, suffix = '', decimals = 0) => {
                    const number = Number(value);
                    return Number.isFinite(number) ? `${number.toFixed(decimals)}${suffix}` : 'No disponible';
                };

                const ignitionLabel = (position) => {
                    const ignition = booleanValue(positionAttribute(position, 'ignition'));
                    return ignition === null ? 'No disponible' : (ignition ? 'Encendida' : 'Apagada');
                };

                const courseData = (position) => {
                    const rawCourse = Number(position?.course);
                    if (!Number.isFinite(rawCourse)) return null;

                    const degrees = ((rawCourse % 360) + 360) % 360;
                    const directions = ['Norte', 'Noreste', 'Este', 'Sureste', 'Sur', 'Suroeste', 'Oeste', 'Noroeste'];

                    return {
                        degrees,
                        direction: directions[Math.round(degrees / 45) % directions.length],
                    };
                };

                const vehicleSvg = `
                    <svg class="gps-marker__vehicle" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill="currentColor" d="M9.2 2.2A2.2 2.2 0 0 1 11.1 1h1.8a2.2 2.2 0 0 1 1.9 1.2l2.3 4.3c.6 1.1.9 2.4.9 3.7v9.6c0 1.2-1 2.2-2.2 2.2H8.2A2.2 2.2 0 0 1 6 19.8v-9.6c0-1.3.3-2.6.9-3.7l2.3-4.3Z"/>
                        <path class="gps-marker__window" d="M9.1 7.2 10.4 4h3.2l1.3 3.2H9.1Zm-.7 2.1h7.2v5.4H8.4V9.3Z"/>
                        <path fill="currentColor" d="M4.8 9.2h1.4v4.1H4.8a.8.8 0 0 1-.8-.8V10a.8.8 0 0 1 .8-.8Zm13 0h1.4a.8.8 0 0 1 .8.8v2.5a.8.8 0 0 1-.8.8h-1.4V9.2Z"/>
                    </svg>`;

                const markerIcon = (device, position, status, following = false) => {
                    const course = courseData(position);
                    const rotation = course?.degrees ?? 0;
                    const name = escapeHtml(device.name || `Unidad ${device.id}`);

                    return L.divIcon({
                    className: '',
                    html: `<div class="gps-marker gps-marker--${status} ${following ? 'gps-following' : ''}">
                        <span class="gps-marker__direction" style="transform: rotate(${rotation}deg)" title="${escapeHtml(course ? `${course.direction} (${course.degrees.toFixed(0)}°)` : 'Rumbo no disponible')}">${vehicleSvg}</span>
                        <span class="gps-marker-label">${name}</span>
                    </div>`,
                    iconAnchor: [18, 18],
                    iconSize: [36, 36],
                    popupAnchor: [0, -19],
                    });
                };

                const positionDate = (device, position) => position?.fixTime
                    ?? position?.deviceTime
                    ?? position?.serverTime
                    ?? device?.lastUpdate;

                const updateMarker = (deviceId) => {
                    const key = String(deviceId);
                    const device = devices.get(key);
                    const position = positions.get(key);
                    const latitude = Number(position?.latitude);
                    const longitude = Number(position?.longitude);

                    if (!device || !Number.isFinite(latitude) || !Number.isFinite(longitude)) return;
                    const status = operationalStatus(device, position);

                    let marker = markers.get(key);
                    if (!marker) {
                        marker = L.marker([latitude, longitude], {
                            icon: markerIcon(device, position, status, followedDeviceId === key),
                            deviceId: key,
                        });
                        marker.on('click', () => selectDevice(key));
                        markers.set(key, marker);
                    } else {
                        marker.setLatLng([latitude, longitude]);
                        marker.setIcon(markerIcon(device, position, status, followedDeviceId === key));
                    }

                    const speed = speedKmhValue(position);
                    const speedKmh = speed === null ? 'No disponible' : `${speed.toFixed(1)} km/h`;
                    marker.bindPopup(`
                        <div class="min-w-56">
                            <h3 class="font-bold text-gray-900">${escapeHtml(device.name || `Unidad ${key}`)}</h3>
                            <p class="mt-1 text-sm text-gray-600">${escapeHtml(statusLabel(status))}</p>
                            <dl class="mt-3 space-y-1 text-sm">
                                <div class="flex justify-between gap-4"><dt class="text-gray-500">Velocidad</dt><dd class="font-medium">${escapeHtml(speedKmh)}</dd></div>
                                <div class="flex justify-between gap-4"><dt class="text-gray-500">Ignición</dt><dd class="font-medium">${escapeHtml(ignitionLabel(position))}</dd></div>
                                <div class="flex justify-between gap-4"><dt class="text-gray-500">Modelo</dt><dd class="font-medium">${escapeHtml(device.model || 'No disponible')}</dd></div>
                                <div class="flex justify-between gap-4"><dt class="text-gray-500">Actualización</dt><dd class="font-medium">${escapeHtml(formatDate(positionDate(device, position)))}</dd></div>
                            </dl>
                        </div>
                    `);

                    if (followedDeviceId === key) map.panTo(marker.getLatLng(), { animate: true });
                    if (selectedDeviceId === key) renderDetail();
                };

                const detailItem = (label, value, icon) => `
                    <div class="p-4 bg-white dark:bg-gray-800">
                        <dt class="flex items-center gap-2 text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400"><i class="ti ti-${icon}"></i>${escapeHtml(label)}</dt>
                        <dd class="mt-2 text-sm font-bold text-gray-900 break-words dark:text-white">${escapeHtml(value)}</dd>
                    </div>`;

                const renderDetail = () => {
                    const device = selectedDeviceId ? devices.get(selectedDeviceId) : null;
                    const position = selectedDeviceId ? positions.get(selectedDeviceId) : null;
                    if (!device) {
                        elements.detail.classList.add('hidden');
                        return;
                    }

                    const status = operationalStatus(device, position);
                    const speed = speedKmhValue(position);
                    const battery = positionAttribute(position, 'batteryLevel');
                    const power = positionAttribute(position, 'power');
                    const totalDistanceRaw = positionAttribute(position, 'totalDistance', 'odometer');
                    const totalDistance = totalDistanceRaw === null ? null : Number(totalDistanceRaw);
                    const course = courseData(position);
                    const plate = deviceAttribute(device, 'plate', 'registration', 'vehicleNumber');
                    const driver = positionAttribute(position, 'driverUniqueId', 'driver');
                    const satellites = positionAttribute(position, 'sat', 'satellites');

                    elements.detail.classList.remove('hidden');
                    elements.detailName.textContent = device.name || `Unidad ${device.id}`;
                    elements.detailIdentity.textContent = [plate, device.model, device.uniqueId].filter(Boolean).join(' · ') || 'Sin identificador adicional';
                    elements.detailStatus.className = `px-3 py-1 text-sm font-semibold rounded-full ${statusClasses(status)}`;
                    elements.detailStatus.textContent = statusLabel(status);
                    elements.follow.innerHTML = followedDeviceId === selectedDeviceId
                        ? '<i class="ti ti-player-stop"></i> Detener seguimiento'
                        : '<i class="ti ti-navigation"></i> Seguir unidad';
                    elements.detailGrid.innerHTML = [
                        detailItem('Ignición', ignitionLabel(position), 'key'),
                        detailItem('Velocidad', speed === null ? 'No disponible' : `${speed.toFixed(1)} km/h`, 'speedboat'),
                        detailItem('Rumbo', course ? `${course.degrees.toFixed(0)}° · ${course.direction}` : 'No disponible', 'compass'),
                        detailItem('Última posición', formatDate(positionDate(device, position)), 'clock'),
                        detailItem('Batería', battery === null ? 'No disponible' : formatNumber(battery, '%', 0), 'battery'),
                        detailItem('Alimentación', power === null ? 'No disponible' : formatNumber(power, ' V', 1), 'bolt'),
                        detailItem('Odómetro', totalDistance !== null && Number.isFinite(totalDistance) ? `${(totalDistance / 1000).toFixed(1)} km` : 'No disponible', 'road'),
                        detailItem('Coordenadas', Number.isFinite(Number(position?.latitude)) && Number.isFinite(Number(position?.longitude)) ? `${Number(position.latitude).toFixed(5)}, ${Number(position.longitude).toFixed(5)}` : 'No disponible', 'map-pin'),
                        detailItem('Placa / económico', plate ?? 'No disponible', 'id-badge-2'),
                        detailItem('Conductor', driver ?? 'No disponible', 'user'),
                        detailItem('Satélites', satellites ?? 'No disponible', 'satellite'),
                        detailItem('Identificador GPS', device.uniqueId ?? 'No disponible', 'device-mobile'),
                    ].join('');
                };

                const selectDevice = (deviceId) => {
                    selectedDeviceId = String(deviceId);
                    renderDetail();
                    const marker = markers.get(selectedDeviceId);
                    if (marker) {
                        map.setView(marker.getLatLng(), Math.max(map.getZoom(), 16), { animate: true });
                        marker.openPopup();
                    }
                };

                const stopFollowing = () => {
                    const previousId = followedDeviceId;
                    followedDeviceId = null;
                    elements.followBanner.classList.add('hidden');
                    elements.followBanner.classList.remove('flex');
                    if (previousId) updateMarker(previousId);
                    renderDetail();
                };

                const toggleFollowing = () => {
                    if (!selectedDeviceId || !markers.has(selectedDeviceId)) return;
                    if (followedDeviceId === selectedDeviceId) {
                        stopFollowing();
                        return;
                    }
                    const previousId = followedDeviceId;
                    followedDeviceId = selectedDeviceId;
                    if (previousId) updateMarker(previousId);
                    updateMarker(followedDeviceId);
                    elements.followLabel.textContent = `Siguiendo ${devices.get(followedDeviceId)?.name || 'unidad'}`;
                    elements.followBanner.classList.remove('hidden');
                    elements.followBanner.classList.add('flex');
                    renderDetail();
                };

                const render = () => {
                    const query = elements.search.value.trim().toLocaleLowerCase('es-MX');
                    const selectedStatus = elements.statusFilter.value;
                    const visibleDevices = [...devices.values()]
                        .filter((device) => {
                            const position = positions.get(String(device.id));
                            const status = operationalStatus(device, position);
                            const searchable = `${device.name ?? ''} ${device.uniqueId ?? ''} ${device.model ?? ''} ${device.phone ?? ''} ${deviceAttribute(device, 'plate', 'registration', 'vehicleNumber') ?? ''}`.toLocaleLowerCase('es-MX');
                            return (selectedStatus === 'all' || status === selectedStatus)
                                && (query === '' || searchable.includes(query));
                        })
                        .sort((left, right) => String(left.name ?? '').localeCompare(String(right.name ?? ''), 'es-MX'));

                    const visibleIds = new Set(visibleDevices.map((device) => String(device.id)));
                    markers.forEach((marker, deviceId) => {
                        if (visibleIds.has(deviceId)) {
                            if (!markerLayer.hasLayer(marker)) markerLayer.addLayer(marker);
                        } else if (markerLayer.hasLayer(marker)) {
                            markerLayer.removeLayer(marker);
                        }
                    });

                    elements.visibleCount.textContent = visibleDevices.length;
                    elements.movingCount.textContent = visibleDevices.filter((device) => operationalStatus(device, positions.get(String(device.id))) === 'moving').length;
                    elements.idleCount.textContent = visibleDevices.filter((device) => operationalStatus(device, positions.get(String(device.id))) === 'idle').length;
                    elements.parkedCount.textContent = visibleDevices.filter((device) => operationalStatus(device, positions.get(String(device.id))) === 'parked').length;

                    if (visibleDevices.length === 0) {
                        elements.deviceList.innerHTML = '<div class="p-6 text-sm text-center text-gray-500 dark:text-gray-400">No hay unidades que coincidan con los filtros.</div>';
                        return;
                    }

                    elements.deviceList.innerHTML = visibleDevices.map((device) => {
                        const position = positions.get(String(device.id));
                        const hasPosition = position && Number.isFinite(Number(position.latitude)) && Number.isFinite(Number(position.longitude));
                        const status = operationalStatus(device, position);
                        const speed = speedKmhValue(position);
                        const secondaryIdentity = deviceAttribute(device, 'plate', 'registration', 'vehicleNumber') || device.model || device.uniqueId || 'Sin modelo registrado';
                        return `
                            <button type="button" data-device-id="${escapeHtml(device.id)}" ${hasPosition ? '' : 'disabled'}
                                class="w-full p-4 text-left transition ${selectedDeviceId === String(device.id) ? 'bg-blue-50 dark:bg-gray-700' : ''} ${hasPosition ? 'hover:bg-blue-50 dark:hover:bg-gray-700' : 'cursor-default opacity-70'}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 truncate dark:text-white">${escapeHtml(device.name || `Unidad ${device.id}`)}</p>
                                        <p class="mt-1 text-xs text-gray-500 truncate dark:text-gray-400">${escapeHtml(secondaryIdentity)}</p>
                                    </div>
                                    <span class="shrink-0 px-2 py-1 text-xs font-semibold rounded-full ${statusClasses(status)}">${escapeHtml(statusLabel(status))}</span>
                                </div>
                                <div class="flex flex-wrap gap-x-3 gap-y-1 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span><i class="ti ti-key mr-1"></i>${escapeHtml(ignitionLabel(position))}</span>
                                    <span><i class="ti ti-gauge mr-1"></i>${escapeHtml(speed === null ? 'Sin velocidad' : `${speed.toFixed(1)} km/h`)}</span>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">${escapeHtml(formatDate(positionDate(device, position)))}</p>
                                ${hasPosition ? '' : '<p class="mt-1 text-xs text-amber-700 dark:text-amber-300">Sin posición disponible</p>'}
                            </button>
                        `;
                    }).join('');
                };

                const fitVisibleMarkers = () => {
                    const layers = markerLayer.getLayers();
                    if (layers.length === 0) return;
                    if (layers.length === 1) {
                        map.setView(layers[0].getLatLng(), 15);
                        return;
                    }
                    map.fitBounds(L.featureGroup(layers).getBounds(), { padding: [35, 35], maxZoom: 16 });
                };

                const mergeDevices = (items) => {
                    (Array.isArray(items) ? items : []).forEach((device) => {
                        if (device?.id === undefined) return;
                        const key = String(device.id);
                        devices.set(key, { ...(devices.get(key) ?? {}), ...device });
                        updateMarker(key);
                    });
                };

                const mergePositions = (items) => {
                    (Array.isArray(items) ? items : []).forEach((position) => {
                        if (position?.deviceId === undefined) return;
                        const key = String(position.deviceId);
                        positions.set(key, position);
                        updateMarker(key);
                    });
                };

                const loadData = async ({ fit = false } = {}) => {
                    elements.refresh.disabled = true;
                    elements.refresh.classList.add('opacity-60');
                    showError('');

                    try {
                        const response = await fetch(endpoints.data, {
                            credentials: 'same-origin',
                            headers: { Accept: 'application/json' },
                        });
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'No fue posible cargar las unidades.');

                        mergeDevices(payload.devices);
                        mergePositions(payload.positions);
                        render();
                        elements.lastUpdate.textContent = `Actualizado: ${formatDate(payload.fetched_at)}`;
                        if (fit) setTimeout(fitVisibleMarkers, 100);
                    } catch (error) {
                        showError(error.message || 'No fue posible cargar las unidades GPS.');
                    } finally {
                        elements.refresh.disabled = false;
                        elements.refresh.classList.remove('opacity-60');
                    }
                };

                const requestSocketToken = async () => {
                    const response = await fetch(endpoints.socketToken, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: '{}',
                    });
                    const payload = await response.json();
                    if (!response.ok || !payload.token) {
                        throw new Error(payload.message || 'No fue posible autenticar el tiempo real.');
                    }
                    return payload.token;
                };

                const scheduleReconnect = () => {
                    if (closingPage || reconnectTimer) return;
                    const delay = Math.min(30000, 1000 * (2 ** reconnectAttempts));
                    reconnectAttempts += 1;
                    setConnectionStatus('disconnected', `Reconectando en ${Math.ceil(delay / 1000)} s…`);
                    reconnectTimer = setTimeout(() => {
                        reconnectTimer = null;
                        connectSocket();
                    }, delay);
                };

                const connectSocket = async () => {
                    if (closingPage || socket?.readyState === WebSocket.OPEN || socket?.readyState === WebSocket.CONNECTING) return;
                    setConnectionStatus('connecting', 'Conectando con Traccar…');

                    try {
                        const token = await requestSocketToken();
                        const socketUrl = new URL(endpoints.websocket);
                        socketUrl.searchParams.set('token', token);
                        socket = new WebSocket(socketUrl.toString());

                        socket.addEventListener('open', () => {
                            reconnectAttempts = 0;
                            showError('');
                            setConnectionStatus('connected', 'Tiempo real conectado');
                        });

                        socket.addEventListener('message', (event) => {
                            try {
                                const payload = JSON.parse(event.data);
                                mergeDevices(payload.devices);
                                mergePositions(payload.positions);
                                render();
                                elements.lastUpdate.textContent = `Tiempo real: ${formatDate(new Date().toISOString())}`;
                            } catch (error) {
                                console.warn('Mensaje de Traccar no válido.', error);
                            }
                        });

                        socket.addEventListener('close', () => {
                            socket = null;
                            scheduleReconnect();
                        });

                        socket.addEventListener('error', () => {
                            setConnectionStatus('disconnected', 'Error en tiempo real');
                        });
                    } catch (error) {
                        showError(error.message || 'No fue posible conectar el seguimiento en tiempo real.');
                        scheduleReconnect();
                    }
                };

                elements.search.addEventListener('input', render);
                elements.statusFilter.addEventListener('change', render);
                elements.refresh.addEventListener('click', () => loadData());
                elements.fit.addEventListener('click', fitVisibleMarkers);
                elements.follow.addEventListener('click', toggleFollowing);
                elements.stopFollow.addEventListener('click', stopFollowing);
                elements.closeDetail.addEventListener('click', () => {
                    selectedDeviceId = null;
                    elements.detail.classList.add('hidden');
                    render();
                });
                elements.fullscreen.addEventListener('click', async () => {
                    try {
                        if (document.fullscreenElement) {
                            await document.exitFullscreen();
                        } else {
                            await elements.mapPanel.requestFullscreen();
                        }
                    } catch (error) {
                        showError('El navegador no permitió activar la pantalla completa.');
                    }
                });
                document.addEventListener('fullscreenchange', () => {
                    elements.fullscreen.innerHTML = document.fullscreenElement
                        ? '<i class="ti ti-minimize"></i><span class="sr-only">Salir de pantalla completa</span>'
                        : '<i class="ti ti-maximize"></i><span class="sr-only">Pantalla completa</span>';
                    setTimeout(() => map.invalidateSize(), 100);
                });
                elements.deviceList.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-device-id]');
                    if (!button || button.disabled) return;
                    selectDevice(button.dataset.deviceId);
                    render();
                });

                window.addEventListener('pagehide', () => {
                    closingPage = true;
                    if (reconnectTimer) clearTimeout(reconnectTimer);
                    socket?.close();
                });

                loadData({ fit: true }).finally(connectSocket);
            });
        </script>
    @endpush
</x-app-layout>
