@php
    $fechaActual = now();
    $inicioFiestas = $fechaActual->copy()->month(12)->day(1);
    $finFiestas = $fechaActual->copy()->month(1)->day(6);

    // Si ya pasamos de enero, ajustamos para el próximo año
    if ($fechaActual->month >= 1 && $fechaActual->month < 12) {
        $inicioFiestas = $inicioFiestas->year($fechaActual->year - 1);
        $finFiestas = $finFiestas->year($fechaActual->year);
    } else {
        $inicioFiestas = $inicioFiestas->year($fechaActual->year);
        $finFiestas = $finFiestas->year($fechaActual->year + 1);
    }

    $esFiestas = $fechaActual->between($inicioFiestas, $finFiestas);
@endphp

<footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 {{ $esFiestas ? 'bg-gradient-to-r from-red-100 via-white to-green-100 dark:from-red-900 via-gray-800 to-green-900' : '' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-8">
            <div class="flex flex-col items-center">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <div class="flex items-center justify-center w-10 h-10 {{ $esFiestas ? 'bg-gradient-to-r from-red-600 to-green-700' : 'bg-gradient-to-r from-blue-600 to-indigo-700' }} rounded-lg shadow">
                        @if($esFiestas)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V21" />
                            </svg>
                        @endif
                    </div>
                    <span class="text-xl font-bold {{ $esFiestas ? 'text-red-700 dark:text-red-200' : 'text-gray-900 dark:text-white' }}">SGI</span>
                </div>

                <div class="text-center mb-4">
                    <p class="text-sm {{ $esFiestas ? 'text-gray-700 dark:text-gray-200' : 'text-gray-600 dark:text-gray-400' }}">
                        &copy; {{ date('Y') }}
                        <span class="font-medium {{ $esFiestas ? 'text-gray-700 dark:text-gray-200' : 'text-gray-900 dark:text-white' }}">Sistema de Gestión Interna</span>
                    </p>
                </div>

                <!-- Indicadores de seguridad centrados -->
                <div class="flex items-center justify-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $esFiestas ? 'text-green-600' : 'text-green-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="text-xs {{ $esFiestas ? 'text-gray-700 dark:text-gray-200' : 'text-gray-500 dark:text-gray-400' }}">
                            Acceso seguro
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $esFiestas ? 'text-blue-600' : 'text-blue-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span class="text-xs {{ $esFiestas ? 'text-gray-700 dark:text-gray-200' : 'text-gray-500 dark:text-gray-400' }}">
                            Personal autorizado
                        </span>
                    </div>

                    @if($esFiestas)
                        <!-- Icono navideño adicional -->
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87.69 6.89L12 21l-5.69-.48.69-6.89-5-4.87 6.91-1.01L12 2z"/>
                            </svg>
                            <span class="text-xs text-gray-700 dark:text-gray-200">
                                ¡Felices Fiestas!
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>
