<div class="rounded-xl bg-white p-3 dark:bg-gray-800 sm:p-5">
    <div class="mb-6 flex flex-col gap-4 border-b border-gray-100 pb-5 dark:border-gray-700 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="flex items-center text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Estadísticas Generales del Personal
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Resumen de movimientos y eventos</p>
        </div>

        @if ($readyToLoad && (array_sum($data) > 0))
            <div class="rounded-xl bg-slate-900 p-3 text-center text-white dark:bg-blue-600">
                <div class="text-xs opacity-90">Total Eventos</div>
                <div class="text-lg font-bold">{{ array_sum($data) }}</div>
            </div>
        @endif
    </div>

    @if (!$readyToLoad)
        <div class="flex flex-col items-center justify-center py-12">
            <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-4 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <button wire:click="initChart"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Generar Gráfico de Estadísticas
            </button>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Haz clic para visualizar las estadísticas generales</p>
        </div>
    @else

        <div wire:loading.remove wire:target="actualizarDatos">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por periodo:</span>
                </div>
                <select wire:model.live.debounce.500ms="filtro"
                        class="block w-full sm:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Esta Semana</option>
                    <option value="mes">Este Mes</option>
                    <option value="anio">Este Año</option>
                </select>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-2 bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                    <div class="relative w-full min-h-[400px]">
                        <canvas id="chartStats" wire:ignore></canvas>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-teal-50 to-emerald-50 dark:from-teal-900/30 dark:to-emerald-900/30 rounded-xl p-5 border border-teal-200 dark:border-teal-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-teal-800 dark:text-teal-200">Altas</h3>
                            <div class="w-3 h-3 bg-teal-500 rounded-full"></div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $data[0] ?? 0 }}
                        </div>
                        <p class="text-xs text-teal-600 dark:text-teal-400 mt-1">Nuevos ingresos</p>
                    </div>

                    <div class="bg-gradient-to-br from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30 rounded-xl p-5 border border-rose-200 dark:border-rose-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-rose-800 dark:text-rose-200">Bajas</h3>
                            <div class="w-3 h-3 bg-rose-500 rounded-full"></div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $data[1] ?? 0 }}
                        </div>
                        <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">Terminaciones</p>
                    </div>

                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 rounded-xl p-5 border border-amber-200 dark:border-amber-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Inasistencias</h3>
                            <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $data[2] ?? 0 }}
                        </div>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Faltas registradas</p>
                    </div>

                    <div class="bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30 rounded-xl p-5 border border-violet-200 dark:border-violet-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-violet-800 dark:text-violet-200">Vacaciones</h3>
                            <div class="w-3 h-3 bg-violet-500 rounded-full"></div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $data[3] ?? 0 }}
                        </div>
                        <p class="text-xs text-violet-600 dark:text-violet-400 mt-1">Días programados</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartInstance = null;

    const initChart = () => {
        const ctx = document.getElementById('chartStats');

        if (!ctx) {
            console.warn("⛔ No se encontró el canvas con id 'chartStats'");
            setTimeout(initChart, 100);
            return;
        }

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Altas', 'Bajas', 'Inasistencias', 'Vacaciones'],
                datasets: [{
                    label: 'Estadísticas',
                    data: [0, 0, 0, 0],
                    backgroundColor: [
                        'rgba(20, 184, 166, 0.8)',   // Teal - Altas
                        'rgba(244, 63, 94, 0.8)',    // Rose - Bajas
                        'rgba(245, 158, 11, 0.8)',   // Amber - Inasistencias
                        'rgba(139, 92, 246, 0.8)'    // Violet - Vacaciones
                    ],
                    borderColor: [
                        'rgba(13, 148, 136, 1)',
                        'rgba(225, 29, 72, 1)',
                        'rgba(217, 119, 6, 1)',
                        'rgba(124, 58, 237, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: 0.7,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.parsed.y} eventos`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });

        console.log("✅ Gráfico inicializado");
    };

    Livewire.on('chart-altas-updated', (data) => {
        console.log("📊 Recibido evento 'chart-altas-updated' con data:", data);

        const cleanData = Array.isArray(data[0]) ? data[0] : data;
        console.log("📦 Datos limpios:", cleanData);

        if (!chartInstance) {
            console.log("🎯 Inicializando gráfico por primera vez...");
            setTimeout(() => {
                initChart();

                if (Array.isArray(cleanData)) {
                    chartInstance.data.datasets[0].data = cleanData;
                    chartInstance.update();
                    console.log("✅ Gráfico creado y datos cargados.");
                } else {
                    console.error("❌ El dato recibido no es un array:", cleanData);
                }
            }, 100);
            return;
        }

        if (chartInstance && chartInstance.data) {
            chartInstance.data.datasets[0].data = cleanData;
            chartInstance.update();
            console.log("🔁 Datos actualizados en el gráfico.");
        } else {
            console.error("❌ Error: chartInstance no existe o no tiene estructura esperada.");
        }
    });

    // Inicializar el gráfico cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('chartStats')) {
            initChart();
        }
    });
</script>
@endpush
