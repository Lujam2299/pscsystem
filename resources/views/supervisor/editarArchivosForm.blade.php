@php
    use Illuminate\Support\Facades\Auth;

    $documentosObligatorios = [];

    if ($tipo === 'armado') {
        $documentosObligatorios = [
            ['label' => 'Solicitud/CV', 'name' => 'arch_solicitud_empleo'],
            ['label' => 'INE', 'name' => 'arch_ine'],
            ['label' => 'NSS', 'name' => 'arch_nss'],
            ['label' => 'CURP', 'name' => 'arch_curp'],
            ['label' => 'RFC', 'name' => 'arch_rfc'],
            ['label' => 'Acta de Nacimiento', 'name' => 'arch_acta_nacimiento'],
            ['label' => 'Comprobante de Estudios', 'name' => 'arch_comprobante_estudios'],
            ['label' => 'Comprobante de Domicilio', 'name' => 'arch_comprobante_domicilio'],
            ['label' => 'Carta de Recomendación Laboral', 'name' => 'arch_carta_rec_laboral'],
            ['label' => 'Carta de Recomendación Personal', 'name' => 'arch_carta_rec_personal'],
            ['label' => 'Cartilla Militar', 'name' => 'arch_cartilla_militar'],
            ['label' => 'Antidoping', 'name' => 'arch_antidoping'],
            ['label' => 'Carta de No Antecedentes Penales', 'name' => 'arch_carta_no_penales'],
            ['label' => 'Contrato', 'name' => 'arch_contrato'],
            ['label' => 'Fotografía (Reciente)', 'name' => 'arch_foto'],
        ];
    } else {
        $documentosObligatorios = [
            ['label' => 'Solicitud/CV', 'name' => 'arch_solicitud_empleo'],
            ['label' => 'INE', 'name' => 'arch_ine'],
            ['label' => 'NSS', 'name' => 'arch_nss'],
            ['label' => 'CURP', 'name' => 'arch_curp'],
            ['label' => 'RFC', 'name' => 'arch_rfc'],
            ['label' => 'Acta de Nacimiento', 'name' => 'arch_acta_nacimiento'],
            ['label' => 'Comprobante de Estudios', 'name' => 'arch_comprobante_estudios'],
            ['label' => 'Comprobante de Domicilio', 'name' => 'arch_comprobante_domicilio'],
            ['label' => 'Carta de Recomendación Laboral', 'name' => 'arch_carta_rec_laboral'],
            ['label' => 'Carta de Recomendación Personal', 'name' => 'arch_carta_rec_personal'],
            ['label' => 'Contrato', 'name' => 'arch_contrato'],
            ['label' => 'Fotografía (Reciente)', 'name' => 'arch_foto'],
        ];
    }

    $documentosOpcionales = [];

    if ($tipo !== 'armado') {
        $documentosOpcionales[] = ['label' => 'Cartilla Militar', 'name' => 'arch_cartilla_militar'];
        $documentosOpcionales[] = ['label' => 'Carta de Antecedentes No Penales', 'name' => 'arch_carta_no_penales'];
    }

    $documentosOpcionales = array_merge($documentosOpcionales, [
        ['label' => 'Comprobante INFONAVIT', 'name' => 'arch_infonavit'],
        ['label' => 'Comprobante FONACOT', 'name' => 'arch_fonacot'],
        ['label' => 'Licencia de Conducir', 'name' => 'arch_licencia_conducir'],
        ['label' => 'Visa', 'name' => 'visa'],
        ['label' => 'Pasaporte', 'name' => 'pasaporte'],
    ]);
@endphp

