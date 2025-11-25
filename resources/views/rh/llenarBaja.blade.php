<x-app-layout>
    <x-navbar />
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-transparent dark:from-gray-700 dark:to-transparent">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Formulario de Solicitud de Baja</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Completa los datos para enviar la solicitud de baja del empleado.</p>
                </div>

                <div class="p-6">
                    @if($solicitudpendiente)
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded-r-lg mb-6">
                            <p class="text-gray-700 dark:text-gray-300">
                                No puede realizar más acciones por el momento, ya que este usuario aún tiene una solicitud pendiente. Favor de esperar la respuesta a su solicitud.
                            </p>
                        </div>
                        <div class="flex justify-center mt-6">
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Regresar
                            </a>
                        </div>
                    @else
                        <form action="{{ route('rh.almacenarBajaNueva', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                            @csrf

                            <div class="bg-gray-50 dark:bg-gray-750/50 p-6 rounded-xl border border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-5">Datos Generales</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label for="fecha_baja" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Baja</label>
                                        <input type="date" name="fecha_baja" id="fecha_baja"
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <div>
                                        <label for="nss" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NSS</label>
                                        <p class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white">
                                            {{ $solicitud->nss }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="fecha_ingreso" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de Ingreso</label>
                                        <p class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white">
                                            {{ $user?->solicitudAlta->fecha_ingreso ? Carbon\Carbon::parse($user?->solicitudAlta->fecha_ingreso)->format('d/m/Y') : 'N/D' }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="incapacidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">¿Incapacidad?</label>
                                        <input type="text" name="incapacidad" id="incapacidad" placeholder="Sí / No o Detalles"
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-750/50 p-6 rounded-xl border border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-5">Datos de Baja</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                        <p class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white">
                                            {{ $user->name }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="empresa" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                                        <p class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white">
                                            {{ $user->empresa }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="punto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Punto</label>
                                        <p class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white">
                                            {{ $user->punto }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="por" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Por</label>
                                        <select name="por" id="por"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Seleccione</option>
                                            <option value="Ausentismo">Ausentismo</option>
                                            <option value="Separación Voluntaria">Separación Voluntaria</option>
                                            <option value="Otro">Otro</option>
                                            <option value="Renuncia">Renuncia</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="ultima_asistencia" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Última Asistencia</label>
                                        <input type="date" name="ultima_asistencia" id="ultima_asistencia"
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <div>
                                        <label for="descuento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descuento por equipo/material no devuelto</label>
                                        <input type="text" name="descuento" id="descuento"
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <div>
                                        <label for="adelanto_nomina" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descuento por Adelanto de Nómina</label>
                                        <input type="text" name="adelanto_nomina" id="adelanto_nomina"
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <div x-data="fileUpload()" class="md:col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Archivo de Baja</label>
                                        <div @dragover.prevent @drop.prevent="handleDrop($event)"
                                             class="flex items-center justify-center w-full p-4 border-2 border-dashed rounded-lg cursor-pointer transition bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 hover:border-blue-500"
                                             :class="{ 'border-blue-500': isDragging }"
                                             @dragenter="isDragging = true"
                                             @dragleave="isDragging = false">
                                            <input type="file" name="archivo_baja" id="archivo_baja" class="hidden" @change="handleFile($event)" x-ref="inputFile">
                                            <div class="text-center">
                                                <p class="text-sm text-gray-600 dark:text-gray-300" x-text="fileName || 'Arrastra un archivo aquí o haz clic para seleccionarlo'"></p>
                                                <button type="button" class="mt-2 text-blue-600 hover:underline" @click="$refs.inputFile.click()">Seleccionar archivo</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-data="fileUpload()" class="md:col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Archivo de Equipo Entregado</label>
                                        <div @dragover.prevent @drop.prevent="handleDrop($event)"
                                             class="flex items-center justify-center w-full p-4 border-2 border-dashed rounded-lg cursor-pointer transition bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 hover:border-blue-500"
                                             :class="{ 'border-blue-500': isDragging }"
                                             @dragenter="isDragging = true"
                                             @dragleave="isDragging = false">
                                            <input type="file" name="arch_equipo_entregado" id="arch_equipo_entregado" class="hidden" @change="handleFile($event)" x-ref="inputFile">
                                            <div class="text-center">
                                                <p class="text-sm text-gray-600 dark:text-gray-300" x-text="fileName || 'Arrastra un archivo aquí o haz clic para seleccionarlo'"></p>
                                                <button type="button" class="mt-2 text-blue-600 hover:underline" @click="$refs.inputFile.click()">Seleccionar archivo</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-data="fileUpload()" class="md:col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Renuncia Firmada</label>
                                        <div @dragover.prevent @drop.prevent="handleDrop($event)"
                                             class="flex items-center justify-center w-full p-4 border-2 border-dashed rounded-lg cursor-pointer transition bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 hover:border-blue-500"
                                             :class="{ 'border-blue-500': isDragging }"
                                             @dragenter="isDragging = true"
                                             @dragleave="isDragging = false">
                                            <input type="file" name="arch_renuncia" id="arch_renuncia" class="hidden" @change="handleFile($event)" x-ref="inputFile">
                                            <div class="text-center">
                                                <p class="text-sm text-gray-600 dark:text-gray-300" x-text="fileName || 'Arrastra un archivo aquí o haz clic para seleccionarlo'"></p>
                                                <button type="button" class="mt-2 text-blue-600 hover:underline" @click="$refs.inputFile.click()">Seleccionar archivo</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="motivo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo (opcional)</label>
                                        <textarea name="motivo" id="motivo" rows="4"
                                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:justify-center gap-3 pt-4">
                                <button type="submit"
                                        class="px-6 py-2.5 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition focus:ring-2 focus:ring-green-300">
                                    Enviar Solicitud
                                </button>
                                <a href="{{ route('dashboard') }}"
                                   class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-center">
                                    Regresar
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

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
