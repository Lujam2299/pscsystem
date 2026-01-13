<div wire:key="lista-conversaciones-{{ count($conversaciones) }}-{{ $conversaciones->pluck('id')->sum() }}"
     class="h-full overflow-y-auto bg-blue-50 p-3 rounded-lg border border-blue-100"
     wire:poll.{{ $enablePolling ? '5s' : '0s' }}="cargarConversaciones">
    <div class="flex items-center justify-between mb-4 px-2">
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}"
                class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-200 text-blue-600 transition"
                title="Volver al Dashboard">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
        </div>
        <h2 class="text-lg font-semibold text-slate-800">Chats</h2>
        <button wire:click="toggleBuscador"
            class="w-9 h-9 rounded-full flex items-center justify-center bg-blue-600 text-white hover:bg-blue-700 transition"
            title="Nuevo chat">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
        </button>
    </div>

    @if ($mostrarBuscador)
        <div class="px-2 mb-3">
            <input
                wire:model.live="buscarUsuario"
                x-ref="searchInput"
                type="text"
                placeholder="Buscar usuario..."
                class="w-full px-4 py-2 rounded-full border border-blue-200 bg-white text-sm text-slate-800 focus:outline-none focus:ring focus:ring-blue-300"
            >
        </div>

        <div wire:transition>
            @if (count($usuariosFiltrados) > 0)
                <div class="bg-white rounded-lg shadow p-2 mb-4 mx-2 border border-blue-100">
                    @foreach ($usuariosFiltrados as $usuario)
                        @php
                            $foto = $usuario->documentacionAltas?->arch_foto;
                            $foto_url = $foto ? asset($foto) : asset('images/default-user.jpg');
                        @endphp
                        <div wire:click="iniciarConversacion({{ $usuario->id }})"
                            class="flex items-center gap-3 cursor-pointer px-3 py-2 hover:bg-blue-100 rounded text-slate-800">
                            <img src="{{ $foto_url }}" alt="Foto" class="w-8 h-8 rounded-full object-cover">
                            <span>{{ $usuario->name }}</span>
                        </div>
                    @endforeach
                </div>
            @elseif(strlen($buscarUsuario) >= 2)
                <div class="px-4 py-2 text-slate-500">No se encontraron resultados</div>
            @endif
        </div>
    @endif

    <div class="space-y-1">
        @foreach ($conversaciones as $conv)
            @php
                $otro = $conv->users->where('id', '!=', auth()->id())->first();
                $foto = $otro?->documentacionAltas?->arch_foto;
                $foto_url = $foto ? asset($foto) : asset('images/default-user.jpg');

                // Detectar si esta conversación fue actualizada recientemente
                $isRecent = $conv->latestMessage && \Carbon\Carbon::parse($conv->latestMessage->created_at)->diffInSeconds(now()) <= 10;
            @endphp

            <div x-data="{ animate: false }"
                 x-init="
                     if (@json($isRecent)) {
                         setTimeout(() => animate = true, 10);
                     } else {
                         animate = true; // Mostrar siempre si no es reciente
                     }
                 "
                 x-show="animate"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="-translate-x-4 opacity-0"
                 x-transition:enter-end="translate-x-0 opacity-100"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0 opacity-100"
                 x-transition:leave-end="translate-x-4 opacity-0"
                 class="relative group"
                 id="conversation-{{ $conv->id }}">

                <div x-data="{ showMenu: false }"
                     @mousedown.right.prevent="(event) => {
                         if (event.target.closest('.group') === event.currentTarget) {
                             showMenu = true;
                         }
                     }"
                     @click.away="showMenu = false"
                     class="relative group">

                    <div wire:click="seleccionarConversacion({{ $conv->id }})"
                        class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-blue-100 transition">
                        <img src="{{ $foto_url }}" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                        <div class="flex-1 min-w-0">
<strong class="block text-slate-800 truncate">
    {{ $conv->is_group ? $conv->title ?? 'Grupo sin nombre' : $otro?->name }}

    @php
        // Buscar el pivot del usuario actual en esta conversación
        $currentUserPivot = $conv->users->firstWhere('id', auth()->id())?->pivot;
    @endphp

    @if($currentUserPivot && $currentUserPivot->unread_count > 0)
        <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 ml-1 inline-block">
            {{ $currentUserPivot->unread_count }}
        </span>
    @endif
</strong>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-500 truncate max-w-[180px]">
                                    {{ $conv->latestMessage?->body ?? 'Sin mensajes' }}
                                </span>
                                <span class="text-xs text-slate-400 whitespace-nowrap ml-2">
                                    @if($conv->latestMessage)
                                        @php
                                            $date = \Carbon\Carbon::parse($conv->latestMessage->created_at);
                                            $now = \Carbon\Carbon::now();

                                            if ($date->isToday()) {
                                                echo $date->format('H:i');
                                            } elseif ($date->isYesterday()) {
                                                echo 'Ayer';
                                            } else {
                                                echo $date->format('d/m');
                                            }
                                        @endphp
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div x-show="showMenu"
                        x-transition
                        class="absolute top-2 right-2 z-50 bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-md rounded-md w-48">
                        <button wire:click="confirmarEliminacion({{ $conv->id }})"
                            class="flex items-center gap-2 w-full px-4 py-2 text-left text-red-600 hover:bg-red-50 dark:hover:bg-red-900 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Eliminar conversación
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:init', () => {

        Livewire.on('resultadosActualizados', () => {
            Livewire.dispatch('render');
        });

        Livewire.on('focusSearchInput', () => {
            setTimeout(() => {
                document.querySelector('[x-ref="searchInput"]')?.focus();
            }, 100);
        });

        Livewire.on('cerrarMenuContextual', () => {
            document.querySelectorAll('.context-menu').forEach(el => el.classList.add('hidden'));
        });

        Livewire.on('confirmarEliminacionJS', ({ id }) => {
            Swal.fire({
                title: '¿Eliminar conversación?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarConversacion', { id });
                }
            });
        });

        Livewire.on('conversacionEliminada', () => {
            Swal.fire({
                icon: 'success',
                title: '¡Conversación eliminada!',
                showConfirmButton: false,
                timer: 1500
            });
        });

    });
</script>
@endpush
