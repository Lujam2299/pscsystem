<x-app-layout>
    <x-navbar />

    <div class="py-8 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
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
                @elseif(session('error'))
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

                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Subir Archivos de Cédulas
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Cargue los archivos de cédulas EMA (mensual) y EVA (bimestral)
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <!-- EMA (Mensual) -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                        <div class="border-b border-gray-200 dark:border-gray-600 pb-4 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                EMA (Mensual)
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Archivos de cédula mensual
                            </p>
                        </div>

                        @if ($emaSubida)
                            <div class="bg-yellow-100 dark:bg-yellow-900/30 border-l-4 border-yellow-500 rounded-r text-yellow-900 dark:text-yellow-200 px-4 py-3 shadow-md" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium">Ya se han subido los archivos EMA para este mes.</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <form action="{{ route('aux.cedulasupload', 'ema') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    @foreach (['ema_spyt' => 'PDF SPyT', 'ema_psc' => 'PDF PSC', 'ema_montana' => 'PDF Montana'] as $name => $label)
                                        <div x-data="dropFile('{{ $name }}')"
                                             x-on:dragover.prevent
                                             x-on:drop.prevent="handleDrop($event)"
                                             class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-green-400 dark:hover:border-green-500 transition-colors duration-200 bg-white dark:bg-gray-800">
                                            <div class="flex flex-col items-center">
                                                <div class="flex-shrink-0 h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                </div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                                    {{ $label }}
                                                </label>
                                                <input type="file"
                                                       name="{{ $name }}"
                                                       x-ref="fileInput"
                                                       accept="application/pdf"
                                                       class="hidden"
                                                       @change="handleFileInput"
                                                       required>
                                                <button type="button"
                                                        @click="$refs.fileInput.click()"
                                                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                    Seleccionar archivo
                                                </button>
                                                <template x-if="fileName">
                                                    <div class="mt-3 p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                                        <p class="text-xs text-green-600 dark:text-green-400 flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            <span class="font-medium">Archivo:</span>
                                                            <span x-text="fileName" class="truncate ml-1"></span>
                                                        </p>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                                    <button type="submit"
                                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        Subir EMA
                                    </button>
                                    <a href="{{ url()->previous() }}"
                                       class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Cancelar
                                    </a>
                                </div>
                            </form>
                        @endif
                    </div>

                    <!-- EVA (Bimestral) -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                        <div class="border-b border-gray-200 dark:border-gray-600 pb-4 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                EBA (Bimestral)
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Archivos de cédula bimestral
                            </p>
                        </div>

                        @if($evaSubida)
                            <div class="bg-yellow-100 dark:bg-yellow-900/30 border-l-4 border-yellow-500 rounded-r text-yellow-900 dark:text-yellow-200 px-4 py-3 shadow-md" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium">Ya se han subido los archivos EVA para este bimestre.</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <form action="{{ route('aux.cedulasupload', 'eva') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    @foreach(['eva_spyt' => 'PDF SPyT', 'eva_psc' => 'PDF PSC', 'eva_montana' => 'PDF Montana'] as $name => $label)
                                        <div x-data="dropFile('{{ $name }}')"
                                             x-on:dragover.prevent
                                             x-on:drop.prevent="handleDrop($event)"
                                             class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-colors duration-200 bg-white dark:bg-gray-800">
                                            <div class="flex flex-col items-center">
                                                <div class="flex-shrink-0 h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-4">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                                    {{ $label }}
                                                </label>
                                                <input type="file"
                                                       name="{{ $name }}"
                                                       x-ref="fileInput"
                                                       accept="application/pdf"
                                                       class="hidden"
                                                       @change="handleFileInput"
                                                       required>
                                                <button type="button"
                                                        @click="$refs.fileInput.click()"
                                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Seleccionar archivo
                                                </button>
                                                <template x-if="fileName">
                                                    <div class="mt-3 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                                        <p class="text-xs text-blue-600 dark:text-blue-400 flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            <span class="font-medium">Archivo:</span>
                                                            <span x-text="fileName" class="truncate ml-1"></span>
                                                        </p>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                                    <button type="submit"
                                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Subir EBA
                                    </button>
                                    <a href="{{ url()->previous() }}"
                                       class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Cancelar
                                    </a>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-center">
                        <a href="{{ url()->previous() }}"
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
    function dropFile(inputName) {
        return {
            fileName: null,
            handleDrop(event) {
                const file = event.dataTransfer.files[0];
                if (file && file.type === 'application/pdf') {
                    this.$refs.fileInput.files = event.dataTransfer.files;
                    this.fileName = file.name;
                } else {
                    alert('Por favor, seleccione un archivo PDF válido.');
                }
            },
            handleFileInput(event) {
                const file = event.target.files[0];
                if (file && file.type === 'application/pdf') {
                    this.fileName = file.name;
                } else if (file) {
                    alert('Por favor, seleccione un archivo PDF válido.');
                    this.$refs.fileInput.value = '';
                } else {
                    this.fileName = null;
                }
            }
        }
    }
</script>
