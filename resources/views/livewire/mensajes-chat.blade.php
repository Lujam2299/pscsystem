<div class="flex flex-col h-full bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden"
     id="{{ $componentId }}-container">

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
                    <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        En línea
                    </p>
                </div>
            </div>

            <button onclick="Livewire.dispatch('cerrarConversacion')"
                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-full transition-colors"
                    title="Cerrar conversación (Esc)">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- AREA DE MENSAJES --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 dark:bg-gray-900/50 custom-scrollbar"
             id="messages-container"
             style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;">

            @foreach ($messages as $msg)
                @php $esMio = $msg['user_id'] == auth()->id(); @endphp

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

                            {{ $msg['body'] }}

                            {{-- Hora del mensaje (pequeña y discreta) --}}
                            <div class="text-[10px] mt-1 opacity-70 text-right {{ $esMio ? 'text-blue-100' : 'text-gray-400' }}">
                                {{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- INPUT AREA --}}
        <div class="p-4 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
            <form wire:submit.prevent="enviarMensaje" class="flex items-end gap-2 bg-gray-50 dark:bg-gray-800 p-2 rounded-2xl border border-gray-200 dark:border-gray-700 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all">
                <textarea
                    wire:model.defer="body"
                    wire:keydown.enter.prevent="if(!$event.shiftKey) { $wire.enviarMensaje() }"
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
                <p class="text-[10px] text-gray-400">Presiona Enter para enviar, Shift + Enter para nueva línea</p>
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

            Livewire.on('conversacionSeleccionada', () => {
                setTimeout(() => scrollToBottom('auto'), 300);
            });

            // Lógica de Echo (Websockets)
            Livewire.on('updatedConversationId', (conversationId) => {
                if (!window.Echo) return;

                if (window.currentEchoChannel) {
                    window.Echo.leave(window.currentEchoChannel);
                }

                if (!conversationId) return;

                const channelName = `conversacion.${conversationId}`;
                window.currentEchoChannel = channelName;

                window.Echo.private(channelName)
                    .listen('.MensajeEnviado', (e) => {
                        const senderUserId = e.message?.user_id || e.message?.user?.id || null;
                        const currentUserId = {{ auth()->id() }};

                        if (senderUserId != currentUserId) {
                            @this.call('agregarMensaje', e);
                            // Opcional: Sonido de notificación suave
                            // new Audio('/sounds/notification.mp3').play().catch(e => {});
                        }
                    })
                    .error((error) => {
                        console.error('Error en canal privado:', error);
                    });
            });
        });
    </script>
    @endpush
</div>
