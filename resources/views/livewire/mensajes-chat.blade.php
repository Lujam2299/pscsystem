<div class="flex flex-col h-full bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden"
     id="{{ $componentId }}-container"
     x-data="{ onlineUsers: [], typingName: null, connectionState: window.EchoConnectionState || 'disconnected' }"
     @chat-presence.window="onlineUsers = $event.detail.users"
     @chat-typing.window="typingName = $event.detail.name; setTimeout(() => typingName = null, 1800)"
     @echo-state-change.window="connectionState = $event.detail.state">

    @if (!$conversation)
        {{-- Estado Vacío --}}
        <div class="flex flex-col items-center justify-center h-full text-center p-6 bg-gray-50 dark:bg-gray-900/50">
            <div class="w-20 h-20 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center mb-4 animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tu mensajería</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs">
                Selecciona una conversación de la lista para comenzar a chatear con tu equipo.
            </p>
        </div>
    @else
        @php
            $otro = $conversation->users->where('id', '!=', auth()->id())->first();
            $foto = $otro?->documentacionAltas?->arch_foto;
            $foto_url = $foto ? asset($foto) : asset('images/default-user.jpg');
        @endphp

        {{-- HEADER DEL CHAT --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 z-10">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <img src="{{ $foto_url }}" class="w-12 h-12 rounded-full object-cover border-2 border-white dark:border-gray-800 shadow-sm" alt="{{ $otro?->name }}">
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full"></span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white leading-tight">{{ $otro?->name }}</h3>
                    <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="h-1.5 w-1.5 rounded-full" :class="onlineUsers.some(user => user.id == {{ $otro?->id ?? 0 }}) ? 'bg-green-500' : 'bg-gray-400'"></span>
                        <span x-show="typingName" x-text="`${typingName} está escribiendo…`"></span>
                        <span x-show="!typingName" x-text="onlineUsers.some(user => user.id == {{ $otro?->id ?? 0 }}) ? 'En línea' : 'Desconectado'"></span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <label class="relative hidden sm:block">
                    <span class="sr-only">Buscar en esta conversación</span>
                    <input wire:model.live.debounce.300ms="buscarMensaje" type="search" placeholder="Buscar mensajes"
                           class="w-44 rounded-xl border border-gray-200 bg-gray-50 py-2 pl-3 pr-8 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <i class="ti ti-search absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true"></i>
                </label>
            <button onclick="Livewire.dispatch('cerrarConversacion')"
                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-full transition-colors"
                    title="Cerrar conversación (Esc)">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            </div>
        </div>

        {{-- AREA DE MENSAJES --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 dark:bg-gray-900/50 custom-scrollbar"
             id="messages-container"
             style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;">

            @if($hasMoreMessages)
                <div class="text-center">
                    <button wire:click="cargarAnteriores" wire:loading.attr="disabled" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-600 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Cargar mensajes anteriores</button>
                </div>
            @endif

            @php $fechaAnterior = null; @endphp
            @forelse ($messages as $msg)
                @php $esMio = $msg['user_id'] == auth()->id(); @endphp
                @php
                    $fechaMensaje = \Carbon\Carbon::parse($msg['created_at'])
                        ->timezone('America/Mexico_City')
                        ->locale('es');
                    $fechaClave = $fechaMensaje->toDateString();
                @endphp
                @if($fechaClave !== $fechaAnterior)
                    <div class="flex items-center gap-3 py-2" role="separator">
                        <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
                        <span class="text-[11px] font-semibold text-gray-400">{{ $fechaMensaje->isToday() ? 'Hoy' : ($fechaMensaje->isYesterday() ? 'Ayer' : $fechaMensaje->isoFormat('D [de] MMMM')) }}</span>
                        <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
                    </div>
                    @php $fechaAnterior = $fechaClave; @endphp
                @endif

                <div class="flex w-full {{ $esMio ? 'justify-end' : 'justify-start' }}">
                    <div class="flex max-w-[85%] md:max-w-[70%] {{ $esMio ? 'flex-row-reverse' : 'flex-row' }} items-end gap-2">

                        {{-- Avatar pequeño para contexto (opcional, solo si quieres estilo Slack/Teams) --}}
                        @if(!$esMio)
                            <img src="{{ $foto_url }}" class="w-6 h-6 rounded-full object-cover mb-1 opacity-70" alt="">
                        @endif

                        <div class="relative group px-4 py-2.5 text-sm shadow-sm break-words
                            {{ $esMio
                                ? 'bg-blue-600 text-white rounded-2xl rounded-tr-none'
                                : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 rounded-2xl rounded-tl-none'
                            }}">

                            @if(!empty($msg['parent']))
                                <div class="mb-2 rounded-lg border-l-2 {{ $esMio ? 'border-blue-200 bg-blue-700/40' : 'border-blue-500 bg-gray-50 dark:bg-gray-700' }} px-2 py-1 text-xs opacity-90">
                                    <strong>{{ $msg['parent']['user']['name'] ?? 'Mensaje' }}</strong>
                                    <p class="max-w-xs truncate">{{ $msg['parent']['body'] ?? 'Mensaje no disponible' }}</p>
                                </div>
                            @endif
                            <p class="whitespace-pre-wrap">{{ $msg['body'] }}</p>

                            {{-- Hora del mensaje (pequeña y discreta) --}}
                            <div class="text-[10px] mt-1 opacity-70 text-right {{ $esMio ? 'text-blue-100' : 'text-gray-400' }}">
                                {{ \Carbon\Carbon::parse($msg['created_at'])->timezone('America/Mexico_City')->format('H:i') }}
                                @if($esMio)
                                    <span class="ml-1">{{ !empty($msg['read_at']) ? '✓✓ Leído' : '✓ Enviado' }}</span>
                                @endif
                            </div>

                            <div class="absolute {{ $esMio ? 'right-full mr-1' : 'left-full ml-1' }} top-0 flex items-center gap-1 sm:hidden sm:group-hover:flex sm:group-focus-within:flex">
                                <button type="button" wire:click="responderA({{ $msg['id'] }})" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-500 shadow hover:text-blue-600 dark:bg-gray-700 dark:text-gray-300" aria-label="Responder"><i class="ti ti-arrow-back-up"></i></button>
                                @if($esMio && \Carbon\Carbon::parse($msg['created_at'])->greaterThanOrEqualTo(now()->subMinutes(15)))
                                    <button type="button" wire:click="eliminarMensaje({{ $msg['id'] }})" wire:confirm="¿Eliminar este mensaje para todos?" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-500 shadow hover:text-red-600 dark:bg-gray-700 dark:text-gray-300" aria-label="Eliminar para todos"><i class="ti ti-trash"></i></button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                @if(mb_strlen(trim($buscarMensaje)) >= 2)
                    <p class="py-10 text-center text-sm text-gray-500">No se encontraron mensajes.</p>
                @endif
            @endforelse
        </div>

        {{-- INPUT AREA --}}
        <div class="p-4 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
            @if($sendState === 'error')
                <div class="mb-2 flex items-center justify-between rounded-xl bg-red-50 px-3 py-2 text-xs text-red-700 dark:bg-red-900/20 dark:text-red-300" role="alert">
                    <span><i class="ti ti-alert-circle mr-1" aria-hidden="true"></i>No fue posible enviar el mensaje. El texto se conservó para reintentar.</span>
                    <button type="button" wire:click="enviarMensaje" class="ml-2 font-bold underline">Reintentar</button>
                </div>
            @endif
            @if($replyToMessageId)
                @php $replyMessage = collect($messages)->firstWhere('id', $replyToMessageId); @endphp
                <div class="mb-2 flex items-center justify-between rounded-xl border-l-4 border-blue-500 bg-blue-50 px-3 py-2 text-xs text-blue-900 dark:bg-blue-900/20 dark:text-blue-200">
                    <div class="min-w-0"><strong>Respondiendo a {{ $replyMessage['user']['name'] ?? 'mensaje' }}</strong><p class="truncate">{{ $replyMessage['body'] ?? '' }}</p></div>
                    <button type="button" wire:click="cancelarRespuesta" class="ml-2 h-8 w-8 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40" aria-label="Cancelar respuesta"><i class="ti ti-x"></i></button>
                </div>
            @endif
            <form wire:submit.prevent="enviarMensaje" class="flex items-end gap-2 bg-gray-50 dark:bg-gray-800 p-2 rounded-2xl border border-gray-200 dark:border-gray-700 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all">
                <textarea
                    x-ref="messageInput"
                    wire:model.defer="body"
                    @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); $wire.enviarMensaje(); }"
                    placeholder="Escribe un mensaje..."
                    class="flex-1 bg-transparent border-none resize-none max-h-32 px-3 py-2 text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:ring-0 focus:outline-none custom-scrollbar"
                    rows="1"
                    x-data="{
                        adjustHeight() {
                            this.$el.style.height = 'auto';
                            this.$el.style.height = this.$el.scrollHeight + 'px';
                        }
                    }"
                    x-init="adjustHeight()"
                    @input="adjustHeight()"
                    @input.debounce.250ms="window.currentPresenceChannel?.whisper('typing', { id: {{ auth()->id() }}, name: @js(auth()->user()->name) })"
                ></textarea>

                <button type="submit"
                        class="p-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                        title="Enviar mensaje"
                        wire:loading.attr="disabled">

                    <svg wire:loading.remove xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>

                    <svg wire:loading class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
            <div class="text-center mt-2">
                <p class="text-[10px] text-gray-400">
                    <span x-show="connectionState === 'connected'">Presiona Enter para enviar, Shift + Enter para nueva línea</span>
                    <span x-show="connectionState !== 'connected'" class="text-amber-600">Reconectando mensajería en tiempo real…</span>
                </p>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                Livewire.dispatch('cerrarConversacion');
            }
        });

        document.addEventListener('livewire:init', () => {
            // Función de scroll suave
            const scrollToBottom = (behavior = 'smooth') => {
                const container = document.getElementById('messages-container');
                if (container) {
                    container.scrollTo({
                        top: container.scrollHeight,
                        behavior: behavior
                    });
                }
            };

            Livewire.on('scrollToBottom', () => {
                setTimeout(() => scrollToBottom('smooth'), 100);
            });

            Livewire.on('scrollToBottomIfNearEnd', () => {
                const container = document.getElementById('messages-container');
                if (container && container.scrollHeight - container.scrollTop - container.clientHeight < 180) {
                    setTimeout(() => scrollToBottom('smooth'), 50);
                }
            });

            Livewire.on('preserveScrollPosition', () => {
                const container = document.getElementById('messages-container');
                if (!container) return;
                const previousHeight = container.scrollHeight;
                requestAnimationFrame(() => { container.scrollTop += container.scrollHeight - previousHeight; });
            });

            Livewire.on('focusMessageInput', () => document.querySelector('[x-ref="messageInput"]')?.focus());

            Livewire.on('conversacionSeleccionada', () => {
                setTimeout(() => scrollToBottom('auto'), 300);
            });

            // Lógica de Echo (Websockets)
            Livewire.on('updatedConversationId', (payload) => {
                if (!window.Echo) return;

                const conversationId = Array.isArray(payload)
                    ? payload[0]
                    : (payload?.conversationId ?? payload?.id ?? payload);

                if (window.currentEchoChannel) {
                    window.Echo.leave(window.currentEchoChannel);
                    window.currentEchoChannel = null;
                    window.currentPresenceChannel = null;
                    window.currentChatUsers = [];
                    window.dispatchEvent(new CustomEvent('chat-presence', { detail: { users: [] } }));
                }

                if (!conversationId || Number.isNaN(Number(conversationId))) return;

                const channelName = `conversacion.${conversationId}`;
                window.currentEchoChannel = channelName;

                window.currentPresenceChannel = window.Echo.join(channelName)
                    .here(users => {
                        window.currentChatUsers = users;
                        window.dispatchEvent(new CustomEvent('chat-presence', { detail: { users } }));
                    })
                    .joining(user => {
                        window.currentChatUsers = [...(window.currentChatUsers || []), user];
                        window.dispatchEvent(new CustomEvent('chat-presence', { detail: { users: window.currentChatUsers } }));
                    })
                    .leaving(user => {
                        window.currentChatUsers = (window.currentChatUsers || []).filter(item => item.id !== user.id);
                        window.dispatchEvent(new CustomEvent('chat-presence', { detail: { users: window.currentChatUsers } }));
                    })
                    .listenForWhisper('typing', user => window.dispatchEvent(new CustomEvent('chat-typing', { detail: user })))
                    .listen('.MensajeEnviado', (e) => {
                        const senderUserId = e.message?.user_id || e.message?.user?.id || null;
                        const currentUserId = {{ auth()->id() }};

                        if (senderUserId != currentUserId) {
                            @this.call('agregarMensaje', e);
                            // Opcional: Sonido de notificación suave
                            // new Audio('/sounds/notification.mp3').play().catch(e => {});
                        }
                    })
                    .listen('.MessagesRead', () => @this.call('actualizarLecturas'))
                    .listen('.MessageDeleted', e => @this.call('quitarMensajeEliminado', e.messageId || e.message_id))
                    .error((error) => {
                        console.error('Error en canal privado:', error);
                    });
            });
        });
    </script>
    @endpush
</div>
