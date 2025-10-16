<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-2xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Generar Riesgo de Trabajo
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Complete la información para generar un nuevo riesgo de trabajo para <span class="font-medium">{{ $user->name }}</span>
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div class="bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-200 px-3 py-1 rounded-full">
                                <span class="text-sm font-medium">Empleado</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded-lg mb-6">
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded-lg mb-6">
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded-lg mb-6">
                        <p class="font-bold mb-2">¡Atención!</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('aux.guardarRiesgo', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">

                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Información del Empleado
                        </h3>
                        <div class="space-y-2">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</p>
                                <p class="text-gray-900 dark:text-white">{{ $user->name }}</p>
                            </div>
                            @if($user->email)
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="text-gray-900 dark:text-white">{{ $user->email }}</p>
                                </div>
                            @endif
                            @if($user->solicitudAlta?->punto)
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Punto</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                                        {{ $user->solicitudAlta->punto }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Campo Fecha -->
                    <div>
                        <label for="fecha" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fecha del Incidente
                        </label>
                        <input type="date"
                            name="fecha"
                            id="fecha"
                            value="{{ old('fecha') }}"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Campo Folio -->
                    <div>
                        <label for="folio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Folio
                        </label>
                        <input type="text"
                            name="folio"
                            id="folio"
                            value="{{ old('folio') }}"
                            placeholder="Ingrese el folio (opcional)"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="tipo_riesgo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tipo de Riesgo
                        </label>
                        <select name="tipo_riesgo" id="tipo_riesgo" required
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Selecciona una opción</option>
                            <option value="En el trabajo" {{ old('tipo_riesgo') == 'En el trabajo' ? 'selected' : '' }}>En el trabajo</option>
                            <option value="En trayecto" {{ old('tipo_riesgo') == 'En trayecto' ? 'selected' : '' }}>En trayecto</option>
                        </select>
                    </div>

                    <div>
                        <label for="descripcion_observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Descripción / Observaciones
                        </label>
                        <textarea name="descripcion_observaciones" id="descripcion_observaciones" rows="5"
                                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-gray-700 dark:text-white">{{ old('descripcion_observaciones') }}</textarea>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Proporcione detalles relevantes sobre el riesgo de trabajo
                        </p>
                    </div>

                    <div>
                        <label for="archivo_pdf" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Hoja de Riesgos
                        </label>
                        <div class="relative">
                            <input type="file" name="archivo_pdf" id="archivo_pdf" accept=".pdf"
                                   class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 file:cursor-pointer dark:file:bg-orange-900/30 dark:file:text-orange-200 dark:hover:file:bg-orange-800/30">
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Solo archivos PDF (máx. 2MB)
                        </p>
                    </div>

                    <div>
                        <label for="arch_alta" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Archivo de Alta
                        </label>
                        <div class="relative">
                            <input type="file" name="arch_alta" id="arch_alta" accept=".pdf,.jpg,.jpeg,.png"
                                   class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 file:cursor-pointer dark:file:bg-orange-900/30 dark:file:text-orange-200 dark:hover:file:bg-orange-800/30">
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            PDF o imágenes (máx. 2MB)
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Guardar Riesgo
                        </button>
                        <a href="{{ route('aux.riesgosTrabajo') }}"
                           class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
