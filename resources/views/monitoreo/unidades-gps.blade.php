<x-app-layout>
    <x-navbar />

    @push('styles')
        <style>
            #traccar-map {
                min-height: 34rem;
                z-index: 0;
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

            .gps-marker--online { background: #059669; }
            .gps-marker--offline { background: #64748b; }
            .gps-marker--unknown { background: #d97706; }

            .gps-marker i {
                font-size: 1.15rem;
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
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="p-4 border border-gray-200 rounded-xl bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40">
                        <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Unidades visibles</p>
                        <p id="gps-visible-count" class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">0</p>
                    </div>
                    <div class="p-4 border border-emerald-200 rounded-xl bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-900/20">
                        <p class="text-xs font-semibold tracking-wide uppercase text-emerald-700 dark:text-emerald-300">En línea</p>
                        <p id="gps-online-count" class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">0</p>
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
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200 lg:w-48">
                        Estado
                        <select id="gps-status-filter" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="all">Todos</option>
                            <option value="online">En línea</option>
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
                    </div>
                </div>

                <div id="gps-error" class="hidden p-4 text-sm text-red-800 border border-red-200 rounded-xl bg-red-50 dark:border-red-900 dark:bg-red-900/20 dark:text-red-200" role="alert"></div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_21rem]">
                    <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-700">
                        <div id="traccar-map" aria-label="Mapa de unidades GPS"></div>
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
                    lastUpdate: document.getElementById('gps-last-update'),
                    onlineCount: document.getElementById('gps-online-count'),
                    refresh: document.getElementById('gps-refresh'),
                    search: document.getElementById('gps-search'),
                    statusFilter: document.getElementById('gps-status-filter'),
                    visibleCount: document.getElementById('gps-visible-count'),
                };

                const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#039;',
                    '"': '&quot;',
                })[character]);

                const normalizeStatus = (status) => ['online', 'offline'].includes(status) ? status : 'unknown';

                const statusLabel = (status) => ({
                    online: 'En línea',
                    offline: 'Fuera de línea',
                    unknown: 'Desconocido',
                })[normalizeStatus(status)];

                const statusClasses = (status) => ({
                    online: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                    offline: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                    unknown: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                })[normalizeStatus(status)];

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

                const markerIcon = (status) => L.divIcon({
                    className: '',
                    html: `<div class="gps-marker gps-marker--${normalizeStatus(status)}"><i class="ti ti-car"></i></div>`,
                    iconAnchor: [18, 18],
                    iconSize: [36, 36],
                    popupAnchor: [0, -19],
                });

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

                    let marker = markers.get(key);
                    if (!marker) {
                        marker = L.marker([latitude, longitude], {
                            icon: markerIcon(device.status),
                            deviceId: key,
                        });
                        markers.set(key, marker);
                    } else {
                        marker.setLatLng([latitude, longitude]);
                        marker.setIcon(markerIcon(device.status));
                    }

                    const speed = Number(position.speed);
                    const speedKmh = Number.isFinite(speed) ? `${(speed * 1.852).toFixed(1)} km/h` : 'No disponible';
                    marker.bindPopup(`
                        <div class="min-w-56">
                            <h3 class="font-bold text-gray-900">${escapeHtml(device.name || `Unidad ${key}`)}</h3>
                            <p class="mt-1 text-sm text-gray-600">${escapeHtml(statusLabel(device.status))}</p>
                            <dl class="mt-3 space-y-1 text-sm">
                                <div class="flex justify-between gap-4"><dt class="text-gray-500">Velocidad</dt><dd class="font-medium">${escapeHtml(speedKmh)}</dd></div>
                                <div class="flex justify-between gap-4"><dt class="text-gray-500">Modelo</dt><dd class="font-medium">${escapeHtml(device.model || 'No disponible')}</dd></div>
                                <div class="flex justify-between gap-4"><dt class="text-gray-500">Actualización</dt><dd class="font-medium">${escapeHtml(formatDate(positionDate(device, position)))}</dd></div>
                            </dl>
                        </div>
                    `);
                };

                const render = () => {
                    const query = elements.search.value.trim().toLocaleLowerCase('es-MX');
                    const selectedStatus = elements.statusFilter.value;
                    const visibleDevices = [...devices.values()]
                        .filter((device) => {
                            const status = normalizeStatus(device.status);
                            const searchable = `${device.name ?? ''} ${device.uniqueId ?? ''} ${device.model ?? ''}`.toLocaleLowerCase('es-MX');
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
                    elements.onlineCount.textContent = visibleDevices.filter((device) => normalizeStatus(device.status) === 'online').length;

                    if (visibleDevices.length === 0) {
                        elements.deviceList.innerHTML = '<div class="p-6 text-sm text-center text-gray-500 dark:text-gray-400">No hay unidades que coincidan con los filtros.</div>';
                        return;
                    }

                    elements.deviceList.innerHTML = visibleDevices.map((device) => {
                        const position = positions.get(String(device.id));
                        const hasPosition = position && Number.isFinite(Number(position.latitude)) && Number.isFinite(Number(position.longitude));
                        return `
                            <button type="button" data-device-id="${escapeHtml(device.id)}" ${hasPosition ? '' : 'disabled'}
                                class="w-full p-4 text-left transition ${hasPosition ? 'hover:bg-blue-50 dark:hover:bg-gray-700' : 'cursor-default opacity-70'}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 truncate dark:text-white">${escapeHtml(device.name || `Unidad ${device.id}`)}</p>
                                        <p class="mt-1 text-xs text-gray-500 truncate dark:text-gray-400">${escapeHtml(device.model || device.uniqueId || 'Sin modelo registrado')}</p>
                                    </div>
                                    <span class="shrink-0 px-2 py-1 text-xs font-semibold rounded-full ${statusClasses(device.status)}">${escapeHtml(statusLabel(device.status))}</span>
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
                elements.deviceList.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-device-id]');
                    if (!button || button.disabled) return;
                    const marker = markers.get(String(button.dataset.deviceId));
                    if (!marker) return;
                    map.setView(marker.getLatLng(), 16, { animate: true });
                    marker.openPopup();
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
