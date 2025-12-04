<x-slot name="header">
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

    @if($esFiestas)
        <div class="w-full bg-gradient-to-r from-red-50 via-white to-green-50 dark:from-red-900/30 via-gray-800/30 to-green-900/30 py-4 px-6 rounded-lg border border-red-200 dark:border-red-800/50">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex-1 flex flex-col">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <img
                                src="https://api.dicebear.com/9.x/initials/svg?seed={{ urlencode(auth()->user()->name) }}"
                                alt="avatar"
                                class="w-12 h-12 rounded-full border-2 border-white dark:border-gray-700 shadow"
                            />
                            <div class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                                🎅
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h2 class="font-semibold text-lg text-gray-800 dark:text-gray-200">
                                    {{ Auth::user()->name }}
                                </h2>
                                <span class="text-xl">✨</span>
                            </div>

                            <div class="mt-1">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 px-3 py-1 rounded-full border border-red-200 dark:border-red-700">
                                    @if(Auth::user()->rol == 'admin')
                                        Administrador
                                    @else
                                        {{ Auth::user()->rol }}
                                    @endif
                                </span>
                            </div>

                            <div class="mt-2">
                                <div class="inline-flex items-center gap-2 bg-red-50 dark:bg-red-900/30 px-3 py-1 rounded-lg border border-red-200 dark:border-red-700">
                                    <span class="text-lg">🎄</span>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-red-700 dark:text-red-300 leading-none">
                                            Que cada momento
                                        </span>
                                        <span class="text-[10px] text-red-600 dark:text-red-400 leading-none">
                                            sea mágico en estas fiestas
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 sm:gap-3">
                    @if(Auth::user()->rol == 'admin')
                        <x-admin-layout></x-admin-layout>
                    @else
                        <x-user-layout></x-user-layout>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <img
                    src="https://api.dicebear.com/9.x/initials/svg?seed={{ urlencode(auth()->user()->name) }}"
                    alt="avatar"
                    class="w-12 h-12 rounded-full"
                />
                <div class="flex flex-col">
                    <h2 class="font-semibold text-lg text-gray-800 dark:text-gray-200">
                        {{ Auth::user()->name }}
                    </h2>
                    <h3 class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        @if(Auth::user()->rol == 'admin')
                            Administrador
                        @else
                            {{ Auth::user()->rol }}
                        @endif
                    </h3>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 sm:gap-4">
                @if(Auth::user()->rol == 'admin')
                    <x-admin-layout></x-admin-layout>
                @else
                    <x-user-layout></x-user-layout>
                @endif
            </div>
        </div>
    @endif
</x-slot>
