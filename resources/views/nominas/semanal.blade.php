<x-app-layout>
    <x-navbar />

    <div class="py-8 px-4 sm:px-6">
        <div class="max-w-2xl mx-auto">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 rounded-r text-green-900 px-4 py-3 shadow-md mb-6" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 sm:p-8">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Registro Semanal de Pagos
                        </h1>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Sube el archivo de pagos correspondiente a una semana específica.
                    </p>
                </div>

                <form action="{{ route('nominas.guardarSemanal') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Selector de Mes -->
                    <div>
                        <label for="mes" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Mes
                        </label>
                        <select id="mes" name="mes" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white">
                            <option value="" disabled selected>Selecciona un mes</option>
                            @foreach ([
                                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                            ] as $index => $nombre)
                                <option value="{{ $index + 1 }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selector de Semana -->
                    <div>
                        <label for="semana" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Semana del mes
                        </label>
                        <select id="semana" name="semana" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white">
                            <option value="" disabled selected>Selecciona la semana</option>
                            <option value="1">1ª semana</option>
                            <option value="2">2ª semana</option>
                            <option value="3">3ª semana</option>
                            <option value="4">4ª semana</option>
                            <option value="5">5ª semana (si aplica)</option>
                        </select>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Nota: Algunos meses tienen 5 semanas. El sistema validará si la combinación es válida.
                        </p>
                    </div>

                    <!-- Archivo -->
                    <div x-data="dropFile('archivo_semanal')"
                         x-on:dragover.prevent
                         x-on:drop.prevent="handleDrop($event)"
                         class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition-all duration-200 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md">

                        <div class="mb-4">
                            <div class="w-12 h-12 mx-auto bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>

                        <label class="block text-gray-900 dark:text-gray-100 font-medium mb-2">
                            Archivo de pagos semanales <span class="text-red-500">*</span>
                        </label>

                        <input type="file" name="archivo_semanal" x-ref="fileInput" class="hidden" @change="handleFileInput" accept=".xlsx,.xls,.csv">

                        <button type="button" @click="$refs.fileInput.click()"
                                class="mt-3 w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Seleccionar archivo
                        </button>

                        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            Formatos permitidos: Excel (xlsx, xls) o CSV
                        </div>

                        <template x-if="fileName">
                            <div class="mt-3 p-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                <p class="text-sm text-green-700 dark:text-green-300 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-medium">Archivo:</span> <span x-text="fileName" class="truncate ml-1"></span>
                                </p>
                            </div>
                        </template>
                    </div>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row gap-3">
                        <button type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            Subir Registro Semanal
                        </button>
                        <a href="{{ route('nominas.subidaArchivos') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            ← Regresar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function dropFile(inputName) {
            return {
                fileName: null,
                handleDrop(event) {
                    const file = event.dataTransfer.files[0];
                    if (file && this.isValidFile(file)) {
                        this.$refs.fileInput.files = event.dataTransfer.files;
                        this.fileName = file.name;
                    } else {
                        alert('Por favor, seleccione un archivo válido (Excel o CSV)');
                    }
                },
                handleFileInput(event) {
                    const file = event.target.files[0];
                    if (file && this.isValidFile(file)) {
                        this.fileName = file.name;
                    } else if (file) {
                        alert('Por favor, seleccione un archivo válido (Excel o CSV)');
                        this.$refs.fileInput.value = '';
                    } else {
                        this.fileName = null;
                    }
                },
                isValidFile(file) {
                    const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'];
                    const validExtensions = ['.xlsx', '.xls', '.csv'];
                    const fileName = file.name.toLowerCase();
                    return validTypes.includes(file.type) || validExtensions.some(ext => fileName.endsWith(ext));
                }
            }
        }
    </script>
</x-app-layout>
