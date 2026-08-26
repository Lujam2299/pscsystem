<x-guest-layout>
    <div class="mx-auto max-w-lg space-y-5 text-center">
        <div class="text-6xl font-semibold text-gray-300 dark:text-gray-600">403</div>

        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                Acceso no autorizado
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $exception->getMessage() ?: 'Tu cuenta no tiene permiso para acceder a este módulo.' }}
            </p>
        </div>

        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
           class="inline-flex min-h-11 items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
            {{ auth()->check() ? 'Regresar al panel' : 'Ir al inicio de sesión' }}
        </a>
    </div>
</x-guest-layout>
