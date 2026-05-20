<x-app-layout>
    <x-navbar />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="mb-4 text-center mt-4">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-2xl mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Solicitud de Baja</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 max-w-lg mx-auto">
                        Completa los datos para procesar la baja del empleado.
                    </p>
                </div>
                {{-- ESTADO: SOLICITUD PENDIENTE --}}
                @if($solicitudpendiente)
                    <div class="p-8">
                        <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-400 rounded-r-xl p-5 mb-6">
                            <div class="flex items-start gap-3">
                                <svg class="h-6 w-6 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200">Acción en espera</h3>
                                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                                        Este usuario ya tiene una solicitud de baja en proceso. Por favor, espera la resolución antes de realizar nuevas acciones.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Regresar al Dashboard
                            </a>
                        </div>
                    </div>

                {{-- FORMULARIO PRINCIPAL --}}
                @else
                    <form action="{{ route('rh.almacenarBajaNueva', $user->id) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-8">
                        @csrf

                        {{-- SECCIÓN 1: DATOS GENERALES --}}
                        <section>
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Datos Generales</h2>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Fecha de Baja --}}
                                <div>
                                    <label for="fecha_baja" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Fecha de Baja <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="fecha_baja" id="fecha_baja"
                                           class="block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 py-3 px-4 text-base transition-all">
                                </div>

                                {{-- NSS (Solo Lectura) --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">NSS</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        </span>
                                        <p class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 pl-10 pr-4 py-3 text-gray-700 dark:text-gray-300 font-mono text-sm">
                                            {{ $solicitud->nss }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Fecha de Ingreso (Solo Lectura) --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Fecha de Ingreso</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </span>
                                        <p class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 pl-10 pr-4 py-3 text-gray-700 dark:text-gray-300 font-mono text-sm">
                                            {{ $user?->solicitudAlta->fecha_ingreso ? Carbon\Carbon::parse($user?->solicitudAlta->fecha_ingreso)->format('d/m/Y') : 'N/D' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Incapacidad --}}
                                <div>
                                    <label for="incapacidad" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">¿Incapacidad?</label>
                                    <input type="text" name="incapacidad" id="incapacidad" placeholder="Sí / No o Detalles"
                                           class="block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 py-3 px-4 text-base transition-all placeholder-gray-400">
                                </div>
                            </div>
                        </section>

                        {{-- SECCIÓN 2: DATOS DE LA BAJA --}}
                        <section>
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                                <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Detalles de la Baja</h2>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Nombre (Solo Lectura) --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nombre del Empleado</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </span>
                                        <p class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 pl-10 pr-4 py-3 text-gray-900 dark:text-white font-medium">
                                            {{ $user->name }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Empresa y Punto --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Empresa</label>
                                    <p class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 px-4 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $user->empresa }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Punto / Ubicación</label>
                                    <p class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 px-4 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $user->punto ?? 'N/D' }}
                                    </p>
                                </div>

                                {{-- Motivo de Baja --}}
                                <div>
                                    <label for="por" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Motivo de Baja <span class="text-red-500">*</span>
                                    </label>
                                    <select name="por" id="por"
                                            class="block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 py-3 px-4 text-base transition-all cursor-pointer appearance-none">
                                        <option value="" disabled selected>Seleccione una opción...</option>
                                        <option value="Ausentismo">Ausentismo</option>
                                        <option value="Separación Voluntaria">Separación Voluntaria</option>
                                        <option value="Otro">Otro</option>
                                        <option value="Renuncia">Renuncia</option>
                                    </select>
                                </div>

                                {{-- Última Asistencia --}}
                                <div>
                                    <label for="ultima_asistencia" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Última Asistencia</label>
                                    <input type="date" name="ultima_asistencia" id="ultima_asistencia"
                                           class="block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 py-3 px-4 text-base transition-all">
                                </div>

                                {{-- Descuentos --}}
                                <div>
                                    <label for="descuento" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Descuento por Equipo/Material</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">$</span>
                                        <input type="text" name="descuento" id="descuento" placeholder="0.00"
                                               class="block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 py-3 pl-8 pr-4 text-base transition-all placeholder-gray-400">
                                    </div>
                                </div>
                                <div>
                                    <label for="adelanto_nomina" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Adelanto de Nómina</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">$</span>
                                        <input type="text" name="adelanto_nomina" id="adelanto_nomina" placeholder="0.00"
                                               class="block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 py-3 pl-8 pr-4 text-base transition-all placeholder-gray-400">
                                    </div>
                                </div>

                                {{-- Upload de Archivos --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Documentación Requerida</label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                        {{-- Archivo de Baja --}}
                                        <div x-data="fileUpload()" class="relative group">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 text-center">Archivo de Baja</label>
                                            <div @dragover.prevent @drop.prevent="handleDrop($event)"
                                                 class="flex flex-col items-center justify-center w-full p-4 border-2 border-dashed rounded-xl cursor-pointer transition-all bg-gray-50 dark:bg-gray-700/50 border-gray-300 dark:border-gray-600 hover:border-red-400 hover:bg-red-50/50 dark:hover:bg-red-900/10"
                                                 :class="{ 'border-red-400 bg-red-50/50 dark:bg-red-900/10': isDragging }"
                                                 @dragenter="isDragging = true"
                                                 @dragleave="isDragging = false">
                                                <input type="file" name="archivo_baja" id="archivo_baja" class="hidden" @change="handleFile($event)" x-ref="inputFile" accept=".pdf,.doc,.docx,.jpg,.png">
                                                <svg class="h-8 w-8 text-gray-400 dark:text-gray-500 mb-2 group-hover:text-red-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                </svg>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center px-2" x-text="fileName || 'Arrastra o haz clic'"></p>
                                            </div>
                                        </div>

                                        {{-- Equipo Entregado --}}
                                        <div x-data="fileUpload()" class="relative group">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 text-center">Equipo Entregado</label>
                                            <div @dragover.prevent @drop.prevent="handleDrop($event)"
                                                 class="flex flex-col items-center justify-center w-full p-4 border-2 border-dashed rounded-xl cursor-pointer transition-all bg-gray-50 dark:bg-gray-700/50 border-gray-300 dark:border-gray-600 hover:border-red-400 hover:bg-red-50/50 dark:hover:bg-red-900/10"
                                                 :class="{ 'border-red-400 bg-red-50/50 dark:bg-red-900/10': isDragging }"
                                                 @dragenter="isDragging = true"
                                                 @dragleave="isDragging = false">
                                                <input type="file" name="arch_equipo_entregado" id="arch_equipo_entregado" class="hidden" @change="handleFile($event)" x-ref="inputFile" accept=".pdf,.doc,.docx,.jpg,.png">
                                                <svg class="h-8 w-8 text-gray-400 dark:text-gray-500 mb-2 group-hover:text-red-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center px-2" x-text="fileName || 'Arrastra o haz clic'"></p>
                                            </div>
                                        </div>

                                        {{-- Renuncia Firmada --}}
                                        <div x-data="fileUpload()" class="relative group">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 text-center">Renuncia Firmada</label>
                                            <div @dragover.prevent @drop.prevent="handleDrop($event)"
                                                 class="flex flex-col items-center justify-center w-full p-4 border-2 border-dashed rounded-xl cursor-pointer transition-all bg-gray-50 dark:bg-gray-700/50 border-gray-300 dark:border-gray-600 hover:border-red-400 hover:bg-red-50/50 dark:hover:bg-red-900/10"
                                                 :class="{ 'border-red-400 bg-red-50/50 dark:bg-red-900/10': isDragging }"
                                                 @dragenter="isDragging = true"
                                                 @dragleave="isDragging = false">
                                                <input type="file" name="arch_renuncia" id="arch_renuncia" class="hidden" @change="handleFile($event)" x-ref="inputFile" accept=".pdf,.doc,.docx,.jpg,.png">
                                                <svg class="h-8 w-8 text-gray-400 dark:text-gray-500 mb-2 group-hover:text-red-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center px-2" x-text="fileName || 'Arrastra o haz clic'"></p>
                                            </div>
                                        </div>

                                    </div>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 text-center">
                                        Formatos aceptados: PDF, DOC, DOCX, JPG, PNG • Máx. 10MB
                                    </p>
                                </div>

                                {{-- Motivo Adicional (Textarea) --}}
                                <div class="md:col-span-2">
                                    <label for="motivo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Motivo Detallado (opcional)</label>
                                    <textarea name="motivo" id="motivo" rows="4" placeholder="Describe brevemente las circunstancias de la baja..."
                                              class="block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 py-3 px-4 text-base transition-all placeholder-gray-400 resize-none"></textarea>
                                </div>
                            </div>
                        </section>

                        {{-- BOTONES DE ACCIÓN --}}
                        <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-end gap-4">
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Enviar Solicitud
                            </button>
                            <a href="{{ route('dashboard') }}"
                               class="w-full sm:w-auto px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all shadow-sm text-center">
                                Cancelar
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- SCRIPT ORIGINAL INTACTO --}}
    <script>
        function fileUpload() {
            return {
                isDragging: false,
                fileName: '',
                handleFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                    }
                },
                handleDrop(event) {
                    const file = event.dataTransfer.files[0];
                    if (file) {
                        this.$refs.inputFile.files = event.dataTransfer.files;
                        this.fileName = file.name;
                    }
                    this.isDragging = false;
                }
            }
        }
    </script>
</x-app-layout>
