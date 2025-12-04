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

@if($esFiestas)
    {{-- Diseño navideño --}}
    <div class="w-full h-auto mx-auto px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-red-100 via-white to-green-100 dark:from-red-900 via-gray-800 to-green-900 py-4">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:h-24">

            <div class="flex-shrink-0">
                @if(Auth::user()->empresa === 'PSC')
                    <img src="{{ asset('images/psc.png') }}" width="100">
                @elseif(Auth::user()->empresa === 'Montana')
                    <img src="{{ asset('images/montana.png') }}" width="150">
                @elseif(Auth::user()->empresa === 'SPYT')
                    <img src="{{ asset('images/spyt.png') }}" width="100">
                @elseif(Auth::user()->empresa === 'CPKC')
                @else
                @endif
            </div>

            <div class="flex-grow text-center">
                <div class="flex justify-center items-center space-x-4">
                    <span class="text-3xl">🎄</span>
                    <h1 class="text-2xl font-bold text-gray-700 dark:text-gray-200">
                        @if(Auth::user()->empresa === 'PSC')
                            PRIVATE SECURITY CONTRACTORS DE MEXICO, S.A. DE C.V.
                        @elseif(Auth::user()->empresa === 'Montana')
                            SUMINISTROS COMERCIALES MONTANA, S.A. DE C.V.
                        @elseif(Auth::user()->empresa === 'SPYT')
                            SERVICIOS DE PROTECCION Y TRASLADO, S.A. DE C.V.
                        @elseif(Auth::user()->empresa === 'CPKC')
                            CANADIAN PACIFIC KANSAS CITY
                        @else
                        @endif
                    </h1>
                    <span class="text-3xl">🎄</span>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-200">Bienvenido al sistema</p>
                <div class="mt-2">
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 animate-pulse">
                        ¡Felices Fiestas y Próspero Año Nuevo!
                    </p>
                </div>
            </div>

            <div class="flex-shrink-0 w-16 flex items-center justify-end">
                <span class="text-4xl">❄️</span>
            </div>
        </div>
    </div>
@else
    {{-- Diseño original --}}
    <div class="w-full h-auto mx-auto px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800 py-4">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:h-24">

            <div class="flex-shrink-0">
                @if(Auth::user()->empresa === 'PSC')
                    <img src="{{ asset('images/psc.png') }}" width="100">
                @elseif(Auth::user()->empresa === 'Montana')
                    <img src="{{ asset('images/montana.png') }}" width="150">
                @elseif(Auth::user()->empresa === 'SPYT')
                    <img src="{{ asset('images/spyt.png') }}" width="100">
                @elseif(Auth::user()->empresa === 'CPKC')
                @else
                @endif
            </div>

            <div class="flex-grow text-center">
                <h1 class="text-2xl font-bold text-indigo-900 dark:text-white">
                    @if(Auth::user()->empresa === 'PSC')
                        <h1 class="text-2xl font-bold text-indigo-900 dark:text-white">PRIVATE SECURITY CONTRACTORS DE MEXICO, S.A. DE C.V.</h1>
                    @elseif(Auth::user()->empresa === 'Montana')
                        <h1 class="text-2xl font-bold text-indigo-900 dark:text-white">SUMINISTROS COMERCIALES MONTANA, S.A. DE C.V.</h1>
                    @elseif(Auth::user()->empresa === 'SPYT')
                        <h1 class="text-2xl font-bold text-indigo-900 dark:text-white">SERVICIOS DE PROTECCION Y TRASLADO, S.A. DE C.V.</h1>
                    @elseif(Auth::user()->empresa === 'CPKC')
                        <h1 class="text-2xl font-bold text-indigo-900 dark:text-white">CANADIAN PACIFIC KANSAS CITY</h1>
                    @else
                    @endif
                </h1>
                <p class="text-sm text-indigo-700 dark:text-indigo-300">Bienvenido al sistema</p>
            </div>

            <div class="flex-shrink-0 w-16"></div>
        </div>
    </div>
@endif


    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
