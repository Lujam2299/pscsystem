@php
    use App\Models\ToastNotificationLog;
    use Illuminate\Support\Facades\Auth;

    $usuarioActual = Auth::user();
    $notificacionesHistorial = $usuarioActual
        ? ToastNotificationLog::recentFor($usuarioActual, 15)
        : collect();
    $notificacionesPendientes = $usuarioActual
        ? ToastNotificationLog::unreadCountFor($usuarioActual)
        : 0;
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
                            @if ($notificacionesPendientes)
                                <span
                                    class="absolute top-0 right-0 inline-flex items-center justify-center w-4 h-4 text-xs text-white bg-red-600 rounded-full">
                                    {{ $notificacionesPendientes > 99 ? '99+' : $notificacionesPendientes }}
                                </span>
                            @endif
                        </button>

                        <div id="notificacionesDropdown"
                            class="absolute right-0 z-50 mt-2 hidden max-h-80 w-80 max-w-[calc(100vw-2rem)] overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl scrollbar-thin scrollbar-thumb-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:scrollbar-thumb-gray-600">
                            @forelse($notificacionesHistorial as $notificacion)
                                <a href="{{ $notificacion->url ?: '#' }}"
                                    class="block border-b px-4 py-3 text-sm text-gray-800 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                            <i class="ti ti-bell"></i>
                                        </span>
                                        <span class="min-w-0">
                                            <strong class="block truncate">{{ $notificacion->title }}</strong>
                                            @if ($notificacion->text)
                                                <span class="block text-xs text-gray-600 dark:text-gray-300">{{ $notificacion->text }}</span>
                                            @endif
                                            <span class="mt-1 block text-[11px] text-gray-400">
                                                {{ optional($notificacion->created_at)->diffForHumans() }}
                                            </span>
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">No hay notificaciones recientes.</div>
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
                            const contador = dropdown.previousElementSibling.querySelector('span');
                            if (contador) contador.remove();
                        }
                    });
            }
        }
    </script>

</div>
