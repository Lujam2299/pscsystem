<x-app-layout>
    <x-navbar />
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">

                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Subir Comprobantes - Vale #{{ $vale->id }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Complete los comprobantes para el vale de comida
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full">
                                <span class="text-sm font-medium">{{ $vale->num_elementos }}</span>
                                <span class="text-xs">elementos</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            Monto total: ${{ number_format($vale->monto, 2) }} | Elementos: {{ $vale->num_elementos }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        Suba {{ $vale->num_elementos }} archivos (o más) que sumen ${{ number_format($vale->monto, 2) }}
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 rounded-r text-red-900 px-4 py-3 shadow-md" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <ul class="list-disc pl-5">
                                    @foreach($errors->all() as $error)
                                        <li class="text-sm font-medium">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('vales.comprobantes.subir', $vale->id) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div id="comprobantes-container">
        <div class="comprobante-item flex flex-wrap gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/30">
            <!-- Campo de búsqueda de usuario -->
            <div class="w-full mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Usuario
                </label>
                <div class="relative">
                    <input type="text"
                           class="busqueda-usuario w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="Buscar usuario..."
                           autocomplete="off">
                    <input type="hidden" class="usuario-id" name="comprobantes[0][usuario_id]">
                    <div class="resultados-usuarios absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto hidden"></div>
                </div>
                <div class="usuario-seleccionado mt-2 flex items-center bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-lg hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="nombre-usuario text-sm text-blue-800 dark:text-blue-200"></span>
                    <button type="button" class="ml-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 limpiar-usuario">×</button>
                </div>
            </div>

            <!-- Campo de archivo -->
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Archivo
                </label>
                <input type="file" name="archivos[]" accept=".pdf,.jpg,.jpeg,.png" required
                       class="block w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
            </div>

            <!-- Campo de monto -->
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Monto
                </label>
                <input type="number" step="0.01" name="montos[]" required
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                       placeholder="0.00">
            </div>

            <!-- Botón eliminar -->
            <div class="flex items-end">
                <button type="button" onclick="removeComprobante(this)" class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition duration-200 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <button type="button" onclick="addComprobante()" class="mb-6 inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs rounded-lg transition duration-200 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Añadir comprobante
    </button>

    <div class="flex flex-wrap gap-3 pt-4">
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            Subir Comprobantes
        </button>

        <a href="{{ route('operaciones.valesPendientes') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Cancelar
        </a>
    </div>
</form>
            </div>
        </div>
    </div>

    <script>
    function addComprobante() {
        const container = document.getElementById('comprobantes-container');
        const index = document.querySelectorAll('.comprobante-item').length;
        const newItem = document.createElement('div');
        newItem.className = 'comprobante-item flex flex-wrap gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/30';
        newItem.innerHTML = `
            <div class="w-full mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Usuario
                </label>
                <div class="relative">
                    <input type="text"
                           class="busqueda-usuario w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="Buscar usuario..."
                           autocomplete="off">
                    <input type="hidden" class="usuario-id" name="comprobantes[${index}][usuario_id]">
                    <div class="resultados-usuarios absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto hidden"></div>
                </div>
                <div class="usuario-seleccionado mt-2 flex items-center bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-lg hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="nombre-usuario text-sm text-blue-800 dark:text-blue-200"></span>
                    <button type="button" class="ml-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 limpiar-usuario">×</button>
                </div>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Archivo
                </label>
                <input type="file" name="archivos[]" accept=".pdf,.jpg,.jpeg,.png" required
                       class="block w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Monto
                </label>
                <input type="number" step="0.01" name="montos[]" required
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                       placeholder="0.00">
            </div>

            <div class="flex items-end">
                <button type="button" onclick="removeComprobante(this)" class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition duration-200 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                </button>
            </div>
        `;
        container.appendChild(newItem);

        // Inicializar autocompletado para el nuevo elemento
        inicializarAutocompletado(newItem);
    }

    function removeComprobante(button) {
        const item = button.closest('.comprobante-item');
        if (document.querySelectorAll('.comprobante-item').length > 1) {
            item.remove();
        }
    }

    // Función para inicializar autocompletado en un elemento
    function inicializarAutocompletado(element) {
        const busquedaInput = element.querySelector('.busqueda-usuario');
        const resultadosDiv = element.querySelector('.resultados-usuarios');
        const usuarioIdInput = element.querySelector('.usuario-id');
        const usuarioSeleccionadoDiv = element.querySelector('.usuario-seleccionado');
        const nombreUsuarioSpan = element.querySelector('.nombre-usuario');
        const limpiarBtn = element.querySelector('.limpiar-usuario');

        let timeoutBusqueda = null;

        busquedaInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(timeoutBusqueda);

            if (query.length >= 2) {
                timeoutBusqueda = setTimeout(() => {
                    buscarUsuarios(query, resultadosDiv);
                }, 300);
            } else {
                resultadosDiv.classList.add('hidden');
            }
        });

        limpiarBtn.addEventListener('click', function() {
            busquedaInput.value = '';
            usuarioIdInput.value = '';
            usuarioSeleccionadoDiv.classList.add('hidden');
            resultadosDiv.classList.add('hidden');
        });

        // Ocultar resultados al hacer clic fuera
        document.addEventListener('click', function(event) {
            if (!resultadosDiv.contains(event.target) && busquedaInput !== event.target) {
                resultadosDiv.classList.add('hidden');
            }
        });
    }

    // Función para buscar usuarios
    function buscarUsuarios(query, resultadosDiv) {
        fetch('{{ route('api.usuarios.buscar') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                search: query
            })
        })
        .then(response => response.json())
        .then(data => {
            mostrarResultadosEnElemento(data.usuarios, resultadosDiv);
        })
        .catch(error => {
            console.error('Error al buscar usuarios:', error);
        });
    }

    // Mostrar resultados en un elemento específico
    function mostrarResultadosEnElemento(usuarios, resultadosDiv) {
        resultadosDiv.innerHTML = '';

        if (usuarios.length === 0) {
            resultadosDiv.innerHTML = `
                <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                    No se encontraron usuarios
                </div>
            `;
            resultadosDiv.classList.remove('hidden');
            return;
        }

        usuarios.forEach(usuario => {
            const div = document.createElement('div');
            div.className = 'px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-sm text-gray-900 dark:text-white';
            div.textContent = usuario.name;
            div.onclick = () => {
                // Encontrar los elementos correspondientes
                const busquedaInput = resultadosDiv.closest('.comprobante-item').querySelector('.busqueda-usuario');
                const usuarioIdInput = resultadosDiv.closest('.comprobante-item').querySelector('.usuario-id');
                const usuarioSeleccionadoDiv = resultadosDiv.closest('.comprobante-item').querySelector('.usuario-seleccionado');
                const nombreUsuarioSpan = resultadosDiv.closest('.comprobante-item').querySelector('.nombre-usuario');

                busquedaInput.value = usuario.name;
                usuarioIdInput.value = usuario.id;
                nombreUsuarioSpan.textContent = usuario.name;
                usuarioSeleccionadoDiv.classList.remove('hidden');
                resultadosDiv.classList.add('hidden');
            };
            resultadosDiv.appendChild(div);
        });

        resultadosDiv.classList.remove('hidden');
    }

    // Inicializar autocompletado para el primer elemento
    document.addEventListener('DOMContentLoaded', function() {
        const primerComprobante = document.querySelector('.comprobante-item');
        if (primerComprobante) {
            inicializarAutocompletado(primerComprobante);
        }
    });

    </script>
</x-app-layout>
