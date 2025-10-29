<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Detalle de Asistencia
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Registro de asistencia del día {{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-3 py-1 rounded-full">
                                <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Supervisor</h3>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $asistencia->usuario->name }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Hora</h3>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $asistencia->hora_asistencia }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Asistentes</h3>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ count($asistencia->usuarios_enlistados) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Faltas</h3>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ count($asistencia->usuarios_faltantes) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($asistencia->observaciones)
                        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Observaciones
                            </h4>
                            <p class="text-sm text-blue-700 dark:text-blue-300 mt-2">{{ $asistencia->observaciones }}</p>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Asistieron -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="bg-green-50 dark:bg-green-900/20 px-6 py-4 border-b border-green-100 dark:border-green-800">
                            <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Asistieron ({{ count($asistencia->usuarios_enlistados) }})
                            </h3>
                        </div>
                        <div class="p-6">
                            @if($asistencia->usuarios_enlistados->isEmpty())
                                <div class="text-center py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-gray-500 dark:text-gray-400">No hay registros de asistencia</p>
                                </div>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    @foreach ($asistencia->usuarios_enlistados as $usuario)
                                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center transition-all duration-200 hover:shadow-md">
                                            @if($usuario->solicitudAlta?->documentacion?->arch_foto)
                                                <img src="{{ asset('storage/' . str_replace('storage/', '', $usuario->solicitudAlta->documentacion->arch_foto)) }}"
                                                     class="w-16 h-16 rounded-full mx-auto object-cover mb-3 border-2 border-white dark:border-gray-600 shadow-sm">
                                            @else
                                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center mx-auto mb-3">
                                                    <span class="text-white font-medium text-lg">
                                                        {{ substr($usuario->name ?? '', 0, 2) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <p class="text-gray-900 dark:text-white font-medium text-sm truncate">{{ $usuario->name }}</p>
                                            @php
                                                $puntoMostrar = $usuario->punto;
                                                // Solo si el supervisor es de KANSAS/MTY y hay asignación específica
                                                if (in_array($asistencia->usuario->punto, ['KANSAS', 'MTY']) && !empty($asistencia->puntos_asignados_map)) {
                                                    $puntoMostrar = $asistencia->puntos_asignados_map[$usuario->id] ?? $usuario->punto;
                                                }
                                            @endphp
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1">
                                                {{ $puntoMostrar }}
                                                @if($puntoMostrar !== $usuario->punto)
                                                    <span class="text-xs text-blue-600 dark:text-blue-400">(asignado)</span>
                                                @endif
                                            </p>

                                            @if (isset($asistencia->fotos_asistentes[$usuario->id]))
                                                <div class="mt-3">
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200 rounded-full">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        Evidencia
                                                    </span>
                                                    <img src="{{ $asistencia->fotos_asistentes[$usuario->id] }}"
                                                         class="w-20 h-16 object-cover mx-auto rounded-lg cursor-pointer border border-gray-300 dark:border-gray-600 mt-2 hover:opacity-80 transition-opacity"
                                                         alt="Foto de evidencia"
                                                         data-img="{{ $asistencia->fotos_asistentes[$usuario->id] }}"
                                                         onclick="openImageModal(this)">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Otras categorías -->
                    <div class="space-y-6">
                        <!-- Descansaron -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="bg-blue-50 dark:bg-blue-900/20 px-6 py-4 border-b border-blue-100 dark:border-blue-800">
                                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                    Descansaron ({{ count($asistencia->usuarios_descansos) }})
                                </h3>
                            </div>
                            <div class="p-6">
                                @if($asistencia->usuarios_descansos->isEmpty())
                                    <div class="text-center py-4">
                                        <p class="text-gray-500 dark:text-gray-400 text-sm">No se registraron descansos</p>
                                    </div>
                                @else
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                        @foreach ($asistencia->usuarios_descansos as $usuario)
                                            <div class="bg-blue-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                                                @if($usuario->solicitudAlta?->documentacion?->arch_foto)
                                                    <img src="{{ asset('storage/' . str_replace('storage/', '', $usuario->solicitudAlta->documentacion->arch_foto)) }}"
                                                         class="w-16 h-16 rounded-full mx-auto object-cover mb-3 border-2 border-white dark:border-gray-600 shadow-sm">
                                                @else
                                                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mx-auto mb-3">
                                                        <span class="text-white font-medium text-lg">
                                                            {{ substr($usuario->name ?? '', 0, 2) }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <p class="text-gray-900 dark:text-white font-medium text-sm truncate">{{ $usuario->name }}</p>
                                                @php
                                                    $puntoMostrar = $usuario->punto;
                                                    // Solo si el supervisor es de KANSAS/MTY y hay asignación específica
                                                    if (in_array($asistencia->usuario->punto, ['KANSAS', 'MTY']) && !empty($asistencia->puntos_asignados_map)) {
                                                        $puntoMostrar = $asistencia->puntos_asignados_map[$usuario->id] ?? $usuario->punto;
                                                    }
                                                @endphp
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1">
                                                    {{ $puntoMostrar }}
                                                    @if($puntoMostrar !== $usuario->punto)
                                                        <span class="text-xs text-blue-600 dark:text-blue-400">(asignado)</span>
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Faltaron -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="bg-red-50 dark:bg-red-900/20 px-6 py-4 border-b border-red-100 dark:border-red-800">
                                <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Faltaron ({{ count($asistencia->usuarios_faltantes) }})
                                </h3>
                            </div>
                            <div class="p-6">
                                @if($asistencia->usuarios_faltantes->isEmpty())
                                    <div class="text-center py-4">
                                        <p class="text-gray-500 dark:text-gray-400 text-sm">No se registraron inasistencias</p>
                                    </div>
                                @else
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                        @foreach ($asistencia->usuarios_faltantes as $usuario)
                                            <div class="bg-red-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                                                @if($usuario->solicitudAlta?->documentacion?->arch_foto)
                                                    <img src="{{ asset('storage/' . str_replace('storage/', '', $usuario->solicitudAlta->documentacion->arch_foto)) }}"
                                                         class="w-16 h-16 rounded-full mx-auto object-cover mb-3 border-2 border-white dark:border-gray-600 shadow-sm">
                                                @else
                                                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-500 to-pink-600 flex items-center justify-center mx-auto mb-3">
                                                        <span class="text-white font-medium text-lg">
                                                            {{ substr($usuario->name ?? '', 0, 2) }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <p class="text-gray-900 dark:text-white font-medium text-sm truncate">{{ $usuario->name }}</p>
                                                @php
                                                    $puntoMostrar = $usuario->punto;
                                                    // Solo si el supervisor es de KANSAS/MTY y hay asignación específica
                                                    if (in_array($asistencia->usuario->punto, ['KANSAS', 'MTY']) && !empty($asistencia->puntos_asignados_map)) {
                                                        $puntoMostrar = $asistencia->puntos_asignados_map[$usuario->id] ?? $usuario->punto;
                                                    }
                                                @endphp
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1">
                                                    {{ $puntoMostrar }}
                                                    @if($puntoMostrar !== $usuario->punto)
                                                        <span class="text-xs text-blue-600 dark:text-blue-400">(asignado)</span>
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Coberturas -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 px-6 py-4 border-b border-yellow-100 dark:border-yellow-800">
                                <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Coberturas ({{ count($asistencia->usuarios_coberturas) }})
                                </h3>
                            </div>
                            <div class="p-6">
                                @if($asistencia->usuarios_coberturas->isEmpty())
                                    <div class="text-center py-4">
                                        <p class="text-gray-500 dark:text-gray-400 text-sm">No se registraron coberturas</p>
                                    </div>
                                @else
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                        @foreach ($asistencia->usuarios_coberturas as $usuario)
                                            <div class="bg-yellow-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                                                @if($usuario->solicitudAlta?->documentacion?->arch_foto)
                                                    <img src="{{ asset('storage/' . str_replace('storage/', '', $usuario->solicitudAlta->documentacion->arch_foto)) }}"
                                                         class="w-16 h-16 rounded-full mx-auto object-cover mb-3 border-2 border-white dark:border-gray-600 shadow-sm">
                                                @else
                                                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center mx-auto mb-3">
                                                        <span class="text-white font-medium text-lg">
                                                            {{ substr($usuario->name ?? '', 0, 2) }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <p class="text-gray-900 dark:text-white font-medium text-sm truncate">{{ $usuario->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1">{{ $usuario->punto }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de imagen -->
                <div id="imageModal"
                     class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden z-50 p-4">
                    <div class="relative max-w-4xl max-h-full">
                        <button onclick="closeImageModal()"
                                class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <img id="modalImage" src="" class="rounded-lg shadow-2xl max-w-full max-h-[80vh] object-contain">
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-center">
                        <a href="{{ route('sup.verAsistencias', Auth()->user()->id) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function openImageModal(imgElement) {
        const imageSrc = imgElement.getAttribute('data-img');
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        modalImage.src = imageSrc;
        modal.classList.remove('hidden');

        // Prevenir scroll del body cuando el modal está abierto
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');

        // Restaurar scroll del body
        document.body.style.overflow = 'auto';
    }

    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeImageModal();
        }
    });

    // Cerrar modal al hacer clic fuera de la imagen
    document.getElementById('imageModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeImageModal();
        }
    });
</script>
