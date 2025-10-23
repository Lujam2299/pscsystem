<x-app-layout>
    <style>
        #sugerencias-elemento {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        #sugerencias-elemento div:hover {
            background-color: #f3f4f6;
        }
    </style>
    <x-navbar></x-navbar>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">

                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Eventuales Activos
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Registre turnos para empleados eventuales
                    </p>
                </div>

                @if($users->isEmpty())
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay eventuales</h3>
                        <p class="text-gray-500 dark:text-gray-400">No se encontraron empleados eventuales activos.</p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($users as $user)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        @php
                                                            $foto = null;
                                                            if ($user->documentacionAltas?->arch_foto) {
                                                                $foto = asset($user->documentacionAltas->arch_foto);
                                                            }
                                                        @endphp

                                                        @if($foto)
                                                            <img src="{{ $foto }}"
                                                                alt="Foto de {{ $user->name ?? 'usuario' }}"
                                                                class="h-8 w-8 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                                                        @else
                                                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                                                <span class="text-white font-medium text-xs">
                                                                    {{ substr(trim($user->name ?? 'ND'), 0, 2) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ $user->name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <button type="button"
                                                        onclick="abrirModalRegistro({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                        class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition duration-200 shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                    Registrar Turno
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-6">
                        {{ $users->links() }}
                    </div>
                @endif

                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-center">
                        <a href="{{ route('dashboard') }}"
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

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function abrirModalRegistro(userId, userName) {
        Swal.fire({
            title: `Registrar Turno - ${userName}`,
            html: `
                <input type="hidden" id="user-id" value="${userId}">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                    <input type="date" id="fecha" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subpunto (Ubicación)</label>
                    <select id="subpunto-id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Seleccione una ubicación</option>
                        @foreach(\App\Models\Punto::with('subpuntos')->get() as $punto)
                            @if($punto->subpuntos->isNotEmpty())
                                <optgroup label="{{ $punto->nombre }}">
                                    @foreach($punto->subpuntos as $subpunto)
                                        <option value="{{ $subpunto->id }}">{{ $subpunto->nombre }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Turno(s)</label>
                    <div class="flex flex-wrap gap-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="dia" class="turno-checkbox rounded text-purple-600">
                            <span class="ml-2 text-sm">Día</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="tarde" class="turno-checkbox rounded text-purple-600">
                            <span class="ml-2 text-sm">Tarde</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="noche" class="turno-checkbox rounded text-purple-600">
                            <span class="ml-2 text-sm">Noche</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pago</label>
                    <select id="tipo-pago" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Seleccione</option>
                        <option value="nomina">Nómina</option>
                        <option value="eventual">Eventual</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Servicio</label>
                    <select id="tipo-servicio" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Seleccione una opción</option>
                        <option value="12 Horas">12 Horas</option>
                        <option value="24 horas">24 horas</option>
                        <option value="36 Horas">36 Horas</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <select id="motivo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Seleccione una opción</option>
                        <option value="Falta de plantilla">Falta de plantilla</option>
                        <option value="Faltas de elementos">Faltas de elementos</option>
                        <option value="Vacaciones de elementos">Vacaciones de elementos</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <!-- Contenedor dinámico para el input con autocompletado -->
                <div id="contenedor-elemento-relacionado" class="mb-4" style="display:none; position:relative;">
                    <label class="block text-sm font-medium text-gray-700 mb-1" id="label-elemento-relacionado">
                        Elemento relacionado
                    </label>
                    <input type="text"
                           id="elemento-relacionado-input"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                           placeholder="Escriba el nombre del elemento...">
                    <input type="hidden" id="elemento-relacionado-id" value="">
                    <input type="hidden" id="tipo-elemento" value="">

                    <!-- Lista de sugerencias -->
                    <div id="sugerencias-elemento"
                         class="absolute z-10 w-full bg-white border border-gray-300 rounded-b-lg shadow-lg max-h-40 overflow-y-auto"
                         style="display:none; top:100%; left:0;">
                    </div>
                </div>

                <!-- Contenedor para observaciones (motivo = Otro) -->
                <div id="contenedor-observaciones" class="mb-4" style="display:none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Detalle del motivo</label>
                    <textarea id="observaciones"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                              rows="3"
                              placeholder="Escriba el motivo..."></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                const motivoSelect = document.getElementById('motivo');
                const contenedor = document.getElementById('contenedor-elemento-relacionado');
                const label = document.getElementById('label-elemento-relacionado');
                const inputElemento = document.getElementById('elemento-relacionado-input');
                const hiddenId = document.getElementById('elemento-relacionado-id');
                const tipoElementoInput = document.getElementById('tipo-elemento');
                const sugerenciasDiv = document.getElementById('sugerencias-elemento');
                const contenedorObservaciones = document.getElementById('contenedor-observaciones');
                const textareaObservaciones = document.getElementById('observaciones');

                // Cerrar sugerencias al hacer clic fuera
                document.addEventListener('click', (e) => {
                    if (!contenedor.contains(e.target)) {
                        sugerenciasDiv.style.display = 'none';
                    }
                });

                // Función para mostrar sugerencias según el tipo
                function mostrarSugerencias(termino, tipo) {
                    if (!termino.trim()) {
                        sugerenciasDiv.style.display = 'none';
                        return;
                    }

                    let listaUsuarios = [];
                    if (tipo === 'falta') {
                        listaUsuarios = usuariosEventualesActivos;
                    } else if (tipo === 'vacaciones') {
                        listaUsuarios = todosUsuariosActivos;
                    }

                    const coincidencias = listaUsuarios.filter(usuario =>
                        usuario.name.toLowerCase().includes(termino.toLowerCase())
                    );

                    if (coincidencias.length === 0) {
                        sugerenciasDiv.style.display = 'none';
                        return;
                    }

                    sugerenciasDiv.innerHTML = coincidencias.map(usuario =>
                        `<div class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-0"
                             data-id="${usuario.id}"
                             data-name="${usuario.name}">
                            ${usuario.name}
                        </div>`
                    ).join('');

                    sugerenciasDiv.style.display = 'block';

                    // Añadir evento a cada sugerencia
                    sugerenciasDiv.querySelectorAll('div').forEach(item => {
                        item.addEventListener('click', () => {
                            inputElemento.value = item.getAttribute('data-name');
                            hiddenId.value = item.getAttribute('data-id');
                            sugerenciasDiv.style.display = 'none';
                        });
                    });
                }

                // Escuchar cambios en el input de autocompletado
                inputElemento.addEventListener('input', function() {
                    const tipo = tipoElementoInput.value;
                    if (tipo) {
                        mostrarSugerencias(this.value, tipo);
                    }
                });

                // Escuchar cambios en el motivo
                motivoSelect.addEventListener('change', function() {
                    const valor = this.value;

                    // Ocultar todo primero
                    contenedor.style.display = 'none';
                    contenedorObservaciones.style.display = 'none';
                    inputElemento.value = '';
                    hiddenId.value = '';
                    tipoElementoInput.value = '';
                    textareaObservaciones.value = '';
                    sugerenciasDiv.style.display = 'none';

                    if (valor === 'Faltas de elementos') {
                        label.textContent = 'Elemento que faltó';
                        inputElemento.placeholder = 'Escriba el nombre del elemento que faltó...';
                        tipoElementoInput.value = 'falta';
                        contenedor.style.display = 'block';
                    } else if (valor === 'Vacaciones de elementos') {
                        label.textContent = 'Elemento en vacaciones';
                        inputElemento.placeholder = 'Escriba el nombre del elemento en vacaciones...';
                        tipoElementoInput.value = 'vacaciones';
                        contenedor.style.display = 'block';
                    } else if (valor === 'Otro') {
                        contenedorObservaciones.style.display = 'block';
                    }
                    // Para "Falta de plantilla", no se muestra nada adicional
                });
            },
            preConfirm: () => {
                const userId = document.getElementById('user-id').value;
                const fecha = document.getElementById('fecha').value;
                const subpuntoId = document.getElementById('subpunto-id').value;
                const tipoPago = document.getElementById('tipo-pago').value;
                const turnos = Array.from(document.querySelectorAll('.turno-checkbox:checked')).map(cb => cb.value);
                const tipoServicio = document.getElementById('tipo-servicio').value;
                const motivo = document.getElementById('motivo').value;
                const elementoRelacionadoId = document.getElementById('elemento-relacionado-id')?.value || null;
                const observaciones = document.getElementById('observaciones')?.value.trim() || null;

                if (!fecha) {
                    Swal.showValidationMessage('La fecha es requerida');
                    return false;
                }
                if (!subpuntoId) {
                    Swal.showValidationMessage('Seleccione un subpunto');
                    return false;
                }
                if (turnos.length === 0) {
                    Swal.showValidationMessage('Seleccione al menos un turno');
                    return false;
                }
                if (!tipoPago) {
                    Swal.showValidationMessage('Seleccione el tipo de pago');
                    return false;
                }
                if (!tipoServicio) {
                    Swal.showValidationMessage('Seleccione el tipo de servicio');
                    return false;
                }
                if (!motivo) {
                    Swal.showValidationMessage('Seleccione el motivo');
                    return false;
                }

                // Validar elemento relacionado si aplica
                if ((motivo === 'Faltas de elementos' || motivo === 'Vacaciones de elementos') && !elementoRelacionadoId) {
                    Swal.showValidationMessage('Seleccione el elemento relacionado');
                    return false;
                }

                // Validar observaciones si es "Otro"
                if (motivo === 'Otro' && (!observaciones || observaciones.length < 3)) {
                    Swal.showValidationMessage('El detalle del motivo es requerido (mínimo 3 caracteres)');
                    return false;
                }

                return fetch("{{ route('operaciones.registrar.eventual') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        fecha: fecha,
                        subpunto_id: subpuntoId,
                        turnos: turnos,
                        tipo_pago: tipoPago,
                        tipo_servicio: tipoServicio,
                        motivo: motivo,
                        elemento_relacionado_id: elementoRelacionadoId,
                        observaciones: observaciones
                    })
                })
                .then(response => response.json().then(data => {
                    if (!response.ok) {
                        throw new Error(data.message || 'Error al guardar el registro');
                    }
                    return data;
                }))
                .catch(error => {
                    Swal.showValidationMessage('Error al guardar el registro');
                    return false;
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('¡Éxito!', result.value.message, 'success').then(() => {
                    window.location.reload();
                });
            }
        });
    }
</script>
@php
    $usuariosEventualesActivos = \App\Models\User::where('estatus', 'Activo')
        ->select('id', 'name')
        ->get()
        ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
        ->values();

    $todosUsuariosActivos = \App\Models\User::where('estatus', 'Activo')
        ->select('id', 'name')
        ->get()
        ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
        ->values();
@endphp

<script>
    const usuariosEventualesActivos = @json($usuariosEventualesActivos);
    const todosUsuariosActivos = @json($todosUsuariosActivos);
</script>
</x-app-layout>
