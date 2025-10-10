<x-app-layout>
    <x-navbar />

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">

                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Comprobación de Vales - En Revisión
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Vales con comprobantes subidos pendientes de verificación
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-3 py-1 rounded-full">
                                <span class="text-sm font-medium">{{ $vales->total() }}</span>
                                <span class="text-xs">vales</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($vales->isEmpty())
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay vales en revisión</h3>
                        <p class="text-gray-500 dark:text-gray-400">No se encontraron vales con comprobantes pendientes de verificación.</p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Total</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Elementos</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Comprobado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($vales as $vale)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ ($vales->currentPage() - 1) * $vales->perPage() + $loop->iteration }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ \Carbon\Carbon::parse($vale->fecha)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                ${{ number_format($vale->monto, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $vale->num_elementos }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ $vale->user?->name ?? 'N/D' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                ${{ number_format($vale->comprobantes->sum('monto'), 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <button type="button"
                                                        onclick="abrirModalComprobantes({{ $vale->id }}, '{{ addslashes($vale->user?->name ?? 'N/D') }}', {{ $vale->monto }}, {{ $vale->comprobantes->sum('monto') }})"
                                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-lg transition duration-200 shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Ver Comprobantes
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
                        {{ $vales->links() }}
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

    <!-- SweetAlert2 para modal de comprobantes -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function abrirModalComprobantes(valeId, userName, montoTotal, montoComprobado) {
    const comprobantes = @json(collect($vales->items())->keyBy('id'));
    const vale = comprobantes[valeId];

    let comprobantesHtml = '';
    if (vale && vale.comprobantes) {
        vale.comprobantes.forEach(comprobante => {
            comprobantesHtml += `
                <tr>
                    <td>
                        <a href="${comprobante.archivo}" target="_blank" class="text-blue-600 hover:underline">
                            Ver Archivo
                        </a>
                    </td>
                    <td>$${parseFloat(comprobante.monto).toFixed(2)}</td>
                </tr>
            `;
        });
    }

    Swal.fire({
        title: `Comprobantes - ${userName}`,
        html: `
            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p><strong>Monto total del vale:</strong> $${montoTotal.toFixed(2)}</p>
                <p><strong>Total comprobado:</strong> $${montoComprobado.toFixed(2)}</p>
                <p class="text-sm ${Math.abs(montoTotal - montoComprobado) < 0.01 ? 'text-green-600' : 'text-red-600'}">
                    ${Math.abs(montoTotal - montoComprobado) < 0.01 ? '✓ Montos coinciden' : '⚠️ Montos no coinciden'}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Archivo</th>
                            <th class="text-left py-2">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${comprobantesHtml}
                    </tbody>
                </table>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Aprobar Comprobación',
        cancelButtonText: 'Rechazar Comprobación',
        reverseButtons: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#ef4444',
        preConfirm: () => {
            // Aprobar
            return fetch(`/vales-comida/${valeId}/aprobar-comprobacion`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error al aprobar');
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Error: ${error.message}`);
                return false;
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Aprobado
            Swal.fire({
                title: '¡Éxito!',
                text: result.value.message,
                icon: 'success'
            }).then(() => {
                window.location.reload();
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Rechazar - abrir modal de motivo
            Swal.fire({
                title: 'Motivo del rechazo',
                text: `¿Por qué rechaza la comprobación de ${userName}?`,
                input: 'textarea',
                inputPlaceholder: 'Escriba el motivo del rechazo...',
                inputAttributes: {
                    'rows': '3'
                },
                showCancelButton: true,
                confirmButtonText: 'Rechazar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ef4444',
                preConfirm: (motivo) => {
                    if (!motivo) {
                        Swal.showValidationMessage('El motivo es requerido');
                        return false;
                    }

                    return fetch(`/vales-comida/${valeId}/rechazar-comprobacion`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ motivo: motivo })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) throw new Error(data.message || 'Error al rechazar');
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
    });
}
    </script>
</x-app-layout>
