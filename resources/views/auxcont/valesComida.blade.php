<x-app-layout>
    <x-navbar />

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">

                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Vales de Comida - En Proceso
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Solicitudes de vales de comida pendientes de aprobación
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 px-3 py-1 rounded-full">
                                <span class="text-sm font-medium">{{ $registros->total() }}</span>
                                <span class="text-xs">vales</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($registros->isEmpty())
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay vales pendientes</h3>
                        <p class="text-gray-500 dark:text-gray-400">No se encontraron solicitudes de vales de comida en proceso.</p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Num. Elementos</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($registros as $registro)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ ($registros->currentPage() - 1) * $registros->perPage() + $loop->iteration }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                ${{ number_format($registro->monto, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $registro->num_elementos }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ $registro->user?->name ?? 'N/D' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $registro->estatus }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div class="flex flex-wrap gap-2">
                                                    <!-- Botón Aceptar con SweetAlert2 -->
                                                    <button type="button"
                                                            onclick="confirmarAceptacion({{ $registro->id }}, '{{ addslashes($registro->user?->name ?? 'N/D') }}')"
                                                            class="inline-flex items-center px-2 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg transition duration-200 shadow-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Aceptar
                                                    </button>

                                                    <!-- Botón Rechazar -->
                                                    <button type="button"
                                                            onclick="abrirModalRechazar({{ $registro->id }}, '{{ addslashes($registro->user?->name ?? 'N/D') }}')"
                                                            class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition duration-200 shadow-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                        Rechazar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6">
                        {{ $registros->links() }}
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
        // Función para confirmar aceptación con SweetAlert2
        function confirmarAceptacion(valeId, userName) {
            Swal.fire({
                title: `¿Aceptar vale de ${userName}?`,
                text: "¿Está seguro de que desea aceptar este vale de comida?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                confirmButtonColor: '#22c55e',
                cancelButtonColor: '#ef4444',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    formData.append('_method', 'POST');

                    return fetch(`/vales-comida/${valeId}/aceptar`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Error al aceptar el vale');
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Error: ${error.message}`);
                        return false;
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: result.value.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        }

        // Función para rechazar con modal
        function abrirModalRechazar(valeId, userName) {
            Swal.fire({
                title: `Rechazar Vale - ${userName}`,
                html: `
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones (opcional)</label>
                    <textarea id="observaciones"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                              rows="3"
                              placeholder="Escriba el motivo del rechazo..."></textarea>
                `,
                showCancelButton: true,
                confirmButtonText: 'Rechazar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33',
                preConfirm: () => {
                    const observaciones = document.getElementById('observaciones').value;

                    const formData = new FormData();
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    formData.append('_method', 'POST');
                    if (observaciones) {
                        formData.append('observaciones', observaciones);
                    }

                    return fetch(`/vales-comida/${valeId}/rechazar`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Error al rechazar el vale');
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Error: ${error.message}`);
                        return false;
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: result.value.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        }
    </script>
</x-app-layout>
