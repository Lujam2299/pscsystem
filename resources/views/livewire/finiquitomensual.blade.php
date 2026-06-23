<div class="h-full rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 sm:p-6">
    <div class="flex h-full items-start gap-4">
        <div class="rounded-xl bg-violet-50 p-3 dark:bg-violet-900/30">
            <!-- Icono de dinero con documento -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600 dark:text-purple-300" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <!-- Documento -->
                <rect x="3" y="3" width="14" height="18" rx="2" ry="2" stroke-width="2" stroke="currentColor" fill="none" />
                <line x1="3" y1="7" x2="17" y2="7" stroke-width="2" stroke="currentColor" />
                <line x1="7" y1="11" x2="13" y2="11" stroke-width="2" stroke="currentColor" />
                <!-- Billete/dinero -->
                <circle cx="20" cy="14" r="3" stroke-width="2" stroke="currentColor" fill="none" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Finiquitos del periodo</p>
            <h3 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                ${{ number_format($finiquitosMesActual, 2) }}
            </h3>
            <div
                class="text-sm mt-1 flex items-center gap-1 {{ $variacionFiniquitos >= 0 ? 'text-red-600' : 'text-green-500' }}">
                @if ($variacionFiniquitos > 0)
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 10l5-5 5 5H5z" />
                    </svg>
                @elseif ($variacionFiniquitos < 0)
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 10l5 5 5-5H5z" />
                    </svg>
                @else
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 9h12v2H4z" />
                    </svg>
                @endif
                <span class="font-medium">{{ $variacionFiniquitos >= 0 ? '+' : '' }}{{ $variacionFiniquitos }}%</span>
                <span class="text-gray-500 dark:text-gray-400">vs. mes anterior</span>
            </div>
        </div>
    </div>
</div>
