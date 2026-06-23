@php
    $fechaActual = now();
    $inicioFiestas = $fechaActual->copy()->month(12)->day(1);
    $finFiestas = $fechaActual->copy()->month(1)->day(6);

    if ($fechaActual->month >= 1 && $fechaActual->month < 12) {
        $inicioFiestas = $inicioFiestas->year($fechaActual->year - 1);
        $finFiestas = $finFiestas->year($fechaActual->year);
    } else {
        $inicioFiestas = $inicioFiestas->year($fechaActual->year);
        $finFiestas = $finFiestas->year($fechaActual->year + 1);
    }

    $esFiestas = $fechaActual->between($inicioFiestas, $finFiestas);
@endphp

<footer class="border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    @if($esFiestas)
        <div class="h-0.5 bg-gradient-to-r from-red-500 via-amber-300 to-emerald-500" aria-hidden="true"></div>
    @endif

    <div class="mx-auto flex max-w-screen-2xl flex-col items-center justify-between gap-4 px-4 py-5 sm:px-6 md:flex-row lg:px-8">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm dark:bg-blue-600">
                <i class="ti ti-building-community text-lg" aria-hidden="true"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">Sistema de Gestión Interna</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">© {{ now()->year }} SGI · Todos los derechos reservados</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-end">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/25 dark:text-emerald-300">
                <i class="ti ti-shield-check" aria-hidden="true"></i>
                Acceso seguro
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 dark:bg-blue-900/25 dark:text-blue-300">
                <i class="ti ti-lock" aria-hidden="true"></i>
                Personal autorizado
            </span>
            @if($esFiestas)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 dark:bg-amber-900/25 dark:text-amber-300">
                    <span aria-hidden="true">❄️</span>
                    Felices fiestas
                </span>
            @endif
        </div>
    </div>
</footer>
