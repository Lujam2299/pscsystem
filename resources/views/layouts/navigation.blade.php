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
    $empresa = Auth::user()->empresa;
    $empresas = [
        'PSC' => [
            'nombre' => 'PRIVATE SECURITY CONTRACTORS DE MEXICO, S.A. DE C.V.',
            'corto' => 'PSC',
            'logo' => 'images/psc.png',
            'acento' => 'bg-indigo-600',
        ],
        'Montana' => [
            'nombre' => 'SUMINISTROS COMERCIALES MONTANA, S.A. DE C.V.',
            'corto' => 'Montana',
            'logo' => 'images/montana.png',
            'acento' => 'bg-emerald-600',
        ],
        'SPYT' => [
            'nombre' => 'SERVICIOS DE PROTECCIÓN Y TRASLADO, S.A. DE C.V.',
            'corto' => 'SPYT',
            'logo' => 'images/spyt.png',
            'acento' => 'bg-blue-600',
        ],
        'CPKC' => [
            'nombre' => 'CANADIAN PACIFIC KANSAS CITY',
            'corto' => 'CPKC',
            'logo' => null,
            'acento' => 'bg-red-600',
        ],
    ];
    $datosEmpresa = $empresas[$empresa] ?? [
        'nombre' => config('app.name', 'SGI'),
        'corto' => config('app.name', 'SGI'),
        'logo' => null,
        'acento' => 'bg-indigo-600',
    ];
@endphp

<style>[x-cloak] { display: none !important; }</style>

<nav x-data="{ open: false, profileOpen: false }"
     class="relative z-40 border-b border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="absolute inset-x-0 top-0 h-1 {{ $datosEmpresa['acento'] }}"></div>

    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
        <div class="grid min-h-16 grid-cols-[auto_1fr_auto] items-center gap-3 py-3 sm:min-h-[72px]">
            <a href="{{ route('dashboard') }}"
               class="flex h-11 w-24 shrink-0 items-center justify-start rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 sm:w-36"
               aria-label="Ir al dashboard">
                @if($datosEmpresa['logo'])
                    <img src="{{ asset($datosEmpresa['logo']) }}"
                         alt="Logotipo de {{ $datosEmpresa['corto'] }}"
                         class="max-h-11 w-full object-contain object-left">
                @else
                    <span class="text-lg font-black tracking-tight text-gray-800 dark:text-white">{{ $datosEmpresa['corto'] }}</span>
                @endif
            </a>

            <div class="min-w-0 text-center">
                <div class="flex items-center justify-center gap-2">
                    @if($esFiestas)
                        <span class="hidden text-lg sm:inline" aria-hidden="true">❄️</span>
                    @endif
                    <h1 class="truncate text-sm font-bold tracking-tight text-gray-900 dark:text-white sm:hidden">
                        {{ $datosEmpresa['corto'] }}
                    </h1>
                    <h1 class="hidden text-base font-bold leading-tight tracking-tight text-indigo-950 dark:text-white sm:block lg:text-lg">
                        {{ $datosEmpresa['nombre'] }}
                    </h1>
                </div>
                <p class="mt-0.5 hidden text-xs text-gray-500 dark:text-gray-400 sm:block">
                    {{ $esFiestas ? 'Felices fiestas · Bienvenido al sistema' : 'Bienvenido al sistema' }}
                </p>
            </div>

            <div class="flex items-center justify-end gap-2">
                <div class="relative hidden sm:block">
                    <button type="button" @click="profileOpen = !profileOpen" @click.outside="profileOpen = false"
                            class="inline-flex items-center gap-2 rounded-xl px-2.5 py-2 text-left transition hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:hover:bg-gray-700"
                            :aria-expanded="profileOpen.toString()" aria-haspopup="menu">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 text-sm font-bold text-white shadow-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden min-w-0 lg:block">
                            <span class="block max-w-40 truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ Auth::user()->name }}</span>
                        </span>
                        <i class="ti ti-chevron-down text-gray-400 transition-transform" :class="profileOpen && 'rotate-180'" aria-hidden="true"></i>
                    </button>

                    <div x-cloak x-show="profileOpen" x-transition.origin.top.right
                         class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl dark:border-gray-700 dark:bg-gray-800"
                         role="menu">
                        <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700" role="menuitem">
                            <i class="ti ti-user" aria-hidden="true"></i> Perfil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20" role="menuitem">
                                <i class="ti ti-logout" aria-hidden="true"></i> Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>

                <button type="button" @click="open = !open"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl text-gray-600 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-gray-300 dark:hover:bg-gray-700 sm:hidden"
                        :aria-expanded="open.toString()" aria-controls="mobile-navigation" aria-label="Abrir menú de usuario">
                    <i class="ti text-xl" :class="open ? 'ti-x' : 'ti-menu-2'" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-navigation" x-cloak x-show="open" x-transition
         class="border-t border-gray-200 bg-white px-4 py-4 dark:border-gray-700 dark:bg-gray-800 sm:hidden">
        <div class="mb-3 flex items-center gap-3 rounded-xl bg-gray-50 p-3 dark:bg-gray-900/40">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 font-bold text-white">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
            <i class="ti ti-layout-dashboard" aria-hidden="true"></i> Dashboard
        </a>
        <a href="{{ route('profile.edit') }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
            <i class="ti ti-user" aria-hidden="true"></i> Perfil
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex min-h-11 w-full items-center gap-3 rounded-lg px-3 text-left text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                <i class="ti ti-logout" aria-hidden="true"></i> Cerrar sesión
            </button>
        </form>
    </div>
</nav>
