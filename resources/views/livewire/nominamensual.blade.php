<div class="h-full rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 sm:p-6">
    <div class="flex h-full items-start gap-4">
        <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-900/30">
            <!-- Icono de tarjeta bancaria -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600 dark:text-green-300" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <rect x="2" y="4" width="20" height="16" rx="2" ry="2" stroke-width="2" stroke="currentColor" fill="none" />
                <line x1="2" y1="10" x2="22" y2="10" stroke-width="2" stroke="currentColor" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nómina del periodo</p>
            <h3 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                ${{ number_format($totalMesActual, 2) }}
            </h3>
            <div
                class="text-sm mt-1 flex items-center gap-1 {{ $variacion >= 0 ? 'text-red-600' : 'text-green-500' }}">
                @if ($variacion > 0)
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 10l5-5 5 5H5z" />
                    </svg>
                @elseif($variacion < 0)
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 10l5 5 5-5H5z" />
                    </svg>
                @else
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 9h12v2H4z" />
                    </svg>
                @endif
                <span class="font-medium">{{ $variacion >= 0 ? '+' : '' }}{{ $variacion }}%</span>
                <span class="text-gray-500 dark:text-gray-400">vs. periodo anterior</span>
            </div>
        </div>
    </div>
</div>
