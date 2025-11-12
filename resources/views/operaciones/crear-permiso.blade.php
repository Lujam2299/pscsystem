<style>
    #suggestions-container {
    position: absolute;
    z-index: 10;
    margin-top: 0.25rem;
    width: 100%;
    max-height: 15rem;
    overflow-y: auto;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    background-color: white;
}
</style>
<x-app-layout>
    <x-navbar />
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Solicitar Permiso Especial</h1>

                <form action="{{ route('operaciones.guardarPermiso') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <div>
                                <label for="empleado_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Empleado
                                </label>
                                <input type="text"
                                    id="empleado_nombre"
                                    placeholder="Escribe el nombre del empleado..."
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                                    autocomplete="off">
                                <input type="hidden" name="user_id" id="user_id" value="">

                                <!-- Contenedor de sugerencias -->
                                <div id="suggestions-container" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md max-h-60 overflow-auto hidden">
                                    <ul id="suggestions-list" class="py-1"></ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Permiso
                            </label>
                            <select name="tipo" id="tipo" required
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                                <option value="">Selecciona un tipo</option>
                                <option value="paternidad">Paternidad</option>
                                <option value="maternidad">Maternidad</option>
                                <option value="defuncion">Defunción</option>
                            </select>
                        </div>

                        <div>
                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de Inicio
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" required
                                   class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div>
                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de Fin
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin" required
                                   class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div>
                            <label for="con_goce" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                ¿Con Goce de Sueldo?
                            </label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="con_goce" value="1" class="h-4 w-4 text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Sí</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="con_goce" value="0" class="h-4 w-4 text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">No</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="archivo_justificante" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Archivo Justificante (opcional)
                            </label>
                            <input type="file" name="archivo_justificante" id="archivo_justificante" accept="image/*,.pdf"
                                   class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="motivo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Motivo
                        </label>
                        <textarea name="motivo" id="motivo" rows="3"
                                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                                  placeholder="Describe el motivo del permiso..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Registrar Permiso
                        </button>
                        <a href="{{ route('operaciones.permisosIndex') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
let debounceTimer;

document.getElementById('empleado_nombre').addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const query = this.value.trim();

    if (query.length < 2) {
        document.getElementById('suggestions-container').classList.add('hidden');
        return;
    }

    debounceTimer = setTimeout(() => {
        fetch(`{{ route('api.empleados.buscar') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('suggestions-container');
                const list = document.getElementById('suggestions-list');
                list.innerHTML = '';

                if (data.length === 0) {
                    list.innerHTML = '<li class="px-4 py-2 text-gray-500 dark:text-gray-400">No se encontraron empleados</li>';
                    container.classList.remove('hidden');
                    return;
                }

                data.forEach(empleado => {
                    const li = document.createElement('li');
                    li.classList.add('px-4', 'py-2', 'cursor-pointer', 'hover:bg-gray-100', 'dark:hover:bg-gray-700', 'text-sm');
                    li.innerHTML = `
                        <div><strong>${empleado.name}</strong></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">${empleado.punto} - ${empleado.empresa}</div>
                    `;
                    li.onclick = () => {
                        document.getElementById('empleado_nombre').value = empleado.name;
                        document.getElementById('user_id').value = empleado.id;
                        container.classList.add('hidden');
                    };
                    list.appendChild(li);
                });

                container.classList.remove('hidden');
            });
    }, 300); // 300ms debounce
});

// Ocultar sugerencias al hacer clic fuera
document.addEventListener('click', function(e) {
    const container = document.getElementById('suggestions-container');
    const input = document.getElementById('empleado_nombre');
    if (!input.contains(e.target) && !container.contains(e.target)) {
        container.classList.add('hidden');
    }
});
</script>
