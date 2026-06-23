<div wire:key="lista-conversaciones-{{ count($conversaciones) }}-{{ $conversaciones->pluck('id')->sum() }}"
     class="h-full flex flex-col bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800">

    {{-- HEADER: Título y Acciones --}}
    <div class="px-4 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-white/80 backdrop-blur-sm sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}"
                class="p-2 rounded-full text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 dark:text-gray-400 transition-colors"
                title="Volver al Dashboard">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight">Mensajes</h2>
        </div>

        <button wire:click="toggleBuscador"
            class="p-2 rounded-full bg-blue-600 text-white hover:bg-blue-700 shadow-sm hover:shadow-md transition-all duration-200"
            title="Nuevo chat / Buscar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
        </button>
    </div>

    <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
        <label class="relative block">
            <span class="sr-only">Buscar conversaciones</span>
            <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true"></i>
            <input wire:model.live.debounce.300ms="buscarConversacion" type="search" placeholder="Buscar conversaciones..."
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
        </label>
    </div>

    {{-- BUSCADOR DESPLEGABLE --}}
    @if ($mostrarBuscador)
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 transition-all duration-300"
             x-data="{ show: true }" x-show="show" x-transition>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input
                    wire:model.live="buscarUsuario"
                    x-ref="searchInput"
                    type="text"
                    placeholder="Buscar colega..."
                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-shadow"
                >
            </div>

            {{-- Resultados de Búsqueda --}}
            <div class="mt-2 max-h-48 overflow-y-auto custom-scrollbar">
                @if (count($usuariosFiltrados) > 0)
                    <div class="space-y-1">
                        @foreach ($usuariosFiltrados as $usuario)
                            @php
                                $foto = $usuario->documentacionAltas?->arch_foto;
                                $foto_url = $foto ? asset($foto) : asset('images/default-user.jpg');
                            @endphp
                            <div wire:click="iniciarConversacion({{ $usuario->id }})"
                                class="flex items-center gap-3 cursor-pointer px-3 py-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg group transition-colors">
                                <img src="{{ $foto_url }}" alt="{{ $usuario->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $usuario->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @elseif(strlen($buscarUsuario) >= 2)
                    <div class="px-3 py-2 text-xs text-center text-gray-500 dark:text-gray-400">No se encontraron usuarios</div>
                @endif
            </div>
        </div>
    @endif

    {{-- LISTA DE CONVERSACIONES --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        @forelse ($conversaciones as $conv)
            @php
                $otro = $conv->users->where('id', '!=', auth()->id())->first();
                $foto = $otro?->documentacionAltas?->arch_foto;
                $foto_url = $foto ? asset($foto) : asset('images/default-user.jpg');

                // Lógica de tiempo
                $timeLabel = '';
                if($conv->latestMessage) {
                    $date = \Carbon\Carbon::parse($conv->latestMessage->created_at);
                    $now = \Carbon\Carbon::now();
                    if ($date->isToday()) $timeLabel = $date->format('H:i');
                    elseif ($date->isYesterday()) $timeLabel = 'Ayer';
                    else $timeLabel = $date->format('d/m');
                }

                // Detectar si es reciente para animación
                $isRecent = $conv->latestMessage && \Carbon\Carbon::parse($conv->latestMessage->created_at)->diffInSeconds(now()) <= 10;

                // Pivot para no leídos
                $currentUserPivot = $conv->users->firstWhere('id', auth()->id())?->pivot;
                $unreadCount = $currentUserPivot?->unread_count ?? 0;
                $isActive = isset($selectedConversationId) && $selectedConversationId == $conv->id;
            @endphp

            <div x-data="{ animate: false, showMenu: false }"
                 x-init="if (@json($isRecent)) { setTimeout(() => animate = true, 10); } else { animate = true; }"
                 x-show="animate"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="-translate-x-4 opacity-0"
                 x-transition:enter-end="translate-x-0 opacity-100"
                 class="relative group"
                 id="conversation-{{ $conv->id }}">

                <div @mousedown.right.prevent="(event) => { if(event.target.closest('.conv-item') === event.currentTarget) showMenu = true; }"
                     @click.away="showMenu = false"
                     class="conv-item relative flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all duration-200
                            {{ $isActive ? 'bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-100 dark:ring-blue-800' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">

                    <div wire:click="seleccionarConversacion({{ $conv->id }})" class="flex w-full items-center gap-3">
                        {{-- Avatar --}}
                        <div class="relative flex-shrink-0">
                            <img src="{{ $foto_url }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover border border-gray-100 dark:border-gray-700 shadow-sm">
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-500 border-2 border-white dark:border-gray-900"></span>
                                </span>
                            @endif
                        </div>

                        {{-- Contenido --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate pr-2">
                                    {{ $conv->is_group ? $conv->title ?? 'Grupo sin nombre' : $otro?->name }}
                                </h3>
                                <span class="text-[10px] font-medium {{ $unreadCount > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400' }}">
                                    {{ $timeLabel }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[160px] sm:max-w-[200px]">
                                    @if($conv->latestMessage)
                                        @if($conv->latestMessage->user_id == auth()->id())
                                            <span class="inline-block mr-1">Tú:</span>
                                        @endif
                                        {{ Str::limit($conv->latestMessage->body, 30) }}
                                    @else
                                        <span class="italic text-gray-400">Inicia la conversación...</span>
                                    @endif
                                </p>

                                @if($unreadCount > 0)
                                    <span class="flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 ml-2 text-[10px] font-bold text-white bg-blue-600 rounded-full shadow-sm">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(!$conv->latestMessage)
                    <button type="button" @click.stop="showMenu = !showMenu"
                            class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 opacity-100 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:hover:bg-gray-700 dark:hover:text-gray-200 lg:opacity-0 lg:group-hover:opacity-100"
                            aria-label="Opciones de conversación">
                        <i class="ti ti-dots-vertical" aria-hidden="true"></i>
                    </button>
                    @endif

                    {{-- Menú Contextual (Click Derecho) --}}
                    @if(!$conv->latestMessage)
                    <div x-show="showMenu"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         class="absolute right-2 top-12 z-20 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <button wire:click="confirmarEliminacion({{ $conv->id }})"
                            class="flex items-center gap-2 w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Eliminar conversación
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-64 text-center px-4">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">No hay conversaciones</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Inicia un nuevo chat usando el botón +</p>
            </div>
        @endforelse
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
                confirmButtonColor: '#ef4444', // Tailwind red-500
                cancelButtonColor: '#6b7280', // Tailwind gray-500
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                background: '#fff',
                borderRadius: '0.75rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarConversacion', { id });
                }
            });
        });

        Livewire.on('conversacionEliminada', () => {
            Swal.fire({
                icon: 'success',
                title: 'Conversación eliminada',
                showConfirmButton: false,
                timer: 1500,
                toast: true,
                position: 'top-end'
            });
        });

        if (window.Echo && window.userId) {
            window.Echo.private(`App.Models.User.${window.userId}`)
                .listen('.ConversationUpdated', () => Livewire.dispatch('forzarRender'));
        }
    });
</script>
@endpush
