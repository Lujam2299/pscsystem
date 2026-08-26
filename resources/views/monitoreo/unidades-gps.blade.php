<x-app-layout>
    <x-navbar />

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css">
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
            .gps-marker--overspeed { background: #dc2626; animation: pulse 1s infinite; }
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
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-7">
                    <button type="button" data-quick-status="all" class="p-4 text-left border border-gray-200 rounded-xl bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40">
                        <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Unidades visibles</p>
                        <p id="gps-visible-count" class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">0</p>
                    </button>
                    <button type="button" data-quick-status="moving" class="p-4 text-left border border-emerald-200 rounded-xl bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-900/20">
                        <p class="text-xs font-semibold tracking-wide uppercase text-emerald-700 dark:text-emerald-300">En movimiento</p>
                        <p id="gps-moving-count" class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">0</p>
                    </button>
                    <button type="button" data-quick-status="idle" class="p-4 text-left border border-amber-200 rounded-xl bg-amber-50 dark:border-amber-900 dark:bg-amber-900/20">
                        <p class="text-xs font-semibold tracking-wide text-amber-700 uppercase dark:text-amber-300">Detenidas</p>
                        <p id="gps-idle-count" class="mt-1 text-2xl font-bold text-amber-700 dark:text-amber-300">0</p>
                    </button>
                    <button type="button" data-quick-status="parked" class="p-4 text-left border border-blue-200 rounded-xl bg-blue-50 dark:border-blue-900 dark:bg-blue-900/20">
                        <p class="text-xs font-semibold tracking-wide text-blue-700 uppercase dark:text-blue-300">Estacionadas</p>
                        <p id="gps-parked-count" class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-300">0</p>
                    </button>
                    <button type="button" data-quick-status="offline" class="p-4 text-left bg-gray-100 border border-gray-300 rounded-xl dark:border-gray-600 dark:bg-gray-700">
                        <p class="text-xs font-semibold tracking-wide text-gray-600 uppercase dark:text-gray-300">Desconectadas</p>
                        <p id="gps-offline-count" class="mt-1 text-2xl font-bold text-gray-700 dark:text-gray-200">0</p>
                    </button>
                    <button type="button" data-quick-status="unknown" class="p-4 text-left border border-red-200 rounded-xl bg-red-50 dark:border-red-900 dark:bg-red-900/20">
                        <p class="text-xs font-semibold tracking-wide text-red-700 uppercase dark:text-red-300">Desconocidas</p>
                        <p id="gps-unknown-count" class="mt-1 text-2xl font-bold text-red-700 dark:text-red-300">0</p>
                    </button>
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
                            <option value="overspeed">Exceso de velocidad</option>
                            <option value="idle">Detenida con ignición</option>
                            <option value="parked">Estacionada</option>
                            <option value="offline">Fuera de línea</option>
                            <option value="unknown">Desconocido</option>
                        </select>
                    </label>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200 lg:w-44">
                        Ignición
                        <select id="gps-ignition-filter" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="all">Todas</option>
                            <option value="on">Encendida</option>
                            <option value="off">Apagada</option>
                            <option value="unknown">Sin información</option>
                        </select>
                    </label>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200 lg:w-44">
                        Etiquetas
                        <select id="gps-label-mode" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="automatic">Automáticas</option>
                            <option value="all">Mostrar todas</option>
                            <option value="none">Ocultar todas</option>
                        </select>
                    </label>
                    <label class="inline-flex items-center gap-2 pb-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                        <input id="gps-alert-filter" type="checkbox" class="text-red-600 border-gray-300 rounded focus:ring-red-500">
                        Solo con alertas recientes
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

                <div class="flex flex-col gap-3 p-4 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800 lg:flex-row lg:items-end">
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                        <input id="gps-geofence-toggle" type="checkbox" checked class="text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        Mostrar geocercas
                    </label>
                    <label class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                        Geocerca destacada
                        <select id="gps-geofence-select" class="w-full mt-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">Todas las geocercas</option>
                        </select>
                    </label>
                    <p id="gps-geofence-units" class="text-sm text-gray-500 dark:text-gray-400">Cargando geocercas…</p>
                    @can('manage-traccar-monitoring')
                        <div class="flex gap-2">
                            <button id="gps-geofence-new" type="button" class="px-3 py-2 text-sm font-semibold text-white bg-purple-600 rounded-lg">Nueva</button>
                            <button id="gps-geofence-edit" type="button" class="px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:text-white">Editar</button>
                            <button id="gps-geofence-delete" type="button" class="px-3 py-2 text-sm font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg">Eliminar</button>
                        </div>
                    @endcan
                </div>

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

                <section class="overflow-hidden bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-3 px-5 py-4 border-b border-gray-200 dark:border-gray-700 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Centro de alertas GPS</h2>
                                <span id="gps-alert-unread" class="hidden px-2 py-1 text-xs font-bold text-white bg-red-600 rounded-full">0</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Eventos de las últimas 24 horas</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                <input id="gps-alert-sound" type="checkbox" class="text-red-600 border-gray-300 rounded focus:ring-red-500">
                                Sonido para alertas críticas
                            </label>
                            <button id="gps-alert-read-all" type="button" class="px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">Marcar todas como leídas</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 md:grid-cols-3">
                        <select id="gps-alert-priority" class="border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">Todas las prioridades</option>
                            <option value="critical">Críticas</option><option value="high">Altas</option><option value="medium">Medias</option><option value="info">Informativas</option>
                        </select>
                        <select id="gps-alert-read-filter" class="border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">Leídas y pendientes</option><option value="unread">Pendientes</option><option value="read">Leídas</option>
                        </select>
                        <input id="gps-alert-search" type="search" placeholder="Buscar unidad o evento" class="border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div id="gps-alert-list" class="overflow-y-auto divide-y divide-gray-100 max-h-96 dark:divide-gray-700">
                        <p class="p-6 text-sm text-center text-gray-500 dark:text-gray-400">Cargando alertas…</p>
                    </div>
                </section>

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
                    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Dirección aproximada</p>
                        <p id="gps-detail-address" class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">Selecciona una unidad para consultar su dirección.</p>
                    </div>
                    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col gap-3 md:flex-row md:items-end">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Límite de velocidad (km/h)
                                <input id="gps-speed-limit" type="number" min="10" max="200" step="1" class="block mt-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            </label>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Tolerancia (segundos)
                                <input id="gps-speed-tolerance" type="number" min="0" max="600" step="5" value="30" class="block mt-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            </label>
                            <label class="inline-flex items-center gap-2 pb-2 text-sm text-gray-700 dark:text-gray-200"><input id="gps-speed-active" type="checkbox" checked> Activo</label>
                            @can('manage-traccar-monitoring')
                                <button id="gps-speed-save" type="button" class="px-4 py-2 text-sm font-semibold text-white bg-orange-600 rounded-lg">Guardar límite</button>
                            @endcan
                            <span id="gps-speed-status" class="pb-2 text-sm text-gray-500 dark:text-gray-400"></span>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Reportes operativos GPS</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Resumen por unidad, viajes, paradas, desconexiones y excesos de velocidad.</p>
                    </div>
                    <form id="gps-report-form" class="grid grid-cols-1 gap-3 p-5 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Unidad
                            <select id="gps-report-device" class="w-full mt-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white"><option value="">Todas las unidades</option></select>
                        </label>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Desde<input id="gps-report-from" type="datetime-local" required class="w-full mt-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Hasta<input id="gps-report-to" type="datetime-local" required class="w-full mt-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg">Generar</button>
                            <button id="gps-report-xlsx" type="button" class="px-3 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg">XLSX</button>
                            <button id="gps-report-pdf" type="button" class="px-3 py-2 text-sm font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg">PDF</button>
                        </div>
                    </form>
                    <div id="gps-report-error" class="hidden px-5 pb-4 text-sm text-red-600"></div>
                    <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="text-left text-gray-600 bg-gray-50 dark:bg-gray-900 dark:text-gray-300"><tr><th class="p-3">Unidad</th><th class="p-3">Km</th><th class="p-3">Máx.</th><th class="p-3">Prom.</th><th class="p-3">Viajes</th><th class="p-3">Paradas</th><th class="p-3">Detenida</th><th class="p-3">Descon.</th><th class="p-3">Excesos</th><th class="p-3">Límite</th></tr></thead><tbody id="gps-report-body" class="divide-y divide-gray-100 dark:divide-gray-700"><tr><td colspan="10" class="p-6 text-center text-gray-500">Genera un reporte para consultar resultados.</td></tr></tbody></table></div>
                </section>

                @can('manage-traccar-monitoring')
                    <div id="gps-geofence-dialog" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4 bg-slate-950/60">
                        <form id="gps-geofence-form" class="w-full max-w-lg p-6 bg-white rounded-2xl shadow-2xl dark:bg-gray-800">
                            <h2 id="gps-geofence-dialog-title" class="text-xl font-bold text-gray-900 dark:text-white">Nueva geocerca</h2>
                            <input id="gps-geofence-id" type="hidden"><input id="gps-geofence-area" type="hidden">
                            <label class="block mt-4 text-sm font-medium text-gray-700 dark:text-gray-200">Nombre<input id="gps-geofence-name" required maxlength="128" class="w-full mt-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                            <label class="block mt-3 text-sm font-medium text-gray-700 dark:text-gray-200">Descripción<textarea id="gps-geofence-description" maxlength="500" rows="3" class="w-full mt-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea></label>
                            <label class="block mt-3 text-sm font-medium text-gray-700 dark:text-gray-200">Color<input id="gps-geofence-color" type="color" value="#7c3aed" class="block mt-1 h-10 w-20"></label>
                            <p id="gps-geofence-form-error" class="hidden mt-3 text-sm text-red-600"></p>
                            <div class="flex justify-end gap-2 mt-5"><button id="gps-geofence-cancel" type="button" class="px-4 py-2 border rounded-lg dark:text-white">Cancelar</button><button type="submit" class="px-4 py-2 font-semibold text-white bg-purple-600 rounded-lg">Guardar en Traccar</button></div>
                        </form>
                    </div>
                @endcan

                <section class="overflow-hidden bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Historial y reproducción</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Selecciona una unidad en el mapa y consulta hasta siete días de recorrido.</p>
                    </div>
                    <form id="gps-history-form" class="grid grid-cols-1 gap-3 p-5 md:grid-cols-[1fr_1fr_auto] md:items-end">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Desde
                            <input id="gps-history-from" type="datetime-local" required class="w-full mt-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        </label>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Hasta
                            <input id="gps-history-to" type="datetime-local" required class="w-full mt-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        </label>
                        <div class="flex gap-2">
                            <button id="gps-history-load" type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                                <i class="ti ti-route"></i> Consultar recorrido
                            </button>
                            <button id="gps-history-close" type="button" class="hidden px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">Volver al tiempo real</button>
                        </div>
                    </form>
                    <div id="gps-history-error" class="hidden px-5 pb-4 text-sm text-red-700 dark:text-red-300"></div>
                    <div id="gps-history-content" class="hidden border-t border-gray-200 dark:border-gray-700">
                        <div id="gps-history-summary" class="grid grid-cols-2 gap-px bg-gray-200 dark:bg-gray-700 md:grid-cols-5"></div>
                        <div class="flex flex-wrap items-center gap-3 px-5 py-4">
                            <button id="gps-play" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg"><i class="ti ti-player-play"></i> Reproducir</button>
                            <button id="gps-restart" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"><i class="ti ti-reload"></i> Reiniciar</button>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Velocidad
                                <select id="gps-play-speed" class="ml-1 border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                    <option value="1000">1×</option><option value="500">2×</option><option value="200">5×</option><option value="100">10×</option>
                                </select>
                            </label>
                            <input id="gps-history-progress" type="range" min="0" max="0" value="0" class="flex-1 min-w-48">
                            <span id="gps-history-time" class="text-sm text-gray-600 dark:text-gray-300">Sin reproducción</span>
                        </div>
                        <div class="grid grid-cols-1 border-t border-gray-200 dark:border-gray-700 lg:grid-cols-2">
                            <div class="p-5 border-b border-gray-200 dark:border-gray-700 lg:border-b-0 lg:border-r">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Paradas</h3>
                                <div id="gps-history-stops" class="mt-3 overflow-y-auto space-y-2 max-h-64"></div>
                            </div>
                            <div class="p-5">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Eventos</h3>
                                <div id="gps-history-events" class="mt-3 overflow-y-auto space-y-2 max-h-64"></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </x-livewire.monitoreo-layout>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
        <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const endpoints = {
                    data: @json(route('monitoreo.unidades-gps.data')),
                    history: @json(route('monitoreo.unidades-gps.history')),
                    address: @json(route('monitoreo.unidades-gps.address')),
                    geofences: @json(route('monitoreo.unidades-gps.geofences')),
                    alerts: @json(route('monitoreo.unidades-gps.alerts')),
                    readAlerts: @json(route('monitoreo.unidades-gps.alerts.read')),
                    reports: @json(route('monitoreo.unidades-gps.reports')),
                    reportXlsx: @json(route('monitoreo.unidades-gps.reports.xlsx')),
                    reportPdf: @json(route('monitoreo.unidades-gps.reports.pdf')),
                    speedLimits: @json(route('monitoreo.unidades-gps.speed-limits')),
                    speedLimitSave: @json(route('monitoreo.unidades-gps.speed-limits.save', 0)),
                    geofenceCreate: @json(route('monitoreo.unidades-gps.geofences.create')),
                    geofenceUpdate: @json(route('monitoreo.unidades-gps.geofences.update', 0)),
                    geofenceDelete: @json(route('monitoreo.unidades-gps.geofences.delete', 0)),
                    socketToken: @json(route('monitoreo.unidades-gps.socket-token')),
                    websocket: @json($websocketUrl),
                };

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                const devices = new Map();
                const positions = new Map();
                const markers = new Map();
                const recentEvents = new Map();
                const addresses = new Map();
                const gpsAlerts = new Map();
                const localSpeedAlerts = new Map();
                const geofences = new Map();
                const speedLimits = new Map();
                const speedExceededSince = new Map();
                const canManageGps = @json(auth()->user()->can('manage-traccar-monitoring'));
                const markerCluster = L.markerClusterGroup({
                    disableClusteringAtZoom: 15,
                    maxClusterRadius: 48,
                    showCoverageOnHover: false,
                    spiderfyOnMaxZoom: true,
                });
                const focusLayer = L.layerGroup();
                const historyLayer = L.layerGroup();
                const geofenceLayer = L.layerGroup();
                const editableGeofenceLayer = L.featureGroup();
                let socket = null;
                let reconnectTimer = null;
                let reconnectAttempts = 0;
                let closingPage = false;
                let selectedDeviceId = null;
                let followedDeviceId = null;
                let historyPositions = [];
                let playbackIndex = 0;
                let playbackTimer = null;
                let playbackMarker = null;
                let alertRefreshTimer = null;
                let alertPollTimer = null;
                let lastCriticalAlertId = null;
                let pendingGeofenceLayer = null;

                const map = L.map('traccar-map', {
                    center: [25.6866, -100.3161],
                    zoom: 11,
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);
                markerCluster.addTo(map);
                focusLayer.addTo(map);
                historyLayer.addTo(map);
                geofenceLayer.addTo(map);
                if (canManageGps) editableGeofenceLayer.addTo(map);

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
                    detailAddress: document.getElementById('gps-detail-address'),
                    detailIdentity: document.getElementById('gps-detail-identity'),
                    detailName: document.getElementById('gps-detail-name'),
                    detailStatus: document.getElementById('gps-detail-status'),
                    closeDetail: document.getElementById('gps-close-detail'),
                    offlineCount: document.getElementById('gps-offline-count'),
                    unknownCount: document.getElementById('gps-unknown-count'),
                    ignitionFilter: document.getElementById('gps-ignition-filter'),
                    labelMode: document.getElementById('gps-label-mode'),
                    alertFilter: document.getElementById('gps-alert-filter'),
                    alertList: document.getElementById('gps-alert-list'),
                    alertPriority: document.getElementById('gps-alert-priority'),
                    alertReadFilter: document.getElementById('gps-alert-read-filter'),
                    alertSearch: document.getElementById('gps-alert-search'),
                    alertSound: document.getElementById('gps-alert-sound'),
                    alertReadAll: document.getElementById('gps-alert-read-all'),
                    alertUnread: document.getElementById('gps-alert-unread'),
                    geofenceToggle: document.getElementById('gps-geofence-toggle'),
                    geofenceSelect: document.getElementById('gps-geofence-select'),
                    geofenceUnits: document.getElementById('gps-geofence-units'),
                    geofenceNew: document.getElementById('gps-geofence-new'),
                    geofenceEdit: document.getElementById('gps-geofence-edit'),
                    geofenceDelete: document.getElementById('gps-geofence-delete'),
                    geofenceDialog: document.getElementById('gps-geofence-dialog'),
                    geofenceForm: document.getElementById('gps-geofence-form'),
                    geofenceDialogTitle: document.getElementById('gps-geofence-dialog-title'),
                    geofenceId: document.getElementById('gps-geofence-id'),
                    geofenceArea: document.getElementById('gps-geofence-area'),
                    geofenceName: document.getElementById('gps-geofence-name'),
                    geofenceDescription: document.getElementById('gps-geofence-description'),
                    geofenceColor: document.getElementById('gps-geofence-color'),
                    geofenceFormError: document.getElementById('gps-geofence-form-error'),
                    geofenceCancel: document.getElementById('gps-geofence-cancel'),
                    speedLimit: document.getElementById('gps-speed-limit'),
                    speedTolerance: document.getElementById('gps-speed-tolerance'),
                    speedActive: document.getElementById('gps-speed-active'),
                    speedSave: document.getElementById('gps-speed-save'),
                    speedStatus: document.getElementById('gps-speed-status'),
                    reportForm: document.getElementById('gps-report-form'),
                    reportDevice: document.getElementById('gps-report-device'),
                    reportFrom: document.getElementById('gps-report-from'),
                    reportTo: document.getElementById('gps-report-to'),
                    reportXlsx: document.getElementById('gps-report-xlsx'),
                    reportPdf: document.getElementById('gps-report-pdf'),
                    reportError: document.getElementById('gps-report-error'),
                    reportBody: document.getElementById('gps-report-body'),
                    historyForm: document.getElementById('gps-history-form'),
                    historyFrom: document.getElementById('gps-history-from'),
                    historyTo: document.getElementById('gps-history-to'),
                    historyLoad: document.getElementById('gps-history-load'),
                    historyClose: document.getElementById('gps-history-close'),
                    historyError: document.getElementById('gps-history-error'),
                    historyContent: document.getElementById('gps-history-content'),
                    historySummary: document.getElementById('gps-history-summary'),
                    historyStops: document.getElementById('gps-history-stops'),
                    historyEvents: document.getElementById('gps-history-events'),
                    historyProgress: document.getElementById('gps-history-progress'),
                    historyTime: document.getElementById('gps-history-time'),
                    play: document.getElementById('gps-play'),
                    restart: document.getElementById('gps-restart'),
                    playSpeed: document.getElementById('gps-play-speed'),
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
                    if (position?.speed === null || position?.speed === undefined || position?.speed === '') return null;
                    const speed = Number(position?.speed);
                    return Number.isFinite(speed) ? speed * 1.852 : null;
                };

                const operationalStatus = (device, position) => {
                    if (device?.status === 'offline') return 'offline';
                    if (device?.status !== 'online') return 'unknown';

                    const speed = speedKmhValue(position);
                    const limit = speedLimits.get(String(device.id));
                    if (limit?.active && speed !== null && speed > Number(limit.speed_limit_kmh)) {
                        const key = String(device.id);
                        if (!speedExceededSince.has(key)) speedExceededSince.set(key, Date.now());
                        if (Date.now() - speedExceededSince.get(key) >= Number(limit.tolerance_seconds || 0) * 1000) return 'overspeed';
                    } else {
                        speedExceededSince.delete(String(device.id));
                    }
                    const motion = booleanValue(positionAttribute(position, 'motion'));
                    const ignition = booleanValue(positionAttribute(position, 'ignition'));

                    if (motion === true || (speed !== null && speed > 1)) return 'moving';
                    if (ignition === true) return 'idle';
                    if (ignition === false) return 'parked';
                    return 'unknown';
                };

                const statusLabel = (status) => ({
                    overspeed: 'Exceso de velocidad',
                    moving: 'En movimiento',
                    idle: 'Detenida con ignición',
                    parked: 'Estacionada',
                    offline: 'Fuera de línea',
                    unknown: 'Desconocido',
                })[status] ?? 'Desconocido';

                const statusClasses = (status) => ({
                    overspeed: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
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

                const positionAge = (device, position) => {
                    const value = positionDate(device, position);
                    const date = value ? new Date(value) : null;
                    if (!date || Number.isNaN(date.getTime())) return { label: 'Sin fecha', stale: true };

                    const seconds = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
                    if (seconds < 60) return { label: `hace ${seconds} s`, stale: false };
                    const minutes = Math.floor(seconds / 60);
                    if (minutes < 60) return { label: `hace ${minutes} min`, stale: minutes >= 10 };
                    const hours = Math.floor(minutes / 60);
                    if (hours < 24) return { label: `hace ${hours} h`, stale: true };
                    return { label: `hace ${Math.floor(hours / 24)} d`, stale: true };
                };

                const hasRecentAlert = (deviceId) => {
                    const eventTime = recentEvents.get(String(deviceId));
                    return eventTime ? Date.now() - eventTime < 30 * 60 * 1000 : false;
                };

                const eventLabel = (type) => ({
                    alarm: 'Alarma',
                    deviceOffline: 'Unidad desconectada',
                    deviceOnline: 'Unidad conectada',
                    geofenceEnter: 'Entrada a geocerca',
                    geofenceExit: 'Salida de geocerca',
                    ignitionOn: 'Ignición encendida',
                    ignitionOff: 'Ignición apagada',
                    overspeed: 'Exceso de velocidad',
                    deviceMoving: 'Movimiento',
                    deviceStopped: 'Detención',
                })[type] ?? type ?? 'Evento';

                const formatNumber = (value, suffix = '', decimals = 0) => {
                    const number = Number(value);
                    return Number.isFinite(number) ? `${number.toFixed(decimals)}${suffix}` : 'No disponible';
                };

                const ignitionLabel = (position) => {
                    const ignition = booleanValue(positionAttribute(position, 'ignition'));
                    return ignition === null ? 'No disponible' : (ignition ? 'Encendida' : 'Apagada');
                };

                const courseData = (position) => {
                    if (position?.course === null || position?.course === undefined || position?.course === '') return null;
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
                    const mode = elements.labelMode.value;
                    const showLabel = String(device.id) === selectedDeviceId
                        || mode === 'all'
                        || (mode === 'automatic' && map.getZoom() >= 12);

                    return L.divIcon({
                    className: '',
                    html: `<div class="gps-marker gps-marker--${status} ${following ? 'gps-following' : ''}">
                        <span class="gps-marker__direction" style="transform: rotate(${rotation}deg)" title="${escapeHtml(course ? `${course.direction} (${course.degrees.toFixed(0)}°)` : 'Rumbo no disponible')}">${vehicleSvg}</span>
                        ${showLabel ? `<span class="gps-marker-label">${name}</span>` : ''}
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
                    if (status === 'overspeed') {
                        const alertKey = `speed-${key}`;
                        if (!localSpeedAlerts.has(alertKey)) {
                            localSpeedAlerts.set(alertKey, {
                                id: alertKey, device_id: Number(device.id), type: 'overspeed', priority: 'high',
                                event_time: new Date().toISOString(), is_read: false, local: true,
                            });
                        }
                        recentEvents.set(key, Date.now());
                    }

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
                    const age = positionAge(device, position);

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
                        detailItem('Última posición', `${age.label}${age.stale ? ' · Posición antigua' : ''}`, 'clock'),
                        detailItem('Batería', battery === null ? 'No disponible' : formatNumber(battery, '%', 0), 'battery'),
                        detailItem('Alimentación', power === null ? 'No disponible' : formatNumber(power, ' V', 1), 'bolt'),
                        detailItem('Odómetro', totalDistance !== null && Number.isFinite(totalDistance) ? `${(totalDistance / 1000).toFixed(1)} km` : 'No disponible', 'road'),
                        detailItem('Coordenadas', Number.isFinite(Number(position?.latitude)) && Number.isFinite(Number(position?.longitude)) ? `${Number(position.latitude).toFixed(5)}, ${Number(position.longitude).toFixed(5)}` : 'No disponible', 'map-pin'),
                        detailItem('Placa / económico', plate ?? 'No disponible', 'id-badge-2'),
                        detailItem('Conductor', driver ?? 'No disponible', 'user'),
                        detailItem('Satélites', satellites ?? 'No disponible', 'satellite'),
                        detailItem('Identificador GPS', device.uniqueId ?? 'No disponible', 'device-mobile'),
                    ].join('');

                    const addressKey = Number.isFinite(Number(position?.latitude)) && Number.isFinite(Number(position?.longitude))
                        ? `${Number(position.latitude).toFixed(5)},${Number(position.longitude).toFixed(5)}`
                        : null;
                    elements.detailAddress.textContent = position?.address
                        || (addressKey ? addresses.get(addressKey) : null)
                        || (addressKey ? 'Consultando dirección…' : 'Dirección no disponible');
                    const configuredLimit = speedLimits.get(String(device.id));
                    elements.speedLimit.value = configuredLimit?.speed_limit_kmh ?? '';
                    elements.speedTolerance.value = configuredLimit?.tolerance_seconds ?? 30;
                    elements.speedActive.checked = configuredLimit?.active ?? true;
                    elements.speedStatus.textContent = configuredLimit
                        ? `Configurado en ${Number(configuredLimit.speed_limit_kmh).toFixed(0)} km/h`
                        : 'Sin límite configurado';
                };

                const resolveSelectedAddress = async () => {
                    const requestedId = selectedDeviceId;
                    const position = requestedId ? positions.get(requestedId) : null;
                    const latitude = Number(position?.latitude);
                    const longitude = Number(position?.longitude);
                    if (!Number.isFinite(latitude) || !Number.isFinite(longitude) || position?.address) return;

                    const addressKey = `${latitude.toFixed(5)},${longitude.toFixed(5)}`;
                    if (addresses.has(addressKey)) return;

                    try {
                        const url = new URL(endpoints.address, window.location.origin);
                        url.searchParams.set('latitude', latitude);
                        url.searchParams.set('longitude', longitude);
                        const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                        const payload = await response.json();
                        addresses.set(addressKey, response.ok && payload.address ? payload.address : 'Dirección no disponible');
                    } catch (error) {
                        addresses.set(addressKey, 'Dirección no disponible');
                    }

                    if (selectedDeviceId === requestedId) renderDetail();
                };

                const selectDevice = (deviceId) => {
                    selectedDeviceId = String(deviceId);
                    updateMarker(selectedDeviceId);
                    renderDetail();
                    resolveSelectedAddress();
                    render();
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
                    render();
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
                    render();
                };

                const render = () => {
                    const query = elements.search.value.trim().toLocaleLowerCase('es-MX');
                    const selectedStatus = elements.statusFilter.value;
                    const selectedIgnition = elements.ignitionFilter.value;
                    const visibleDevices = [...devices.values()]
                        .filter((device) => {
                            const position = positions.get(String(device.id));
                            const status = operationalStatus(device, position);
                            const ignition = booleanValue(positionAttribute(position, 'ignition'));
                            const ignitionMatches = selectedIgnition === 'all'
                                || (selectedIgnition === 'on' && ignition === true)
                                || (selectedIgnition === 'off' && ignition === false)
                                || (selectedIgnition === 'unknown' && ignition === null);
                            const searchable = `${device.name ?? ''} ${device.uniqueId ?? ''} ${device.model ?? ''} ${device.phone ?? ''} ${deviceAttribute(device, 'plate', 'registration', 'vehicleNumber') ?? ''}`.toLocaleLowerCase('es-MX');
                            return (selectedStatus === 'all' || status === selectedStatus)
                                && ignitionMatches
                                && (!elements.alertFilter.checked || hasRecentAlert(device.id))
                                && (query === '' || searchable.includes(query));
                        })
                        .sort((left, right) => String(left.name ?? '').localeCompare(String(right.name ?? ''), 'es-MX'));

                    const visibleIds = new Set(visibleDevices.map((device) => String(device.id)));
                    markers.forEach((marker, deviceId) => {
                        if (visibleIds.has(deviceId)) {
                            const focused = deviceId === selectedDeviceId || deviceId === followedDeviceId;
                            if (focused) {
                                markerCluster.removeLayer(marker);
                                if (!focusLayer.hasLayer(marker)) focusLayer.addLayer(marker);
                            } else {
                                focusLayer.removeLayer(marker);
                                if (!markerCluster.hasLayer(marker)) markerCluster.addLayer(marker);
                            }
                        } else {
                            markerCluster.removeLayer(marker);
                            focusLayer.removeLayer(marker);
                        }
                    });

                    elements.visibleCount.textContent = visibleDevices.length;
                    elements.movingCount.textContent = visibleDevices.filter((device) => operationalStatus(device, positions.get(String(device.id))) === 'moving').length;
                    elements.idleCount.textContent = visibleDevices.filter((device) => operationalStatus(device, positions.get(String(device.id))) === 'idle').length;
                    elements.parkedCount.textContent = visibleDevices.filter((device) => operationalStatus(device, positions.get(String(device.id))) === 'parked').length;
                    elements.offlineCount.textContent = visibleDevices.filter((device) => operationalStatus(device, positions.get(String(device.id))) === 'offline').length;
                    elements.unknownCount.textContent = visibleDevices.filter((device) => operationalStatus(device, positions.get(String(device.id))) === 'unknown').length;

                    if (visibleDevices.length === 0) {
                        elements.deviceList.innerHTML = '<div class="p-6 text-sm text-center text-gray-500 dark:text-gray-400">No hay unidades que coincidan con los filtros.</div>';
                        return;
                    }

                    elements.deviceList.innerHTML = visibleDevices.map((device) => {
                        const position = positions.get(String(device.id));
                        const hasPosition = position && Number.isFinite(Number(position.latitude)) && Number.isFinite(Number(position.longitude));
                        const status = operationalStatus(device, position);
                        const speed = speedKmhValue(position);
                        const age = positionAge(device, position);
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
                                <p class="mt-2 text-xs ${age.stale ? 'font-semibold text-red-600 dark:text-red-300' : 'text-gray-500 dark:text-gray-400'}">${escapeHtml(age.label)}${hasRecentAlert(device.id) ? ' · ⚠ Alerta reciente' : ''}</p>
                                ${hasPosition ? '' : '<p class="mt-1 text-xs text-amber-700 dark:text-amber-300">Sin posición disponible</p>'}
                            </button>
                        `;
                    }).join('');
                };

                const fitVisibleMarkers = () => {
                    const layers = [...markerCluster.getLayers(), ...focusLayer.getLayers()];
                    if (layers.length === 0) return;
                    if (layers.length === 1) {
                        map.setView(layers[0].getLatLng(), 15);
                        return;
                    }
                    map.fitBounds(L.featureGroup(layers).getBounds(), { padding: [35, 35], maxZoom: 16 });
                };

                const pointInPolygon = (latitude, longitude, points) => {
                    let inside = false;
                    for (let current = 0, previous = points.length - 1; current < points.length; previous = current++) {
                        const [currentLat, currentLng] = points[current];
                        const [previousLat, previousLng] = points[previous];
                        const intersects = ((currentLng > longitude) !== (previousLng > longitude))
                            && (latitude < (previousLat - currentLat) * (longitude - currentLng) / ((previousLng - currentLng) || Number.EPSILON) + currentLat);
                        if (intersects) inside = !inside;
                    }
                    return inside;
                };

                const parseCoordinatePairs = (value) => value.split(',').map((pair) => {
                    const [latitude, longitude] = pair.trim().split(/\s+/).map(Number);
                    return [latitude, longitude];
                }).filter(([latitude, longitude]) => Number.isFinite(latitude) && Number.isFinite(longitude));

                const createGeofenceShape = (geofence) => {
                    const area = String(geofence.area || '').trim();
                    const circle = area.match(/^CIRCLE\s*\(\s*(-?[\d.]+)\s+(-?[\d.]+)\s*,\s*([\d.]+)\s*\)$/i);
                    const style = { color: '#7c3aed', fillColor: '#8b5cf6', fillOpacity: .12, weight: 2 };
                    if (circle) {
                        const center = [Number(circle[1]), Number(circle[2])];
                        const radius = Number(circle[3]);
                        return {
                            layer: L.circle(center, { ...style, radius }),
                            contains: (latitude, longitude) => map.distance(center, [latitude, longitude]) <= radius,
                        };
                    }

                    const polygon = area.match(/^POLYGON\s*\(\((.+)\)\)$/i);
                    if (polygon) {
                        const points = parseCoordinatePairs(polygon[1]);
                        return {
                            layer: L.polygon(points, style),
                            contains: (latitude, longitude) => pointInPolygon(latitude, longitude, points),
                        };
                    }

                    const line = area.match(/^LINESTRING\s*\((.+)\)$/i);
                    if (line) {
                        return { layer: L.polyline(parseCoordinatePairs(line[1]), { color: '#7c3aed', weight: 4 }), contains: () => false };
                    }
                    return null;
                };

                const renderGeofenceUnits = () => {
                    const selected = geofences.get(elements.geofenceSelect.value);
                    if (!selected) {
                        elements.geofenceUnits.textContent = `${geofences.size} geocerca${geofences.size === 1 ? '' : 's'} disponible${geofences.size === 1 ? '' : 's'}`;
                        return;
                    }
                    const inside = [...devices.values()].filter((device) => {
                        const position = positions.get(String(device.id));
                        const latitude = Number(position?.latitude);
                        const longitude = Number(position?.longitude);
                        return Number.isFinite(latitude) && Number.isFinite(longitude) && selected.contains(latitude, longitude);
                    });
                    elements.geofenceUnits.textContent = `${inside.length} unidad${inside.length === 1 ? '' : 'es'} dentro de ${selected.data.name}`;
                };

                const selectGeofence = (geofenceId, fit = true) => {
                    const selectedId = String(geofenceId || '');
                    elements.geofenceSelect.value = selectedId;
                    geofences.forEach((entry, id) => {
                        if (!entry.layer.setStyle) return;
                        entry.layer.setStyle(id === selectedId
                            ? { color: '#dc2626', fillColor: '#ef4444', fillOpacity: .2, weight: 4 }
                            : { color: '#7c3aed', fillColor: '#8b5cf6', fillOpacity: .12, weight: 2 });
                    });
                    const selected = geofences.get(selectedId);
                    if (fit && selected?.layer.getBounds) map.fitBounds(selected.layer.getBounds(), { padding: [35, 35], maxZoom: 16 });
                    renderGeofenceUnits();
                };

                const loadGeofences = async () => {
                    try {
                        const response = await fetch(endpoints.geofences, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'No fue posible consultar las geocercas.');
                        geofenceLayer.clearLayers();
                        editableGeofenceLayer.clearLayers();
                        geofences.clear();
                        elements.geofenceSelect.innerHTML = '<option value="">Todas las geocercas</option>';
                        (payload.geofences || []).forEach((geofence) => {
                            const shape = createGeofenceShape(geofence);
                            if (!shape || geofence.id === undefined) return;
                            const id = String(geofence.id);
                            shape.layer.bindTooltip(escapeHtml(geofence.name || `Geocerca ${id}`));
                            shape.layer._gpsGeofenceId = id;
                            shape.layer.addTo(canManageGps ? editableGeofenceLayer : geofenceLayer);
                            geofences.set(id, { ...shape, data: geofence });
                            elements.geofenceSelect.insertAdjacentHTML('beforeend', `<option value="${escapeHtml(id)}">${escapeHtml(geofence.name || `Geocerca ${id}`)}</option>`);
                        });
                        renderGeofenceUnits();
                    } catch (error) {
                        elements.geofenceUnits.textContent = error.message || 'Geocercas no disponibles';
                    }
                };

                const loadSpeedLimits = async () => {
                    try {
                        const response = await fetch(endpoints.speedLimits, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'No fue posible consultar los límites.');
                        speedLimits.clear();
                        (payload.limits || []).forEach((limit) => speedLimits.set(String(limit.device_id), limit));
                        devices.forEach((device) => updateMarker(device.id));
                        render();
                        renderDetail();
                    } catch (error) {
                        elements.speedStatus.textContent = error.message || 'Límites no disponibles';
                    }
                };

                const saveSelectedSpeedLimit = async () => {
                    if (!selectedDeviceId) throw new Error('Selecciona primero una unidad.');
                    const endpoint = endpoints.speedLimitSave.replace(/\/0$/, `/${selectedDeviceId}`);
                    const response = await fetch(endpoint, {
                        method: 'PUT', credentials: 'same-origin',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({
                            speed_limit_kmh: Number(elements.speedLimit.value),
                            tolerance_seconds: Number(elements.speedTolerance.value),
                            active: elements.speedActive.checked,
                        }),
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(Object.values(payload.errors || {}).flat()[0] || payload.message || 'No fue posible guardar el límite.');
                    speedLimits.set(String(payload.limit.device_id), payload.limit);
                    updateMarker(selectedDeviceId);
                    renderDetail();
                    render();
                };

                const reportUrl = (base) => {
                    const url = new URL(base, window.location.origin);
                    if (elements.reportDevice.value) url.searchParams.append('device_ids[]', elements.reportDevice.value);
                    url.searchParams.set('from', new Date(elements.reportFrom.value).toISOString());
                    url.searchParams.set('to', new Date(elements.reportTo.value).toISOString());
                    return url;
                };

                const renderOperationalReport = (report) => {
                    const rows = Array.isArray(report.rows) ? report.rows : [];
                    elements.reportBody.innerHTML = rows.length ? rows.map((row) => `<tr class="dark:text-gray-200">
                        <td class="p-3 font-semibold">${escapeHtml(row.device_name)}</td><td class="p-3">${escapeHtml(row.distance_km)}</td><td class="p-3">${escapeHtml(row.max_speed_kmh)} km/h</td><td class="p-3">${escapeHtml(row.average_speed_kmh)} km/h</td>
                        <td class="p-3">${escapeHtml(row.trips_count)}</td><td class="p-3">${escapeHtml(row.stops_count)}</td><td class="p-3">${escapeHtml(row.stopped_hours)} h</td><td class="p-3">${escapeHtml(row.offline_events)}</td>
                        <td class="p-3 ${Number(row.overspeed_events) + Number(row.trips_over_limit) > 0 ? 'font-bold text-red-600' : ''}">${Number(row.overspeed_events) + Number(row.trips_over_limit)}</td><td class="p-3">${row.speed_limit_kmh ?? 'N/D'}</td>
                    </tr>`).join('') : '<tr><td colspan="10" class="p-6 text-center text-gray-500">No hay datos para el periodo seleccionado.</td></tr>';
                };

                const loadOperationalReport = async () => {
                    const response = await fetch(reportUrl(endpoints.reports), { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(Object.values(payload.errors || {}).flat()[0] || payload.message || 'No fue posible generar el reporte.');
                    renderOperationalReport(payload);
                };

                const layerToArea = (layer) => {
                    if (layer instanceof L.Circle) {
                        const center = layer.getLatLng();
                        return `CIRCLE (${center.lat.toFixed(6)} ${center.lng.toFixed(6)}, ${Math.round(layer.getRadius())})`;
                    }
                    const latLngs = layer.getLatLngs();
                    const points = Array.isArray(latLngs[0]) ? latLngs[0] : latLngs;
                    const coordinates = points.map((point) => `${point.lat.toFixed(6)} ${point.lng.toFixed(6)}`);
                    if (layer instanceof L.Polygon) {
                        if (coordinates[0] !== coordinates.at(-1)) coordinates.push(coordinates[0]);
                        return `POLYGON ((${coordinates.join(', ')}))`;
                    }
                    return `LINESTRING (${coordinates.join(', ')})`;
                };

                const openGeofenceDialog = (layer, geofenceId = '') => {
                    if (!canManageGps) return;
                    const existing = geofences.get(String(geofenceId));
                    pendingGeofenceLayer = layer;
                    elements.geofenceId.value = geofenceId;
                    elements.geofenceArea.value = layer ? layerToArea(layer) : existing?.data.area || '';
                    elements.geofenceName.value = existing?.data.name || '';
                    elements.geofenceDescription.value = existing?.data.description || '';
                    elements.geofenceColor.value = existing?.data.attributes?.color || '#7c3aed';
                    elements.geofenceDialogTitle.textContent = geofenceId ? 'Editar geocerca' : 'Nueva geocerca';
                    elements.geofenceFormError.classList.add('hidden');
                    elements.geofenceDialog.classList.remove('hidden');
                    elements.geofenceDialog.classList.add('flex');
                };

                const closeGeofenceDialog = () => {
                    elements.geofenceDialog?.classList.add('hidden');
                    elements.geofenceDialog?.classList.remove('flex');
                    pendingGeofenceLayer = null;
                };

                const initializeGeofenceDrawing = () => {
                    if (!canManageGps || !window.L?.Control?.Draw) return;
                    map.addControl(new L.Control.Draw({
                        position: 'topleft',
                        draw: { rectangle: false, marker: false, circlemarker: false },
                        edit: { featureGroup: editableGeofenceLayer, remove: false },
                    }));
                    map.on(L.Draw.Event.CREATED, (event) => openGeofenceDialog(event.layer));
                    map.on(L.Draw.Event.EDITED, (event) => event.layers.eachLayer((layer) => openGeofenceDialog(layer, layer._gpsGeofenceId || '')));
                };

                const priorityLabel = (priority) => ({ critical: 'Crítica', high: 'Alta', medium: 'Media', info: 'Informativa' })[priority] || 'Informativa';
                const priorityClasses = (priority) => ({
                    critical: 'border-red-500 bg-red-50 dark:bg-red-900/20',
                    high: 'border-orange-500 bg-orange-50 dark:bg-orange-900/20',
                    medium: 'border-amber-500 bg-amber-50 dark:bg-amber-900/20',
                    info: 'border-blue-400 bg-blue-50 dark:bg-blue-900/20',
                })[priority] || 'border-gray-300 bg-gray-50';

                const playCriticalSound = () => {
                    if (!elements.alertSound.checked) return;
                    try {
                        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                        const context = new AudioContextClass();
                        const oscillator = context.createOscillator();
                        const gain = context.createGain();
                        oscillator.frequency.value = 880;
                        gain.gain.setValueAtTime(.12, context.currentTime);
                        gain.gain.exponentialRampToValueAtTime(.001, context.currentTime + .45);
                        oscillator.connect(gain).connect(context.destination);
                        oscillator.start();
                        oscillator.stop(context.currentTime + .45);
                    } catch (error) {
                        elements.alertSound.checked = false;
                    }
                };

                const renderAlerts = () => {
                    const query = elements.alertSearch.value.trim().toLocaleLowerCase('es-MX');
                    const alerts = [...gpsAlerts.values(), ...localSpeedAlerts.values()]
                        .sort((left, right) => new Date(right.event_time) - new Date(left.event_time))
                        .filter((alert) => {
                        const device = devices.get(String(alert.device_id));
                        return query === '' || `${device?.name || ''} ${eventLabel(alert.type)} ${alert.attributes?.alarm || ''}`.toLocaleLowerCase('es-MX').includes(query);
                    });
                    if (alerts.length === 0) {
                        elements.alertList.innerHTML = '<p class="p-6 text-sm text-center text-gray-500 dark:text-gray-400">No hay alertas que coincidan con los filtros.</p>';
                        return;
                    }
                    elements.alertList.innerHTML = alerts.map((alert) => {
                        const device = devices.get(String(alert.device_id));
                        const geofence = geofences.get(String(alert.geofence_id));
                        return `<button type="button" data-gps-alert-id="${escapeHtml(alert.id)}" data-device-id="${escapeHtml(alert.device_id)}" data-geofence-id="${escapeHtml(alert.geofence_id || '')}" class="w-full p-4 text-left border-l-4 ${priorityClasses(alert.priority)} ${alert.is_read ? 'opacity-65' : ''}">
                            <div class="flex items-start justify-between gap-3"><div><p class="font-bold text-gray-900 dark:text-white">${escapeHtml(eventLabel(alert.type))}</p><p class="mt-1 text-sm text-gray-600 dark:text-gray-300">${escapeHtml(device?.name || `Unidad ${alert.device_id}`)}${geofence ? ` · ${escapeHtml(geofence.data.name)}` : ''}</p></div><span class="text-xs font-bold uppercase text-gray-600 dark:text-gray-300">${escapeHtml(priorityLabel(alert.priority))}</span></div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">${escapeHtml(formatDate(alert.event_time))}${alert.is_read ? ' · Leída' : ' · Pendiente'}</p>
                        </button>`;
                    }).join('');
                };

                const loadAlerts = async () => {
                    const url = new URL(endpoints.alerts, window.location.origin);
                    if (elements.alertPriority.value) url.searchParams.set('priority', elements.alertPriority.value);
                    if (elements.alertReadFilter.value) url.searchParams.set('read', elements.alertReadFilter.value);
                    const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(payload.message || 'No fue posible consultar las alertas.');
                    gpsAlerts.clear();
                    (payload.alerts || []).forEach((alert) => {
                        gpsAlerts.set(String(alert.id), alert);
                        const timestamp = new Date(alert.event_time).getTime();
                        if (Number.isFinite(timestamp)) recentEvents.set(String(alert.device_id), timestamp);
                    });
                    elements.alertUnread.textContent = payload.unread_count || 0;
                    elements.alertUnread.classList.toggle('hidden', !payload.unread_count);
                    const newestCritical = (payload.alerts || []).find((alert) => alert.priority === 'critical' && !alert.is_read);
                    if (lastCriticalAlertId !== null && newestCritical && String(newestCritical.id) !== lastCriticalAlertId) playCriticalSound();
                    lastCriticalAlertId = newestCritical ? String(newestCritical.id) : lastCriticalAlertId;
                    renderAlerts();
                };

                const markAlertsRead = async (ids = [], all = false) => {
                    const response = await fetch(endpoints.readAlerts, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(all ? { all: true } : { ids }),
                    });
                    if (!response.ok) throw new Error('No fue posible actualizar la alerta.');
                    await loadAlerts();
                };

                const mergeDevices = (items) => {
                    (Array.isArray(items) ? items : []).forEach((device) => {
                        if (device?.id === undefined) return;
                        const key = String(device.id);
                        devices.set(key, { ...(devices.get(key) ?? {}), ...device });
                        updateMarker(key);
                    });
                    elements.reportDevice.innerHTML = '<option value="">Todas las unidades</option>'
                        + [...devices.values()].sort((left, right) => String(left.name || '').localeCompare(String(right.name || ''), 'es-MX'))
                            .map((device) => `<option value="${escapeHtml(device.id)}">${escapeHtml(device.name || `Unidad ${device.id}`)}</option>`).join('');
                };

                const mergePositions = (items) => {
                    (Array.isArray(items) ? items : []).forEach((position) => {
                        if (position?.deviceId === undefined) return;
                        const key = String(position.deviceId);
                        positions.set(key, position);
                        updateMarker(key);
                    });
                    renderGeofenceUnits();
                    renderAlerts();
                };

                const mergeEvents = (items) => {
                    (Array.isArray(items) ? items : []).forEach((event) => {
                        if (event?.deviceId === undefined) return;
                        const timestamp = new Date(event.eventTime || event.serverTime || Date.now()).getTime();
                        if (Number.isFinite(timestamp)) recentEvents.set(String(event.deviceId), timestamp);
                    });
                    if (Array.isArray(items) && items.length > 0) {
                        if (alertRefreshTimer) clearTimeout(alertRefreshTimer);
                        alertRefreshTimer = setTimeout(() => loadAlerts().catch(() => {}), 1500);
                    }
                };

                const distanceBetween = (left, right) => {
                    const radius = 6371;
                    const toRadians = (degrees) => degrees * Math.PI / 180;
                    const latitudeDelta = toRadians(Number(right.latitude) - Number(left.latitude));
                    const longitudeDelta = toRadians(Number(right.longitude) - Number(left.longitude));
                    const a = Math.sin(latitudeDelta / 2) ** 2
                        + Math.cos(toRadians(Number(left.latitude))) * Math.cos(toRadians(Number(right.latitude)))
                        * Math.sin(longitudeDelta / 2) ** 2;
                    return 2 * radius * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                };

                const stopPlayback = () => {
                    if (playbackTimer) clearInterval(playbackTimer);
                    playbackTimer = null;
                    elements.play.innerHTML = '<i class="ti ti-player-play"></i> Reproducir';
                };

                const updatePlayback = (index) => {
                    if (historyPositions.length === 0) return;
                    playbackIndex = Math.max(0, Math.min(Number(index), historyPositions.length - 1));
                    const position = historyPositions[playbackIndex];
                    const latitude = Number(position.latitude);
                    const longitude = Number(position.longitude);
                    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) return;

                    if (!playbackMarker) {
                        playbackMarker = L.marker([latitude, longitude], {
                            icon: L.divIcon({
                                className: '',
                                html: `<div class="gps-marker gps-marker--moving"><span class="gps-marker__direction">${vehicleSvg}</span></div>`,
                                iconAnchor: [18, 18],
                                iconSize: [36, 36],
                            }),
                        }).addTo(historyLayer);
                    }

                    const course = courseData(position);
                    playbackMarker.setLatLng([latitude, longitude]);
                    playbackMarker.setIcon(L.divIcon({
                        className: '',
                        html: `<div class="gps-marker gps-marker--moving"><span class="gps-marker__direction" style="transform:rotate(${course?.degrees ?? 0}deg)">${vehicleSvg}</span></div>`,
                        iconAnchor: [18, 18],
                        iconSize: [36, 36],
                    }));
                    map.panTo([latitude, longitude], { animate: true });
                    elements.historyProgress.value = playbackIndex;
                    const playbackSpeed = speedKmhValue(position);
                    elements.historyTime.textContent = `${formatDate(positionDate(null, position))} · ${playbackSpeed === null ? 'Velocidad no disponible' : `${playbackSpeed.toFixed(1)} km/h`}`;
                };

                const togglePlayback = () => {
                    if (historyPositions.length === 0) return;
                    if (playbackTimer) {
                        stopPlayback();
                        return;
                    }
                    if (playbackIndex >= historyPositions.length - 1) updatePlayback(0);
                    elements.play.innerHTML = '<i class="ti ti-player-pause"></i> Pausar';
                    playbackTimer = setInterval(() => {
                        if (playbackIndex >= historyPositions.length - 1) {
                            stopPlayback();
                            return;
                        }
                        updatePlayback(playbackIndex + 1);
                    }, Number(elements.playSpeed.value));
                };

                const closeHistory = () => {
                    stopPlayback();
                    historyPositions = [];
                    playbackIndex = 0;
                    playbackMarker = null;
                    historyLayer.clearLayers();
                    elements.historyContent.classList.add('hidden');
                    elements.historyClose.classList.add('hidden');
                    if (!map.hasLayer(markerCluster)) markerCluster.addTo(map);
                    if (!map.hasLayer(focusLayer)) focusLayer.addTo(map);
                    fitVisibleMarkers();
                };

                const inferredStops = (route) => {
                    const stops = [];
                    let startIndex = null;

                    route.forEach((position, index) => {
                        const speed = speedKmhValue(position);
                        const stopped = speed !== null && speed <= 1;
                        if (stopped && startIndex === null) startIndex = index;
                        const closesStop = startIndex !== null && (!stopped || index === route.length - 1);
                        if (!closesStop) return;

                        const endIndex = stopped && index === route.length - 1 ? index : index - 1;
                        const startTime = new Date(positionDate(null, route[startIndex]));
                        const endTime = new Date(positionDate(null, route[endIndex]));
                        const duration = endTime - startTime;
                        if (duration >= 5 * 60 * 1000) {
                            stops.push({
                                latitude: route[startIndex].latitude,
                                longitude: route[startIndex].longitude,
                                startTime: startTime.toISOString(),
                                endTime: endTime.toISOString(),
                                duration,
                                address: route[startIndex].address || 'Parada estimada',
                            });
                        }
                        startIndex = null;
                    });

                    return stops;
                };

                const renderHistory = (payload) => {
                    historyPositions = (Array.isArray(payload.positions) ? payload.positions : [])
                        .filter((position) => Number.isFinite(Number(position.latitude)) && Number.isFinite(Number(position.longitude)))
                        .sort((left, right) => new Date(positionDate(null, left)) - new Date(positionDate(null, right)));
                    if (historyPositions.length === 0) throw new Error('No hay posiciones registradas en el periodo seleccionado.');

                    stopPlayback();
                    historyLayer.clearLayers();
                    playbackMarker = null;
                    if (map.hasLayer(markerCluster)) map.removeLayer(markerCluster);
                    if (map.hasLayer(focusLayer)) map.removeLayer(focusLayer);

                    const coordinates = historyPositions.map((position) => [Number(position.latitude), Number(position.longitude)]);
                    L.polyline(coordinates, { color: '#4f46e5', weight: 5, opacity: .85 }).addTo(historyLayer);
                    L.circleMarker(coordinates[0], { radius: 8, color: '#fff', weight: 2, fillColor: '#16a34a', fillOpacity: 1 }).bindTooltip('Inicio').addTo(historyLayer);
                    L.circleMarker(coordinates[coordinates.length - 1], { radius: 8, color: '#fff', weight: 2, fillColor: '#dc2626', fillOpacity: 1 }).bindTooltip('Fin').addTo(historyLayer);

                    let distance = 0;
                    for (let index = 1; index < historyPositions.length; index += 1) distance += distanceBetween(historyPositions[index - 1], historyPositions[index]);
                    const speeds = historyPositions.map(speedKmhValue).filter((speed) => speed !== null);
                    const durationMs = new Date(positionDate(null, historyPositions.at(-1))) - new Date(positionDate(null, historyPositions[0]));
                    const durationHours = Math.max(0, durationMs / 3600000);
                    const averageSpeed = speeds.length ? speeds.reduce((total, speed) => total + speed, 0) / speeds.length : null;

                    elements.historySummary.innerHTML = [
                        detailItem('Posiciones', historyPositions.length, 'map-pin'),
                        detailItem('Distancia', `${distance.toFixed(1)} km`, 'route'),
                        detailItem('Duración', `${durationHours.toFixed(1)} h`, 'clock'),
                        detailItem('Velocidad máxima', speeds.length ? `${Math.max(...speeds).toFixed(1)} km/h` : 'No disponible', 'gauge'),
                        detailItem('Velocidad promedio', averageSpeed === null ? 'No disponible' : `${averageSpeed.toFixed(1)} km/h`, 'brand-speedtest'),
                    ].join('');

                    const reportedStops = Array.isArray(payload.stops) ? payload.stops : [];
                    const stops = reportedStops.length ? reportedStops : inferredStops(historyPositions);
                    stops.forEach((stop) => {
                        const latitude = Number(stop.latitude);
                        const longitude = Number(stop.longitude);
                        if (Number.isFinite(latitude) && Number.isFinite(longitude)) {
                            L.circleMarker([latitude, longitude], { radius: 6, color: '#fff', weight: 2, fillColor: '#f59e0b', fillOpacity: 1 })
                                .bindTooltip(`Parada · ${stop.address || formatDate(stop.startTime)}`)
                                .addTo(historyLayer);
                        }
                    });
                    elements.historyStops.innerHTML = stops.length
                        ? stops.map((stop) => `<div class="p-3 text-sm rounded-lg bg-amber-50 dark:bg-amber-900/20"><p class="font-semibold text-amber-900 dark:text-amber-200">${escapeHtml(stop.address || 'Parada registrada')}</p><p class="mt-1 text-amber-700 dark:text-amber-300">${escapeHtml(formatDate(stop.startTime))} · ${escapeHtml(formatNumber(Number(stop.duration) / 60000, ' min', 0))}</p></div>`).join('')
                        : '<p class="text-sm text-gray-500 dark:text-gray-400">No se reportaron paradas para este periodo.</p>';

                    const events = Array.isArray(payload.events) ? payload.events : [];
                    elements.historyEvents.innerHTML = events.length
                        ? events.map((event) => `<div class="p-3 text-sm rounded-lg bg-red-50 dark:bg-red-900/20"><p class="font-semibold text-red-900 dark:text-red-200">${escapeHtml(eventLabel(event.type))}</p><p class="mt-1 text-red-700 dark:text-red-300">${escapeHtml(formatDate(event.eventTime))}</p></div>`).join('')
                        : '<p class="text-sm text-gray-500 dark:text-gray-400">No se reportaron eventos para este periodo.</p>';

                    elements.historyProgress.max = historyPositions.length - 1;
                    elements.historyProgress.value = 0;
                    elements.historyContent.classList.remove('hidden');
                    elements.historyClose.classList.remove('hidden');
                    map.fitBounds(L.latLngBounds(coordinates), { padding: [35, 35], maxZoom: 16 });
                    updatePlayback(0);
                };

                const loadHistory = async () => {
                    if (!selectedDeviceId) throw new Error('Selecciona primero una unidad en el mapa o en la lista.');
                    if (followedDeviceId) stopFollowing();
                    const from = new Date(elements.historyFrom.value);
                    const to = new Date(elements.historyTo.value);
                    if (Number.isNaN(from.getTime()) || Number.isNaN(to.getTime())) throw new Error('Selecciona un periodo válido.');

                    const url = new URL(endpoints.history, window.location.origin);
                    url.searchParams.set('device_id', selectedDeviceId);
                    url.searchParams.set('from', from.toISOString());
                    url.searchParams.set('to', to.toISOString());
                    const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                    const payload = await response.json();
                    if (!response.ok) {
                        const validationMessage = payload.errors ? Object.values(payload.errors).flat()[0] : null;
                        throw new Error(validationMessage || payload.message || 'No fue posible consultar el recorrido.');
                    }
                    renderHistory(payload);
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
                        renderAlerts();
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
                                mergeEvents(payload.events);
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
                elements.ignitionFilter.addEventListener('change', render);
                elements.alertFilter.addEventListener('change', render);
                elements.geofenceToggle.addEventListener('change', () => {
                    if (elements.geofenceToggle.checked) {
                        if (!map.hasLayer(geofenceLayer)) geofenceLayer.addTo(map);
                        if (canManageGps && !map.hasLayer(editableGeofenceLayer)) editableGeofenceLayer.addTo(map);
                    } else if (map.hasLayer(geofenceLayer)) {
                        map.removeLayer(geofenceLayer);
                        if (map.hasLayer(editableGeofenceLayer)) map.removeLayer(editableGeofenceLayer);
                    }
                });
                elements.geofenceSelect.addEventListener('change', () => selectGeofence(elements.geofenceSelect.value));
                elements.speedSave?.addEventListener('click', () => {
                    elements.speedStatus.textContent = 'Guardando…';
                    saveSelectedSpeedLimit().then(() => {
                        elements.speedStatus.textContent = 'Límite guardado';
                    }).catch((error) => {
                        elements.speedStatus.textContent = error.message;
                    });
                });
                elements.reportForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    elements.reportError.classList.add('hidden');
                    loadOperationalReport().catch((error) => {
                        elements.reportError.textContent = error.message;
                        elements.reportError.classList.remove('hidden');
                    });
                });
                elements.reportXlsx.addEventListener('click', () => { window.location.href = reportUrl(endpoints.reportXlsx).toString(); });
                elements.reportPdf.addEventListener('click', () => { window.location.href = reportUrl(endpoints.reportPdf).toString(); });
                elements.geofenceNew?.addEventListener('click', () => new L.Draw.Polygon(map).enable());
                elements.geofenceEdit?.addEventListener('click', () => {
                    const selected = geofences.get(elements.geofenceSelect.value);
                    if (!selected) return showError('Selecciona una geocerca para editarla.');
                    openGeofenceDialog(selected.layer, elements.geofenceSelect.value);
                });
                elements.geofenceDelete?.addEventListener('click', async () => {
                    const geofenceId = elements.geofenceSelect.value;
                    const selected = geofences.get(geofenceId);
                    if (!selected) return showError('Selecciona una geocerca para eliminarla.');
                    if (!window.confirm(`¿Eliminar definitivamente la geocerca "${selected.data.name}" de Traccar?`)) return;
                    try {
                        const response = await fetch(endpoints.geofenceDelete.replace(/\/0$/, `/${geofenceId}`), {
                            method: 'DELETE', credentials: 'same-origin', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        });
                        if (!response.ok) throw new Error('No fue posible eliminar la geocerca.');
                        await loadGeofences();
                    } catch (error) { showError(error.message); }
                });
                elements.geofenceCancel?.addEventListener('click', closeGeofenceDialog);
                elements.geofenceForm?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const geofenceId = elements.geofenceId.value;
                    const endpoint = geofenceId ? endpoints.geofenceUpdate.replace(/\/0$/, `/${geofenceId}`) : endpoints.geofenceCreate;
                    try {
                        const response = await fetch(endpoint, {
                            method: geofenceId ? 'PUT' : 'POST', credentials: 'same-origin',
                            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({ name: elements.geofenceName.value, description: elements.geofenceDescription.value, area: elements.geofenceArea.value, attributes: { color: elements.geofenceColor.value } }),
                        });
                        const payload = await response.json();
                        if (!response.ok) throw new Error(Object.values(payload.errors || {}).flat()[0] || payload.message || 'No fue posible guardar la geocerca.');
                        closeGeofenceDialog();
                        await loadGeofences();
                        if (payload.geofence?.id) selectGeofence(payload.geofence.id);
                    } catch (error) {
                        elements.geofenceFormError.textContent = error.message;
                        elements.geofenceFormError.classList.remove('hidden');
                    }
                });
                elements.alertPriority.addEventListener('change', () => loadAlerts().catch((error) => showError(error.message)));
                elements.alertReadFilter.addEventListener('change', () => loadAlerts().catch((error) => showError(error.message)));
                elements.alertSearch.addEventListener('input', renderAlerts);
                elements.alertReadAll.addEventListener('click', () => markAlertsRead([], true).catch((error) => showError(error.message)));
                elements.alertList.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-gps-alert-id]');
                    if (!button) return;
                    const rawAlertId = button.dataset.gpsAlertId;
                    const deviceId = button.dataset.deviceId;
                    if (deviceId && devices.has(String(deviceId))) selectDevice(deviceId);
                    if (button.dataset.geofenceId) selectGeofence(button.dataset.geofenceId, false);
                    if (!rawAlertId.startsWith('speed-')) markAlertsRead([Number(rawAlertId)]).catch((error) => showError(error.message));
                });
                elements.labelMode.addEventListener('change', () => {
                    devices.forEach((device) => updateMarker(device.id));
                    render();
                });
                document.querySelectorAll('[data-quick-status]').forEach((button) => {
                    button.addEventListener('click', () => {
                        elements.statusFilter.value = button.dataset.quickStatus;
                        render();
                    });
                });
                elements.refresh.addEventListener('click', () => loadData());
                elements.fit.addEventListener('click', fitVisibleMarkers);
                elements.follow.addEventListener('click', toggleFollowing);
                elements.stopFollow.addEventListener('click', stopFollowing);
                elements.closeDetail.addEventListener('click', () => {
                    selectedDeviceId = null;
                    elements.detail.classList.add('hidden');
                    devices.forEach((device) => updateMarker(device.id));
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
                map.on('zoomend', () => {
                    if (elements.labelMode.value !== 'automatic') return;
                    devices.forEach((device) => updateMarker(device.id));
                    render();
                });
                elements.deviceList.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-device-id]');
                    if (!button || button.disabled) return;
                    selectDevice(button.dataset.deviceId);
                });
                elements.historyForm.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    elements.historyError.classList.add('hidden');
                    elements.historyLoad.disabled = true;
                    elements.historyLoad.classList.add('opacity-60');
                    try {
                        await loadHistory();
                    } catch (error) {
                        elements.historyError.textContent = error.message || 'No fue posible consultar el recorrido.';
                        elements.historyError.classList.remove('hidden');
                    } finally {
                        elements.historyLoad.disabled = false;
                        elements.historyLoad.classList.remove('opacity-60');
                    }
                });
                elements.historyClose.addEventListener('click', closeHistory);
                elements.play.addEventListener('click', togglePlayback);
                elements.restart.addEventListener('click', () => {
                    stopPlayback();
                    updatePlayback(0);
                });
                elements.historyProgress.addEventListener('input', () => {
                    stopPlayback();
                    updatePlayback(elements.historyProgress.value);
                });
                elements.playSpeed.addEventListener('change', () => {
                    if (!playbackTimer) return;
                    stopPlayback();
                    togglePlayback();
                });

                window.addEventListener('pagehide', () => {
                    closingPage = true;
                    if (reconnectTimer) clearTimeout(reconnectTimer);
                    if (alertRefreshTimer) clearTimeout(alertRefreshTimer);
                    if (alertPollTimer) clearInterval(alertPollTimer);
                    stopPlayback();
                    socket?.close();
                });

                const localDateTimeValue = (date) => new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
                const historyEnd = new Date();
                const historyStart = new Date(historyEnd.getTime() - 6 * 60 * 60 * 1000);
                elements.historyFrom.value = localDateTimeValue(historyStart);
                elements.historyTo.value = localDateTimeValue(historyEnd);
                elements.reportFrom.value = localDateTimeValue(new Date(historyEnd.getTime() - 24 * 60 * 60 * 1000));
                elements.reportTo.value = localDateTimeValue(historyEnd);

                loadData({ fit: true }).finally(connectSocket);
                loadGeofences();
                loadSpeedLimits();
                initializeGeofenceDrawing();
                loadAlerts().catch((error) => {
                    elements.alertList.innerHTML = `<p class="p-6 text-sm text-center text-red-600 dark:text-red-300">${escapeHtml(error.message)}</p>`;
                });
                alertPollTimer = setInterval(() => loadAlerts().catch(() => {}), 60000);
            });
        </script>
    @endpush
</x-app-layout>
