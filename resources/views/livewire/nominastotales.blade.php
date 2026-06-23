<div class="rounded-xl bg-white p-3 dark:bg-gray-800 sm:p-5">
    <div class="mb-6 flex flex-col gap-4 border-b border-gray-100 pb-5 dark:border-gray-700 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="flex items-center text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Análisis de Nómina
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Visualización de montos por periodos</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            <a href="{{ route('nominas.registros') }}"
               class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Ver Registros
            </a>

            @if ($readyToLoad && $total > 0)
                <div class="rounded-xl bg-slate-900 p-3 text-center text-white dark:bg-blue-600">
                    <div class="text-xs opacity-90">Total Nómina</div>
                    <div class="text-lg font-bold">${{ number_format($total, 2) }}</div>
                </div>
            @endif
        </div>
    </div>

    @if (!$readyToLoad)
        <div class="flex flex-col items-center justify-center py-12">
            <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-4 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <button wire:click="cargarGrafica"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Generar Gráfico de Nómina
            </button>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Haz clic para visualizar los datos de nómina</p>
        </div>
    @endif

    @if ($readyToLoad)
        <div wire:loading.remove wire:target="actualizarGrafica">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por mes:</span>
                </div>
                <select wire:model.live="filtro"
                        class="block w-full sm:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                    <option value="todos">Todos los meses</option>
                    @foreach(['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'] as $mes)
                        <option value="{{ $mes }}">{{ ucfirst($mes) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-2 bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                    <div class="relative w-full min-h-[400px]">
                        <canvas id="chartNominas" wire:ignore></canvas>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/30 dark:to-violet-900/30 rounded-xl p-5 border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-purple-800 dark:text-purple-200">Periodo 1</h3>
                            <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format(array_sum($periodo1), 2) }}
                        </div>
                        <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">26-10 de cada mes</p>
                    </div>

                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/30 dark:to-amber-900/30 rounded-xl p-5 border border-orange-200 dark:border-orange-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">Periodo 2</h3>
                            <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format(array_sum($periodo2), 2) }}
                        </div>
                        <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">11-25 de cada mes</p>
                    </div>

                    <div class="bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/30 dark:to-cyan-900/30 rounded-xl p-5 border border-teal-200 dark:border-teal-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-teal-800 dark:text-teal-200">Diferencia</h3>
                            <div class="w-3 h-3 bg-teal-500 rounded-full"></div>
                        </div>
                        @php
                            $dif = array_sum($periodo1) - array_sum($periodo2);
                            $porcentaje = array_sum($periodo2) > 0 ? (($dif) / array_sum($periodo2)) * 100 : 0;
                        @endphp
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $dif >= 0 ? '+' : '' }}${{ number_format(abs($dif), 2) }}
                        </div>
                        <p class="text-xs text-teal-600 dark:text-teal-400 mt-1">
                            {{ $porcentaje >= 0 ? '+' : '' }}{{ number_format($porcentaje, 1) }}%
                        </p>
                    </div>
                </div>
            </div>

            @if ($filtro !== 'todos')
                <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                    <div class="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-500 flex-shrink-0 mt-0.5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-purple-800 dark:text-purple-200">Filtro activo</h4>
                            <p class="text-sm text-purple-700 dark:text-purple-300 mt-1">
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
    document.addEventListener('livewire:init', () => {
        let chartNominas = null;
        let pendingData = null;

        function initializeChart() {
            const ctx = document.getElementById('chartNominas');
            if (!ctx) {
                setTimeout(initializeChart, 100);
                return;
            }

            if (chartNominas) {
                chartNominas.destroy();
            }

            const data = pendingData || {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                periodo1: Array(12).fill(0),
                periodo2: Array(12).fill(0),
                total: 0
            };

            chartNominas = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Periodo 1 (26-10)',
                            backgroundColor: 'rgba(147, 51, 234, 0.8)',
                            borderColor: 'rgba(126, 34, 206, 1)',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                            data: data.periodo1,
                            barPercentage: 0.8,
                            categoryPercentage: 0.9,
                        },
                        {
                            label: 'Periodo 2 (11-25)',
                            backgroundColor: 'rgba(249, 115, 22, 0.8)',
                            borderColor: 'rgba(234, 88, 12, 1)',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                            data: data.periodo2,
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
                        title: {
                            display: true,
                            text: `Total Nómina: $${parseFloat(data.total || 0).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 20
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
                                    return '$' + value;
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

            pendingData = null;
        }

        Livewire.on('chart-nominas-updated', (data) => {
            const chartData = Array.isArray(data) ? data[0] : data;

            if (!document.getElementById('chartNominas')) {
                pendingData = chartData;
                setTimeout(initializeChart, 100);
            } else if (chartNominas) {
                chartNominas.data.labels = chartData.labels;
                chartNominas.data.datasets[0].data = chartData.periodo1;
                chartNominas.data.datasets[1].data = chartData.periodo2;
                chartNominas.options.plugins.title.text = `Total Nómina: $${parseFloat(chartData.total || 0).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                chartNominas.update('active');
            } else {
                pendingData = chartData;
                initializeChart();
            }
        });

        if (document.getElementById('chartNominas')) {
            initializeChart();
        }
    });
</script>
@endpush