<x-app-layout>
    <x-navbar />

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">

                <!-- Encabezado Principal -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 dark:from-blue-700 dark:to-indigo-800 px-6 py-8">
                    <h2 class="text-3xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        {{ Auth::user()->rol == 'Supervisor' ? 'Solicitud de Alta de Usuario' : 'Editar Documentos de Usuario' }}
                    </h2>
                    <p class="mt-2 text-blue-100">Sube o reemplaza los documentos del usuario.</p>
                </div>

                <div class="p-6">

                    <form action="{{ route(($flujoAdministrativo ?? false) ? 'admin.actualizarDocumentacionUsuario' : 'sup.guardarArchivosEditados', $id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Documentos Obligatorios -->
                        <div class="mb-10">
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-4 pb-2 border-b border-red-200 dark:border-red-700 flex items-center">
                                <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                                Documentos Obligatorios
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($documentosObligatorios as $doc)
                                    <div x-data="dropFile('{{ $doc['name'] }}')" x-on:dragover.prevent x-on:drop.prevent="handleDrop($event)" class="border-2 border-dashed border-red-300 dark:border-red-700 rounded-xl p-4 bg-red-50/30 dark:bg-red-900/10 hover:border-red-500 dark:hover:border-red-600 transition-colors duration-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-sm font-medium text-red-700 dark:text-red-300">
                                                {{ $doc['label'] }} <span class="text-red-500">*</span>
                                            </label>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>

                                        @if ($documentacion && $documentacion->{$doc['name']})
                                            <div class="mb-2 text-xs text-green-600 dark:text-green-400">
                                                <span>Archivo actual:</span>
                                                <a href="{{ asset($documentacion->{$doc['name']}) }}" target="_blank" class="inline-flex items-center underline text-blue-500 dark:text-blue-400 hover:no-underline">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Ver {{ $doc['label'] }}
                                                </a>
                                            </div>
                                        @endif

                                        <input type="file" name="{{ $doc['name'] }}" x-ref="fileInput" class="hidden" @change="handleFileInput">

                                        <button type="button" @click="$refs.fileInput.click()" class="w-full mt-1 px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors duration-200 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            Subir Nuevo
                                        </button>

                                        <template x-if="fileName">
                                            <div class="mt-2 p-2 bg-green-100 dark:bg-green-900/30 rounded-lg border border-green-200 dark:border-green-700">
                                                <p class="text-xs text-green-700 dark:text-green-300 truncate" x-text="fileName"></p>
                                            </div>
                                        </template>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Documentos Opcionales -->
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center">
                                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                                Documentos Opcionales
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($documentosOpcionales as $doc)
                                    <div x-data="dropFile('{{ $doc['name'] }}')" x-on:dragover.prevent x-on:drop.prevent="handleDrop($event)" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 bg-gray-50/30 dark:bg-gray-700/10 hover:border-blue-400 dark:hover:border-blue-500 transition-colors duration-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ $doc['label'] }}
                                            </label>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                        </div>

                                        @if ($documentacion && $documentacion->{$doc['name']})
                                            <div class="mb-2 text-xs text-green-600 dark:text-green-400">
                                                <span>Archivo actual:</span>
                                                <a href="{{ asset($documentacion->{$doc['name']}) }}" target="_blank" class="inline-flex items-center underline text-blue-500 dark:text-blue-400 hover:no-underline">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Ver {{ $doc['label'] }}
                                                </a>
                                            </div>
                                        @endif

                                        <input type="file" name="{{ $doc['name'] }}" x-ref="fileInput" class="hidden" @change="handleFileInput">

                                        <button type="button" @click="$refs.fileInput.click()" class="w-full mt-1 px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors duration-200 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            Subir Nuevo
                                        </button>

                                        <template x-if="fileName">
                                            <div class="mt-2 p-2 bg-green-100 dark:bg-green-900/30 rounded-lg border border-green-200 dark:border-green-700">
                                                <p class="text-xs text-green-700 dark:text-green-300 truncate" x-text="fileName"></p>
                                            </div>
                                        </template>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-center gap-4">
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-all duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Guardar Cambios
                            </button>
                            @if(Auth::user()->rol == 'Supervisor')
                                <a href="{{ route('sup.solicitud.detalle', $id) }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-all duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Regresar
                                </a>
                            @else
                                <a href="{{ route('admin.editarUsuarioForm',$user->id) }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-all duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Regresar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function dropFile(inputName) {
            return {
                fileName: null,
                handleDrop(event) {
                    const file = event.dataTransfer.files[0];
                    if (file) {
                        this.$refs.fileInput.files = event.dataTransfer.files;
                        this.fileName = file.name;
                    }
                },
                handleFileInput(event) {
                    const file = event.target.files[0];
                    this.fileName = file ? file.name : null;
                }
            }
        }
    </script>
</x-app-layout>
