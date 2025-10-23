@php
    $documentosObligatorios = [
        ['label' => 'Nómina PSC', 'name' => 'arch_nomina'],
        ['label' => 'Nómina SPyT', 'name' => 'arch_nomina_spyt'],
        ['label' => 'Nómina Montana', 'name' => 'arch_nomina_montana'],
        ['label' => 'Destajos', 'name' => 'arch_destajo'],
    ];
@endphp

<x-app-layout>
    <x-navbar />

    <div class="py-8 px-4 sm:px-6">
        <div class="max-w-5xl mx-auto">
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

            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 rounded-r text-red-900 px-4 py-3 shadow-md mb-6" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 sm:p-8">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Archivos de Nóminas y Destajos
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Suba los documentos de nómina y destajos correspondientes al periodo
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full">
                                <span class="text-sm font-medium">4</span>
                                <span class="text-xs">documentos</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mb-4">
                    <a href="{{ route('nominas.semanal') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Ingresar registro de pagos semanales
                    </a>
                </div>

                <form action="{{ route('nominas.guardarArchivos') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                        <label for="periodo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Periodo
                            </div>
                        </label>
                        <select id="periodo" name="periodo" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                            <option value="" disabled selected>Selecciona un periodo</option>
                            <option value="1° Enero">1° Enero</option>
                            <option value="2° Enero">2° Enero</option>
                            <option value="1° Febrero">1° Febrero</option>
                            <option value="2° Febrero">2° Febrero</option>
                            <option value="1° Marzo">1° Marzo</option>
                            <option value="2° Marzo">2° Marzo</option>
                            <option value="1° Abril">1° Abril</option>
                            <option value="2° Abril">2° Abril</option>
                            <option value="1° Mayo">1° Mayo</option>
                            <option value="2° Mayo">2° Mayo</option>
                            <option value="1° Junio">1° Junio</option>
                            <option value="2° Junio">2° Junio</option>
                            <option value="1° Julio">1° Julio</option>
                            <option value="2° Julio">2° Julio</option>
                            <option value="1° Agosto">1° Agosto</option>
                            <option value="2° Agosto">2° Agosto</option>
                            <option value="1° Septiembre">1° Septiembre</option>
                            <option value="2° Septiembre">2° Septiembre</option>
                            <option value="1° Octubre">1° Octubre</option>
                            <option value="2° Octubre">2° Octubre</option>
                            <option value="1° Noviembre">1° Noviembre</option>
                            <option value="2° Noviembre">2° Noviembre</option>
                            <option value="1° Diciembre">1° Diciembre</option>
                            <option value="2° Diciembre">2° Diciembre</option>
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Documentos Requeridos</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($documentosObligatorios as $doc)
                                <div x-data="dropFile('{{ $doc['name'] }}')"
                                     x-on:dragover.prevent
                                     x-on:drop.prevent="handleDrop($event)"
                                     class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center hover:border-blue-500 dark:hover:border-blue-400 transition-all duration-200 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md">

                                    <div class="mb-4">
                                        <div class="w-12 h-12 mx-auto bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <label class="block text-gray-900 dark:text-gray-100 font-medium mb-2">
                                        {{ $doc['label'] }} <span class="text-red-500">*</span>
                                    </label>

                                    <input type="file" name="{{ $doc['name'] }}" x-ref="fileInput" class="hidden" @change="handleFileInput" accept=".pdf,.xlsx,.xls,.csv">

                                    <button type="button" @click="$refs.fileInput.click()"
                                            class="mt-3 w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        Seleccionar archivo
                                    </button>

                                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                        Formatos permitidos: PDF, Excel (xlsx, xls), CSV
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
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Subir Documentos
                            </button>
                            <a href="{{ route('dashboard') }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Regresar
                            </a>
                        </div>
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
                        alert('Por favor, seleccione un archivo válido (PDF, Excel o CSV)');
                    }
                },
                handleFileInput(event) {
                    const file = event.target.files[0];
                    if (file && this.isValidFile(file)) {
                        this.fileName = file.name;
                    } else if (file) {
                        alert('Por favor, seleccione un archivo válido (PDF, Excel o CSV)');
                        this.$refs.fileInput.value = '';
                    } else {
                        this.fileName = null;
                    }
                },
                isValidFile(file) {
                    const validTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'];
                    const validExtensions = ['.pdf', '.xlsx', '.xls', '.csv'];
                    const fileName = file.name.toLowerCase();

                    return validTypes.includes(file.type) || validExtensions.some(ext => fileName.endsWith(ext));
                }
            }
        }
    </script>
</x-app-layout>
