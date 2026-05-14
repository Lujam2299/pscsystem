<meta name="csrf-token" content="{{ csrf_token() }}">
<x-app-layout>
    <x-navbar />

    <div class="h-[calc(100vh-4rem)] bg-gray-50 dark:bg-gray-900 flex flex-col overflow-hidden">

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
            <div class="hidden md:flex items-center gap-2 px-3 py-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-full text-xs font-medium border border-green-100 dark:border-green-800">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Conectado
            </div>
        </header>

        {{-- AREA DE TRABAJO (GRID) --}}
        <main class="flex-1 p-4 overflow-hidden">
            <div class="max-w-7xl mx-auto h-full grid grid-cols-1 lg:grid-cols-12 gap-4">

                {{-- COLUMNA IZQUIERDA: LISTA DE CHATS --}}
                <div class="lg:col-span-4 flex flex-col h-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    @livewire('mensajes-lista')
                </div>

                {{-- COLUMNA DERECHA: VENTANA DE CHAT --}}
                <div class="lg:col-span-8 flex flex-col h-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden relative">
                    {{-- Overlay para móvil si no hay chat seleccionado (opcional, depende de tu lógica UX) --}}
                    @livewire('mensajes-chat')
                </div>

            </div>
        </main>
    </div>
</x-app-layout>
