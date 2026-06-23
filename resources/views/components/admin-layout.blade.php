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


<div class="w-full">
    <nav aria-label="Acciones administrativas">
        <div>
            <div class="flex flex-wrap items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('dashboard') }}"
                        class="flex min-h-11 items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-blue-300">
                        <svg class="w-5 h-5 text-blue-500 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 9h1v7a2 2 0 002 2h2a1 1 0 001-1v-4h2v4a1 1 0 001 1h2a2 2 0 002-2V9h1a1 1 0 00.707-1.707l-7-7z" />
                        </svg>
                        Inicio
                    </a>

                    <div class="relative">
                        <button onclick="toggleNotificaciones()"
                            class="relative flex min-h-11 items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-emerald-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-500 dark:text-green-400"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 2a6 6 0 00-6 6v2c0 .768-.293 1.47-.769 2H3a1 1 0 000 2h14a1 1 0 000-2h-.231A3.001 3.001 0 0116 10V8a6 6 0 00-6-6zM7 18a3 3 0 006 0H7z" />
                            </svg>
                            Notificaciones
                            @if ($notificacionesDocumentacion->count())
                                <span
                                    class="absolute top-0 right-0 inline-flex items-center justify-center w-4 h-4 text-xs text-white bg-red-600 rounded-full">
                                    {{ $notificacionesDocumentacion->count() > 99 ? '99+' : $notificacionesDocumentacion->count() }}
                                </span>
                            @endif
                        </button>

                        <div id="notificacionesDropdown"
                            class="absolute right-0 z-50 mt-2 hidden max-h-80 w-80 max-w-[calc(100vw-2rem)] overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl scrollbar-thin scrollbar-thumb-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:scrollbar-thumb-gray-600">
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


                </div>
            </div>
        </div>
    </nav>

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
