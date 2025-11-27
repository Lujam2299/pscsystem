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
                @if(session('success'))
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

                <!-- Contenedor de advertencia dinámica -->
                <div id="contenedor-advertencia" class="hidden">
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-r">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-yellow-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938-4h13.856c1.54 0 2.502-1.667 1.732-3l-7.02-9.36a2 2 0 00-3.464 0l-7.02 9.36c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <p class="font-bold">Advertencia:</p>
                                <p>Ya existe un registro de asistencia para el punto <strong>OFICINA</strong> en la fecha <strong id="fecha-advertencia">XX/XX/XXXX</strong>. Este registro podría reemplazar el anterior.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Registro de Asistencia – Montana
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Marque la asistencia de los usuarios de Montana.
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-3 py-1 rounded-full">
                                <span class="text-sm font-medium">{{ $usuarios->count() }}</span>
                                <span class="text-xs">empleados</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('rh.guardarAsistenciaMontana') }}" method="POST">
                    @csrf

                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-6">
                        <label for="fecha_registro" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 px-3 py-2">
                            Fecha de Registro
                        </label>
                        <input type="date" name="fecha_registro" id="fecha_registro" value="{{ now()->toDateString() }}"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                               onchange="verificarVacaciones(this.value)"
                               required>
                    </div>

                    @if($usuarios->isEmpty())
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No hay personal activo en Montana.
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($usuarios as $user)
                                <div class="usuario-card relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-md"
                                     data-user-id="{{ $user->id }}">
                                    <div class="p-5">
                                        <div class="flex items-center space-x-4 mb-4">
                                            @if($user->solicitudAlta?->documentacion?->arch_foto)
                                                <img src="{{ asset('storage/' . str_replace('storage/', '', $user->solicitudAlta->documentacion->arch_foto)) }}"
                                                     alt="Foto de {{ $user->name }}"
                                                     class="w-16 h-16 rounded-full object-cover border-2 border-white dark:border-gray-600 shadow-sm">
                                            @else
                                                <div class="flex-shrink-0 w-16 h-16 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center">
                                                    <span class="text-white font-medium text-lg">
                                                        {{ substr($user->name ?? '', 0, 2) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                                    {{ $user->name }}
                                                </h2>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                    {{ $user->empresa }}
                                                </p>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 mt-1">
                                                    {{ $user->rol }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Asistió -->
                                        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                            <label for="asistio_{{ $user->id }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Asistió
                                            </label>
                                            <input type="checkbox"
                                                   name="asistio[]"
                                                   value="{{ $user->id }}"
                                                   id="asistio_{{ $user->id }}"
                                                   class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded cursor-pointer"
                                                   onchange="toggleAsistenciaPanel(this, {{ $user->id }})">
                                        </div>

                                        <!-- Faltó -->
                                        <div class="flex items-center justify-between pt-2">
                                            <label for="falto_{{ $user->id }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Faltó
                                            </label>
                                            <input type="checkbox"
                                                   name="falto[]"
                                                   value="{{ $user->id }}"
                                                   id="falto_{{ $user->id }}"
                                                   class="h-5 w-5 text-red-600 focus:ring-red-500 border-gray-300 rounded cursor-pointer"
                                                   onchange="toggleFaltaPanel(this, {{ $user->id }})">
                                        </div>

                                        <!-- Panel condicional: Retraso -->
                                        <div id="retraso_panel_{{ $user->id }}" class="mt-3 hidden space-y-3 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800">
                                            <label for="retraso_minutos_{{ $user->id }}" class="block text-xs font-medium text-blue-700 dark:text-blue-300 mb-1">
                                                Minutos de Retraso
                                            </label>
                                            <input type="number"
                                                   name="retraso_minutos[{{ $user->id }}]"
                                                   id="retraso_minutos_{{ $user->id }}"
                                                   min="1"
                                                   max="599"
                                                   placeholder="0"
                                                   class="block w-full px-2 py-1 text-xs border border-blue-300 dark:border-blue-700 rounded bg-white dark:bg-blue-800/50 text-gray-900 dark:text-white">
                                        </div>

                                        <div id="tipo_falta_panel_{{ $user->id }}" class="mt-3 hidden space-y-3 bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                            <label class="block text-xs font-medium text-yellow-700 dark:text-yellow-300 mb-2">
                                                Tipo de Falta
                                            </label>
                                            <div class="flex space-x-4">
                                                <label class="inline-flex items-center">
                                                    <input type="radio"
                                                           name="tipo_falta[{{ $user->id }}]"
                                                           value="justificada"
                                                           class="h-4 w-4 text-green-600">
                                                    <span class="ml-2 text-sm text-green-700 dark:text-green-300">Justificada</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio"
                                                           name="tipo_falta[{{ $user->id }}]"
                                                           value="injustificada"
                                                           class="h-4 w-4 text-red-600">
                                                    <span class="ml-2 text-sm text-red-700 dark:text-red-300">Injustificada</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-8">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                            <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Observaciones
                            </label>
                            <textarea name="observaciones" id="observaciones" rows="4"
                                      class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Escribe tus observaciones aquí..."></textarea>
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
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    let usuariosEnVacaciones = [];
    let usuariosConPermisos = [];

    function verificarVacaciones(fecha) {
    Promise.all([
        fetch(`/api/usuarios-vacaciones/${fecha}`).then(r => r.json()),
        fetch(`/api/usuarios-permisos/${fecha}`).then(r => r.json()),
        fetch(`/api/verificar-asistencia/${fecha}`).then(r => r.json()) // 👈 Nuevo
    ])
    .then(([vacacionesData, permisosData, asistenciaData]) => {
        usuariosEnVacaciones = vacacionesData.usuarios || [];
        usuariosConPermisos = permisosData.usuarios || [];
        actualizarEstadoUsuarios();

        // Actualizar advertencia de duplicado
        const contenedor = document.getElementById('contenedor-advertencia');
        const fechaSpan = document.getElementById('fecha-advertencia');

        if (asistenciaData.existe) {
            fechaSpan.textContent = asistenciaData.fecha_formateada;
            contenedor.classList.remove('hidden');
        } else {
            contenedor.classList.add('hidden');
        }
    });
}

    function actualizarEstadoUsuarios() {
    // Reiniciar todos los usuarios
    document.querySelectorAll('.usuario-card').forEach(card => {
        card.classList.remove('opacity-50');
        const asistioCb = card.querySelector('input[name="asistio[]"]');
        const faltoCb = card.querySelector('input[name="falto[]"]');
        if (asistioCb) asistioCb.disabled = false;
        if (faltoCb) faltoCb.disabled = false;

        // Ocultar badges de vacaciones y permisos
        const badgeVacaciones = card.querySelector('.vacaciones-badge');
        const badgePermiso = card.querySelector('.permiso-badge');
        if (badgeVacaciones) badgeVacaciones.style.display = 'none';
        if (badgePermiso) badgePermiso.style.display = 'none';
    });

    // Marcar como en vacaciones
    usuariosEnVacaciones.forEach(userId => {
        const card = document.querySelector(`.usuario-card[data-user-id="${userId}"]`);
        if (card) {
            card.classList.add('opacity-50');
            const asistioCb = card.querySelector(`input[name="asistio[]"][value="${userId}"]`);
            const faltoCb = card.querySelector(`input[name="falto[]"][value="${userId}"]`);
            if (asistioCb) asistioCb.disabled = true;
            if (faltoCb) faltoCb.disabled = true;

            // Añadir badge de vacaciones si no existe
            let badge = card.querySelector('.vacaciones-badge');
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'vacaciones-badge absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded';
                badge.textContent = 'Vacaciones';
                card.querySelector('.p-5').prepend(badge);
            }
            badge.style.display = 'block';
        }
    });

    // Marcar como en permiso
    usuariosConPermisos.forEach(userId => {
        const card = document.querySelector(`.usuario-card[data-user-id="${userId}"]`);
        if (card) {
            card.classList.add('opacity-50');
            const asistioCb = card.querySelector(`input[name="asistio[]"][value="${userId}"]`);
            const faltoCb = card.querySelector(`input[name="falto[]"][value="${userId}"]`);
            if (asistioCb) asistioCb.disabled = true;
            if (faltoCb) faltoCb.disabled = true;

            // Añadir badge de permiso si no existe
            let badge = card.querySelector('.permiso-badge');
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'permiso-badge absolute top-2 right-2 bg-purple-500 text-white text-xs px-2 py-1 rounded';
                badge.textContent = 'Permiso';
                card.querySelector('.p-5').prepend(badge);
            }
            badge.style.display = 'block';
        }
    });
}

    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const fechaActual = document.getElementById('fecha_registro').value;
        verificarVacaciones(fechaActual);
    });

    function toggleAsistenciaPanel(checkbox, userId) {
        const faltoCheckbox = document.getElementById('falto_' + userId);
        const retrasoPanel = document.getElementById('retraso_panel_' + userId);

        if (checkbox.checked) {
            faltoCheckbox.checked = false;
            retrasoPanel.classList.remove('hidden');
        } else {
            retrasoPanel.classList.add('hidden');
            document.getElementById('retraso_minutos_' + userId).value = '';
        }
    }

    function toggleFaltaPanel(checkbox, userId) {
        const asistioCheckbox = document.getElementById('asistio_' + userId);
        const tipoFaltaPanel = document.getElementById('tipo_falta_panel_' + userId);

        if (checkbox.checked) {
            asistioCheckbox.checked = false;
            tipoFaltaPanel.classList.remove('hidden');
        } else {
            tipoFaltaPanel.classList.add('hidden');
            const radios = tipoFaltaPanel.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => radio.checked = false);
        }
    }
</script>
