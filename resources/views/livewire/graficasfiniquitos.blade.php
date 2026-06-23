<div class="rounded-xl bg-white p-3 dark:bg-gray-800 sm:p-5">
    <div class="mb-6 flex flex-col gap-4 border-b border-gray-100 pb-5 dark:border-gray-700 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="flex items-center text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Cálculo de Finiquitos por Periodo
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Análisis de montos por quincenas</p>
        </div>

        @if ($readyToLoad && (array_sum($dataPeriodo1) > 0 || array_sum($dataPeriodo2) > 0))
            <div class="rounded-xl bg-slate-900 p-3 text-center text-white dark:bg-blue-600">
                <div class="text-xs opacity-90">Total Acumulado</div>
                <div class="text-lg font-bold">${{ number_format(array_sum($dataPeriodo1) + array_sum($dataPeriodo2), 2) }}</div>
            </div>
        @endif
    </div>

    @if (!$readyToLoad)
        <div class="flex flex-col items-center justify-center py-12">
            <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-4 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <button wire:click="cargarGrafica"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Generar Gráfico de Finiquitos
            </button>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Haz clic para visualizar los datos de finiquitos</p>
        </div>
    @else
        <div wire:loading.remove wire:target="actualizarDatos">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por periodo:</span>
                </div>
                <select wire:model.live="filtro"
                        class="block w-full sm:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"
                        wire:loading.attr="disabled">
                    <option value="hoy">Hoy</option>
                    <option value="anio">Este Año</option>
                    <option value="enero">Enero</option>
                    <option value="febrero">Febrero</option>
                    <option value="marzo">Marzo</option>
                    <option value="abril">Abril</option>
                    <option value="mayo">Mayo</option>
                    <option value="junio">Junio</option>
                    <option value="julio">Julio</option>
                    <option value="agosto">Agosto</option>
                    <option value="septiembre">Septiembre</option>
                    <option value="octubre">Octubre</option>
                    <option value="noviembre">Noviembre</option>
                    <option value="diciembre">Diciembre</option>
                </select>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                <div class="lg:col-span-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                    <div class="relative w-full min-h-[400px]">
                        <canvas id="finiquitosChart" wire:ignore></canvas>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 rounded-xl p-5 border border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Periodo 1</h3>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format(array_sum($dataPeriodo1), 2) }}
                        </div>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">26-10 de cada mes</p>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/30 dark:to-cyan-900/30 rounded-xl p-5 border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Periodo 2</h3>
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format(array_sum($dataPeriodo2), 2) }}
                        </div>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">11-25 de cada mes</p>
                    </div>

                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 rounded-xl p-5 border border-amber-200 dark:border-amber-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Total</h3>
                            <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format(array_sum($dataPeriodo1) + array_sum($dataPeriodo2), 2) }}
                        </div>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Suma total</p>
                    </div>
                </div>
            </div>

            @if (in_array($filtro, ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre']))
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4">
                    <div class="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500 flex-shrink-0 mt-0.5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-emerald-800 dark:text-emerald-200">Filtro activo</h4>
                            <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-1">
                                Mostrando datos solo para <span class="font-medium">{{ ucfirst($filtro) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:init', function() {
        let chartInstance = null;

        function initChart(data) {
            const ctx = document.getElementById('finiquitosChart');
            if (!ctx) {
                setTimeout(() => initChart(data), 100);
                return;
            }

            if (chartInstance) {
                chartInstance.destroy();
            }

            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels || ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [
                        {
                            label: 'Periodo 1 (26-10)',
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                            data: data.periodo1 || Array(12).fill(0),
                            barPercentage: 0.8,
                            categoryPercentage: 0.9,
                        },
                        {
                            label: 'Periodo 2 (11-25)',
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                            data: data.periodo2 || Array(12).fill(0),
                            barPercentage: 0.8,
                            categoryPercentage: 0.9,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12
                                },
                                usePointStyle: true,
                                padding: 20
                            }
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
                                    return `${context.dataset.label}: $${context.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                },
                                title: function(tooltipItems) {
                                    return tooltipItems[0].label;
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
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return '$' + (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return '$' + (value / 1000).toFixed(1) + 'K';
                                    }
                                    return '$' + value.toLocaleString('es-MX');
                                },
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
        }

        Livewire.on('chart-finiquitos-updated', (data) => {
            console.log('Datos recibidos para gráfico:', data);

            const chartData = Array.isArray(data) ? data[0] : data;
            initChart(chartData);
        });

        // Inicializar con datos por defecto
        initChart({
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            periodo1: Array(12).fill(0),
            periodo2: Array(12).fill(0)
        });
    });
</script>
@endpush
