@php
    use App\Models\User;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;

    $usuarios = User::with('documentacionAltas')
        ->where('estatus', 'Activo')
        ->whereDate('fecha_ingreso', '<', Carbon::now()->subMonth())
        ->where('empresa', '!=', 'Montana')
        ->where('rol', '!=', 'admin')
        ->get();

    $notificacionesDocumentacion = collect();

    foreach ($usuarios as $usuario) {
        $solicitud = $usuario->solicitudAlta;
        $documentacion = $usuario->documentacionAltas;

        if (!$documentacion) {
            continue;
        }

        $tipo = strtolower($solicitud->tipo_empleado ?? '');

        if ($tipo === 'armado') {
            $camposObligatorios = [
                'arch_solicitud_empleo',
                'arch_ine',
                'arch_nss',
                'arch_curp',
                'arch_rfc',
                'arch_acta_nacimiento',
                'arch_comprobante_estudios',
                'arch_comprobante_domicilio',
                'arch_carta_rec_laboral',
                'arch_carta_rec_personal',
                'arch_cartilla_militar',
                'arch_antidoping',
                'arch_carta_no_penales',
                'arch_contrato',
                'arch_foto',
            ];
        } else {
            $camposObligatorios = [
                'arch_solicitud_empleo',
                'arch_ine',
                'arch_nss',
                'arch_curp',
                'arch_rfc',
                'arch_acta_nacimiento',
                'arch_comprobante_estudios',
                'arch_comprobante_domicilio',
                'arch_carta_rec_laboral',
                'arch_carta_rec_personal',
                'arch_contrato',
                'arch_foto',
            ];
        }

        $entregados = 0;
        foreach ($camposObligatorios as $campo) {
            if (!empty($documentacion->$campo)) {
                $entregados++;
            }
        }

        $porcentaje = ($entregados / count($camposObligatorios)) * 100;

        if ($porcentaje < 50) {
            $notificacionesDocumentacion->push([
                'nombre' => $usuario->name,
                'punto' => $usuario->punto,
                'porcentaje' => round($porcentaje, 1),
            ]);
        }
    }
@endphp


<div class="rounded-lg bg-gray-100 dark:bg-gray-900 h-16 w-full">
    <nav class="bg-white dark:bg-gray-800 shadow-md rounded-t-lg rounded-b-lg">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex flex-wrap items-center justify-between">
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-2 px-3 py-2 text-sm sm:text-base font-medium text-gray-800 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-all duration-200">
                        <svg class="w-5 h-5 text-blue-500 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 9h1v7a2 2 0 002 2h2a1 1 0 001-1v-4h2v4a1 1 0 001 1h2a2 2 0 002-2V9h1a1 1 0 00.707-1.707l-7-7z" />
                        </svg>
                        Inicio
                    </a>

                    <div class="relative">
                        <button onclick="toggleNotificaciones()"
                            class="flex items-center gap-2 px-4 py-2 text-sm sm:text-base font-medium text-gray-800 dark:text-gray-200 dark:bg-gray-700 hover:text-green-600 dark:hover:text-green-400 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-all duration-200 relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-500 dark:text-green-400"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 2a6 6 0 00-6 6v2c0 .768-.293 1.47-.769 2H3a1 1 0 000 2h14a1 1 0 000-2h-.231A3.001 3.001 0 0116 10V8a6 6 0 00-6-6zM7 18a3 3 0 006 0H7z" />
                            </svg>
                            Notificaciones
                            @if ($notificacionesDocumentacion->count())
                                <span
                                    class="absolute top-0 right-0 inline-flex items-center justify-center w-4 h-4 text-xs text-white bg-red-600 rounded-full">
                                    {{ $notificacionesDocumentacion->count() }}
                                </span>
                            @endif
                        </button>

                        <div id="notificacionesDropdown"
                            class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-700 border rounded shadow-lg z-50 max-h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 dark:scrollbar-thumb-gray-600">
                            @forelse($notificacionesDocumentacion as $alerta)
                                <div
                                    class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100 border-b dark:border-gray-600">
                                    <strong>{{ $alerta['nombre'] }}</strong> — <span
                                        class="text-xs text-gray-500">{{ $alerta['punto'] }}</span><br>
                                    <span>Documentación incompleta ({{ $alerta['porcentaje'] }}%)</span>
                                </div>
                            @empty
                                <div class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">No hay usuarios con
                                    documentación incompleta.</div>
                            @endforelse
                        </div>
                    </div>


                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-2 px-3 py-2 text-sm sm:text-base font-medium text-gray-800 dark:text-gray-200 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-all duration-200">
                        <svg class="w-5 h-5 text-purple-500 dark:text-purple-300" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 14.25V16a2 2 0 01-2 2H4a2 2 0 01-2-2v-1.75A6.25 6.25 0 018.25 8h3.5A6.25 6.25 0 0118 14.25zM10 7A3 3 0 1010 1a3 3 0 000 6z"
                                clip-rule="evenodd" />
                        </svg>
                        Mi Perfil
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 px-3 py-2 text-sm sm:text-base font-medium text-gray-800 dark:text-gray-200 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-all duration-200">
                            <svg class="w-5 h-5 text-red-500 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 4a1 1 0 011-1h6a1 1 0 110 2H5v10h5a1 1 0 110 2H4a1 1 0 01-1-1V4zm11.293 1.293a1 1 0 011.414 1.414L14.414 9H17a1 1 0 110 2h-2.586l1.293 1.293a1 1 0 01-1.414 1.414L11 10l3.293-3.293z"
                                    clip-rule="evenodd" />
                            </svg>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="p-4">
        {{ $slot }}
    </div>
    <script>
        function toggleNotificaciones() {
            const dropdown = document.getElementById('notificacionesDropdown');
            dropdown.classList.toggle('hidden');
            if (!dropdown.classList.contains('hidden')) {
                fetch("{{ route('notificaciones.leer') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({})
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.ok) {
                            const contador = document.querySelector('[id^="notificacionesDropdown"]')
                                .previousElementSibling.querySelector('span');
                            if (contador) contador.remove();
                        }
                    });
            }
        }
    </script>

</div>
