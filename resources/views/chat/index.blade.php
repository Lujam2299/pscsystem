<meta name="csrf-token" content="{{ csrf_token() }}">
<x-app-layout>
    <x-navbar />

    <div x-data="{ mobileChatOpen: false, connectionState: window.EchoConnectionState || 'disconnected' }"
         @abrir-chat-movil.window="mobileChatOpen = true"
         @cerrar-chat-movil.window="mobileChatOpen = false"
         @echo-state-change.window="connectionState = $event.detail.state"
         class="flex h-[calc(100dvh-4rem)] min-h-[520px] flex-col overflow-hidden bg-gray-50 dark:bg-gray-900">

        {{-- HEADER PRINCIPAL DE LA APP --}}
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-3 flex items-center justify-between shadow-sm z-10">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}"
                   class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                   title="Volver al Dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Mensajería Interna
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">
                        Comunícate con tu equipo en tiempo real
                    </p>
                </div>
            </div>

            {{-- Indicador de Estado (Opcional, visual) --}}
            <div class="hidden items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium md:flex"
                 :class="connectionState === 'connected' ? 'border-green-100 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400' : 'border-amber-100 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400'">
                <span class="relative flex h-2 w-2">
                  <span x-show="connectionState === 'connected'" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2" :class="connectionState === 'connected' ? 'bg-green-500' : 'bg-amber-500'"></span>
                </span>
                <span x-text="connectionState === 'connected' ? 'Conectado' : 'Reconectando'"></span>
            </div>
        </header>

        {{-- AREA DE TRABAJO (GRID) --}}
        <main class="flex-1 p-4 overflow-hidden">
            <div class="max-w-7xl mx-auto h-full grid grid-cols-1 lg:grid-cols-12 gap-4">

                {{-- COLUMNA IZQUIERDA: LISTA DE CHATS --}}
                <div :class="mobileChatOpen ? 'hidden lg:flex' : 'flex'" class="lg:col-span-4 flex-col h-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    @livewire('mensajes-lista')
                </div>

                {{-- COLUMNA DERECHA: VENTANA DE CHAT --}}
                <div :class="mobileChatOpen ? 'flex' : 'hidden lg:flex'" class="lg:col-span-8 flex-col h-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden relative">
                    {{-- Overlay para móvil si no hay chat seleccionado (opcional, depende de tu lógica UX) --}}
                    @livewire('mensajes-chat')
                </div>

            </div>
        </main>
    </div>
</x-app-layout>
