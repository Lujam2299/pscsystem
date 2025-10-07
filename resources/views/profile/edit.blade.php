<x-app-layout>
    <x-navbar></x-navbar>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- ✅ Contenedor global: fondo blanco, redondeado, sombra -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">

                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Mi Perfil
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Actualiza tu información personal y configuración de seguridad
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Columna izquierda: Formularios -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Información del perfil -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center mb-5">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h2 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">
                                    Información Personal
                                </h2>
                            </div>
                            @include('profile.partials.update-profile-information-form')
                        </div>

                        <!-- Contraseña -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center mb-5">
                                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <h2 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">
                                    Contraseña
                                </h2>
                            </div>
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    <!-- Columna derecha: Foto de perfil -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 border border-gray-200 dark:border-gray-700 sticky top-6">
                            <div class="flex items-center mb-4">
                                <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h2 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">
                                    Foto de Perfil
                                </h2>
                            </div>

                            <div class="flex justify-center mt-4">
                                @php
                                    $foto = auth()->user()->documentacionAltas?->arch_foto
                                        ? asset(auth()->user()->documentacionAltas->arch_foto)
                                        : asset('images/default-user.jpg');
                                @endphp

                                <img src="{{ $foto }}"
                                     alt="Foto del usuario"
                                     class="h-32 w-32 md:h-40 md:w-40 object-cover rounded-full border-4 border-white dark:border-gray-700 shadow-md">
                            </div>

                            <p class="mt-4 text-sm text-center text-gray-600 dark:text-gray-400">
                                Tu foto se actualiza automáticamente al subir documentos en tu ficha de alta.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
