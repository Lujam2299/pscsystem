<x-app-layout>
    <x-navbar />

    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 rounded-r text-red-900 px-4 py-3 shadow-md mb-6" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">Error: {{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(Auth::user()->rol == 'Supervisor' || Auth::user()->rol == 'SUPERVISOR OPERATIVO')
                    @if(!$asistencia_hoy->isEmpty())
                        <div class="text-center py-12">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Asistencia ya registrada</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">El día de hoy ya se registró la asistencia de todos los usuarios. Favor de volver mañana.</p>

                            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                                <a href="{{ route('sup.verAsistencias', Auth::user()->id) }}"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Ver Registros de Asistencia
                                </a>
                                <a href="{{ route('dashboard') }}"
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Regresar
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Registro de Asistencias
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Marque la asistencia de los usuarios activos y adjunte evidencias cuando sea necesario
                                    </p>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-3 py-1 rounded-full">
                                        <span class="text-sm font-medium">{{ $elementos->count() }}</span>
                                        <span class="text-xs">usuarios</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('sup.guardarAsistencias') }}" method="POST" enctype="multipart/form-data" id="form-asistencias">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                @foreach($elementos as $elemento)
                                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-md">
                                        <div class="p-5">
                                            <div class="flex items-center space-x-4 mb-4">
                                                @if($elemento->solicitudAlta?->documentacion?->arch_foto)
                                                    <img src="{{ asset('storage/' . str_replace('storage/', '', $elemento->solicitudAlta->documentacion->arch_foto)) }}"
                                                         alt="Foto de {{ $elemento->name }}"
                                                         class="w-16 h-16 rounded-full object-cover border-2 border-white dark:border-gray-600 shadow-sm">
                                                @else
                                                    <div class="flex-shrink-0 w-16 h-16 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center">
                                                        <span class="text-white font-medium text-lg">
                                                            {{ substr($elemento->name ?? '', 0, 2) }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                                        {{ $elemento->name }}
                                                    </h2>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                        {{ $elemento->empresa }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                        {{ $elemento->punto }}
                                                    </p>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 mt-1">
                                                        {{ $elemento->rol }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                                <label for="asistencia_{{ $elemento->id }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Asistió
                                                </label>
                                                <input type="checkbox"
                                                       name="asistencias[]"
                                                       value="{{ $elemento->id }}"
                                                       id="asistencia_{{ $elemento->id }}"
                                                       class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded cursor-pointer"
                                                       onchange="toggleUploadButton(this, 'upload_container_{{ $elemento->id }}')">
                                            </div>

                                            <div id="upload_container_{{ $elemento->id }}" class="mt-3 hidden">
                                                <label class="flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition cursor-pointer text-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                    Adjuntar evidencia
                                                    <input type="file"
                                                           name="foto_evidencia[{{ $elemento->id }}]"
                                                           class="hidden"
                                                           accept="image/*"
                                                           onchange="previewEvidence(this, '{{ $elemento->id }}')">
                                                </label>

                                                <div id="evidence_preview_{{ $elemento->id }}" class="hidden mt-3">
                                                    <div class="relative">
                                                        <img id="evidence_img_{{ $elemento->id }}" class="h-24 w-full object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                                        <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition-all duration-200 rounded-lg flex items-center justify-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white opacity-0 hover:opacity-100 transition-opacity duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">Vista previa</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-8">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                                    <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Observaciones
                                    </label>
                                    <textarea name="observaciones"
                                              id="observaciones"
                                              rows="4"
                                              class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Escribe tus observaciones aquí..."></textarea>
                                </div>
                            </div>

                            <div class="mt-6">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Coberturas de Turno
                                    </label>
                                    @livewire('seleccioncoberturas')
                                </div>
                            </div>

                            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                                    <button type="submit"
                                            class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Guardar Asistencias
                                    </button>
                                    <a href="{{ route('dashboard') }}"
                                       class="inline-flex items-center px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Regresar
                                    </a>
                                </div>
                            </div>
                        </form>
                    @endif
                @else
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Control de Supervisores
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Supervise el envío de asistencias de los supervisores
                                </p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <div class="bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 px-3 py-1 rounded-full">
                                    <span class="text-sm font-medium">{{ $supervisores->count() }}</span>
                                    <span class="text-xs">supervisores</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            #
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Supervisor
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Punto
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Asistencia Enviada
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Acciones
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($supervisores as $user)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                                            <span class="text-white font-medium text-xs">
                                                                {{ substr($user->name ?? '', 0, 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ $user->name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                                                    {{ $user->punto }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($user->envio_asistencia === 'Sí')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Enviada
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                        Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if($user->envio_asistencia === 'Sí')
                                                    @php
                                                        $asistHoy = \App\Models\Asistencia::where('user_id', $user->id)
                                                            ->where('fecha', \Carbon\Carbon::today())
                                                            ->first();
                                                    @endphp
                                                    @if($asistHoy)
                                                        <a href="{{ route('sup.detalleAsistencia', $asistHoy) }}"
                                                           class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg transition duration-200 shadow-sm">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            Ver Detalles
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500 text-xs">Sin acciones</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-center">
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Regresar
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function toggleUploadButton(checkbox, containerId) {
        const container = document.getElementById(containerId);
        if (checkbox.checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            const previewDiv = document.getElementById('evidence_preview_' + checkbox.id.split('_')[1]);
            if (previewDiv) previewDiv.classList.add('hidden');
        }
    }

    function previewEvidence(input, elementId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.getElementById('evidence_preview_' + elementId);
                const previewImg = document.getElementById('evidence_img_' + elementId);

                previewImg.src = e.target.result;
                previewDiv.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
