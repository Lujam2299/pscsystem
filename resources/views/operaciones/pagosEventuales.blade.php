<x-app-layout>
    <x-navbar></x-navbar>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">

                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Pagos de Eventuales Pendientes
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Registros de eventuales sin comprobante de pago (últimos 15 días)
                    </p>
                </div>

                @if($registros->isEmpty())
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay pagos pendientes</h3>
                        <p class="text-gray-500 dark:text-gray-400">Todos los registros tienen comprobante o están fuera del rango de 15 días.</p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Pago</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($registros as $registro)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ $registro->user?->name ?? 'N/D' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($registro->tipo_pago === 'nomina')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                                                        Nómina
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">
                                                        Efectivo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <button type="button"
                                                        onclick="mostrarDetallesRegistro({{ $registro->id }})"
                                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition duration-200 shadow-sm"
                                                        title="Ver detalles">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Detalles
                                                </button>
                                                @if($registro->tipo_pago === 'efectivo' || $registro->tipo_pago === 'eventual')
                                                    <button type="button"
                                                            onclick="abrirModalSubirPago({{ $registro->id }})"
                                                            class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition duration-200 shadow-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                        </svg>
                                                        Subir Comprobante
                                                    </button>
                                                @else
                                                    <span class="text-gray-400">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación -->
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

    <!-- SweetAlert2 -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function abrirModalSubirPago(registroId) {
            Swal.fire({
                title: 'Subir Comprobante de Pago',
                html: `
                    <input type="file" id="archivo-pago" accept=".pdf,.jpg,.jpeg,.png" class="w-full mt-2">
                    <p class="text-xs text-gray-500 mt-2">Formatos: PDF, JPG, PNG (Máx. 5MB)</p>
                `,
                showCancelButton: true,
                confirmButtonText: 'Subir',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const file = document.getElementById('archivo-pago').files[0];
                    if (!file) {
                        Swal.showValidationMessage('Seleccione un archivo');
                        return false;
                    }

                    if (file.size > 5 * 1024 * 1024) {
                        Swal.showValidationMessage('El archivo no debe exceder 5MB');
                        return false;
                    }

                    const formData = new FormData();
                    formData.append('archivo', file);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    return fetch(`/operaciones/subir-pago-eventual/${registroId}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Error al subir el archivo');
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
                    Swal.fire('¡Éxito!', result.value.message, 'success').then(() => {
                        window.location.reload();
                    });
                }
            });
        }
    </script>
    <script>
    function mostrarDetallesRegistro(registroId) {
        Swal.fire({
            title: 'Cargando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`/eventuales/${registroId}/detalles`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Error al cargar los detalles');
                }

                const r = data.data;

                // Formatear turnos
                const turnosTexto = r.turnos ? r.turnos.join(', ') : '—';

                // Información adicional según motivo
                let infoAdicional = '';
                if (r.motivo === 'Faltas de elementos' || r.motivo === 'Vacaciones de elementos') {
                    infoAdicional = `
                        <div class="mt-2">
                            <strong>Cubre a:</strong> ${r.elemento_relacionado_name || '—'}
                        </div>
                    `;
                } else if (r.motivo === 'Otro' && r.observaciones) {
                    infoAdicional = `
                        <div class="mt-2">
                            <strong>Detalle:</strong> ${r.observaciones}
                        </div>
                    `;
                }

                Swal.fire({
                    title: `Detalles del Registro`,
                    html: `
                        <div class="text-left text-sm space-y-2">
                            <div><strong>Usuario:</strong> ${r.user_name || 'N/A'}</div>
                            <div><strong>Fecha:</strong> ${r.fecha_formateada}</div>
                            <div><strong>Turnos:</strong> ${turnosTexto}</div>
                            <div><strong>Tipo de Servicio:</strong> ${r.tipo_servicio || 'N/A'}</div>
                            <div><strong>Motivo:</strong> ${r.motivo || 'N/A'}</div>
                            <div><strong>Tipo de Pago:</strong>
                                ${r.tipo_pago === 'nomina' ? 'Nómina' : 'Efectivo'}
                            </div>
                            <div><strong>Observaciones:</strong> ${r.observaciones ?? 'N/A'}</div>
                            ${infoAdicional}
                            <div class="mt-3 text-xs text-gray-500">
                                <em>Este registro aún no tiene comprobante de pago.</em>
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: 'Cerrar'
                });
            })
            .catch(error => {
                Swal.fire('Error', 'No se pudieron cargar los detalles.', 'error');
                console.error('Error:', error);
            });
    }
</script>
</x-app-layout>
