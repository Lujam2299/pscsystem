<x-app-layout>
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
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ $user->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <button type="button"
                                                        onclick="abrirModalRegistro({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                        class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded-lg transition duration-200 shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
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

    <!-- Modal con SweetAlert2 -->
<!-- Modal con SweetAlert2 -->
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
                        <option value="efectivo">Efectivo</option>
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const userId = document.getElementById('user-id').value;
                const fecha = document.getElementById('fecha').value;
                const subpuntoId = document.getElementById('subpunto-id').value;
                const tipoPago = document.getElementById('tipo-pago').value;
                const turnos = Array.from(document.querySelectorAll('.turno-checkbox:checked')).map(cb => cb.value);

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
                        tipo_pago: tipoPago
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
            // ✅ Este bloque es el que muestra el mensaje de éxito
            if (result.isConfirmed) {
                Swal.fire('¡Éxito!', result.value.message, 'success').then(() => {
                    window.location.reload();
                });
            }
        });
    }
</script>
</x-app-layout>
