@php
use App\Models\Alerta;
use App\Models\Punto;
use App\Models\Subpunto;
use App\Models\User;

$user = auth()->user();
$notificaciones = collect();

if ($user->rol === 'Supervisor') {
$puntoUsuarioRaw = $user->punto;
$subpuntosZona = collect();

$punto = Punto::where('nombre', $puntoUsuarioRaw)->first();

if (!$punto) {
$subpunto = Subpunto::where('nombre', $puntoUsuarioRaw)->first()
?? Subpunto::where('codigo', $puntoUsuarioRaw)->first();

if ($subpunto && $subpunto->zona) {
$subpuntosZona = Subpunto::where('zona', $subpunto->zona)->pluck('nombre');
}
}

$idsSupervisados = User::where('empresa', $user->empresa)
->where('estatus', 'Activo')
->where(function ($query) use ($user, $subpuntosZona) {
$query->where('punto', $user->punto);
if ($subpuntosZona->isNotEmpty()) {
$query->orWhereIn('punto', $subpuntosZona);
}
})
->pluck('id');

$notificaciones = Alerta::where('leida', false)
->whereIn('user_id', $idsSupervisados)
->latest()
->get();
} else {
$notificaciones = Alerta::where('leida', false)->latest()->get();
}
@endphp

<div class="bg-gray-100 rounded-lg dark:bg-gray-900 h-16 w-full">
    <nav class="bg-white rounded-t-lg rounded-b-lg shadow-md dark:bg-gray-800">
        <div class="container px-4 py-3 mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between">
                <div class="flex flex-wrap items-center gap-4">

                    <a href="{{ route('dashboard') }}">
                        <button
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-800 transition-all duration-200 bg-gray-100 rounded-md sm:text-base dark:text-gray-200 dark:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-200 dark:hover:bg-gray-600">
                            <svg class="w-5 h-5 text-blue-500 dark:text-blue-300" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 9h1v7a2 2 0 002 2h2a1 1 0 001-1v-4h2v4a1 1 0 001 1h2a2 2 0 002-2V9h1a1 1 0 00.707-1.707l-7-7z" />
                            </svg>
                            Inicio
                        </button>
                    </a>
                    @php
                    $rolesPermitidos = [
                    'admin', 'SUPERVISOR', 'Supervisor',
                    'AUXILIAR RECURSOS HUMANOS', 'Auxiliar recursos humanos',
                    'AUXILIAR RH', 'AUX RH', 'Auxiliar Administrativo', 'Auxiliar RH', 'Auxiliar Recursos Humanos', 'Aux RH'
                    ];
                    $departamentosPermitidos = ['Recursos Humanos'];
                    $user = Auth::user();
                    @endphp

                    @if(
                    in_array($user->rol, $rolesPermitidos) ||
                    (optional($user->solicitudAlta)->departamento && in_array($user->solicitudAlta->departamento,
                    $departamentosPermitidos)) ||
                    (optional($user->solicitudAlta)->rol && in_array($user->solicitudAlta->rol, $rolesPermitidos))
                    )
                    <div class="relative">
                        <button onclick="toggleNotificaciones()"
                            class="relative flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-800 transition-all duration-200 bg-gray-100 rounded-md sm:text-base dark:text-gray-200 dark:bg-gray-700 hover:text-green-600 dark:hover:text-green-400 hover:bg-gray-200 dark:hover:bg-gray-600">
                            <svg class="w-6 h-6 text-green-500 dark:text-green-400" fill="currentColor"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 20 20">
                                <path
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C8.67 6.165 8 7.388 8 9v5.159c0 .538-.214 1.055-.595 1.436L6 17h5m4 0v1a3 3 0 01-6 0v-1m6 0H9" />
                            </svg>
                            Notificaciones
                            @if($notificaciones->count())
                            <span
                                class="absolute top-0 right-0 inline-flex items-center justify-center w-4 h-4 text-xs text-white bg-red-600 rounded-full">
                                {{ $notificaciones->count() }}
                            </span>
                            @endif
                        </button>

                        <div id="notificacionesDropdown"
                            class="absolute right-0 z-50 hidden mt-2 overflow-y-auto bg-white border rounded shadow-lg w-80 dark:bg-gray-700 max-h-80 scrollbar-thin scrollbar-thumb-gray-400 dark:scrollbar-thumb-gray-600">
                            @forelse($notificaciones as $alerta)
                            <div
                                class="px-4 py-2 text-sm text-gray-800 border-b dark:text-gray-100 dark:border-gray-600">
                                <strong>{{ $alerta->titulo }}</strong><br>
                                <span>{{ $alerta->mensaje }}</span>
                            </div>
                            @empty
                            <div class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">No hay notificaciones
                                nuevas.</div>
                            @endforelse
                        </div>
                    </div>
                    @endif
                    <a href="{{ route('profile.edit') }}">
                        <button
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-800 transition-all duration-200 bg-gray-100 rounded-md sm:text-base dark:text-gray-200 dark:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-200 dark:hover:bg-gray-600">
                            <svg class="w-5 h-5 text-purple-500 dark:text-purple-300" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 14.25V16a2 2 0 01-2 2H4a2 2 0 01-2-2v-1.75A6.25 6.25 0 018.25 8h3.5A6.25 6.25 0 0118 14.25zM10 7A3 3 0 1010 1a3 3 0 000 6z"
                                    clip-rule="evenodd" />
                            </svg>
                            Mi Perfil
                        </button>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 px-4 py-2 mt-auto text-sm font-medium text-gray-800 transition-all duration-200 bg-gray-100 rounded-md sm:text-base dark:text-gray-200 dark:bg-gray-700 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-200 dark:hover:bg-gray-600">
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
