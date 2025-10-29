<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                                Nueva Misión
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Complete la información para crear una nueva misión
                            </p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('misiones.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de Inicio
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                onchange="actualizarAgentes()" required>
                        </div>

                        <div>
                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de Fin
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                onchange="actualizarAgentes()" required>
                        </div>
                    </div>

                    <div>
                        <label for="agentes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Agentes Asignados
                        </label>
                        <select id="agentes" name="agentes_id[]"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                multiple required>
                            <option disabled>Selecciona fechas para ver agentes disponibles</option>
                        </select>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Usa Ctrl + Clic para seleccionar varios agentes
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="nivel_amenaza" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nivel de Amenaza
                            </label>
                            <select name="nivel_amenaza" id="nivel_amenaza"
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    required>
                                <option value="" disabled selected>Selecciona un nivel</option>
                                <option value="bajo">Bajo</option>
                                <option value="medio">Medio</option>
                                <option value="alto">Alto</option>
                            </select>
                        </div>

                        <div>
                            <label for="armados" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Agentes
                            </label>
                            <select name="armados" id="armados"
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    required>
                                <option value="" disabled selected>Selecciona tipo</option>
                                <option value="armado">Armados</option>
                                <option value="desarmado">Desarmados</option>
                            </select>
                        </div>

                        <div>
                            <label for="tipo_servicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Servicio
                            </label>
                            <input type="text" name="tipo_servicio" id="tipo_servicio"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                required>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Ubicaciones
                            </h2>
                        </div>

                        <div id="ubicaciones-container" class="space-y-4 mb-4">
                            <div class="ubicacion-item bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Dirección
                                </label>
                                <input type="text" name="ubicaciones[0][direccion]"
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white mb-3"
                                    placeholder="Ej. Calle X, Ciudad, Estado">

                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                    O ingresa latitud y longitud directamente:
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Latitud
                                        </label>
                                        <input type="text" name="ubicaciones[0][latitud]"
                                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Longitud
                                        </label>
                                        <input type="text" name="ubicaciones[0][longitud]"
                                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="agregarUbicacion()"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Agregar otra ubicación
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="nombre_clave" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre Clave
                            </label>
                            <input type="text" name="nombre_clave" id="nombre_clave"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                required>
                        </div>

                        <div>
                            <label for="cliente" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cliente
                            </label>
                            <input type="text" name="cliente" id="cliente"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label for="pasajeros" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Pasajeros
                            </label>
                            <input type="text" name="pasajeros" id="pasajeros"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tipo_operacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Operación (opcional)
                            </label>
                            <input type="text" name="tipo_operacion" id="tipo_operacion"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label for="num_vehiculos" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Número de Vehículos
                            </label>
                            <input type="number" name="num_vehiculos" id="num_vehiculos"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                min="0">
                        </div>
                    </div>

                    <div>
                        <label for="tipo_vehiculos" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tipo de Vehículos
                        </label>
                        <select name="tipo_vehiculos[]" id="tipo_vehiculos" multiple
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="Sedán">Sedán</option>
                            <option value="SUV">SUV</option>
                            <option value="Pick-up">Pick-up</option>
                            <option value="Blindado">Blindado</option>
                            <option value="Otro">Otro</option>
                        </select>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Selecciona uno o varios tipos de vehículos
                        </p>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Información Adicional
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Hotel
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                        <input type="text" name="hotel[nombre]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección</label>
                                        <input type="text" name="hotel[direccion]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                                        <input type="text" name="hotel[telefono]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                </div>
                            </div>

                            <!--<div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    Hospital
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                        <input type="text" name="hospital[nombre]"
                                               class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección</label>
                                        <input type="text" name="hospital[direccion]"
                                               class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                                        <input type="text" name="hospital[telefono]"
                                               class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Embajada
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                        <input type="text" name="embajada[nombre]"
                                               class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección</label>
                                        <input type="text" name="embajada[direccion]"
                                               class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                                        <input type="text" name="embajada[telefono]"
                                               class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                </div>
                            </div>-->

                            <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Aeropuerto
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                        <input type="text" name="aeropuerto[nombre]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección</label>
                                        <input type="text" name="aeropuerto[direccion]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                                        <input type="text" name="aeropuerto[telefono]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Datos del Vuelo
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                                        <input type="date" name="vuelo[fecha]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Flight</label>
                                        <input type="text" name="vuelo[flight]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hora</label>
                                        <input type="time" name="vuelo[hora]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pax</label>
                                        <input type="text" name="vuelo[pax]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Evento</label>
                                        <input type="text" name="vuelo[evento]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Aeropuerto</label>
                                        <input type="text" name="vuelo[aeropuerto]"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Registrar Misión
                        </button>
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Regresar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function actualizarAgentes() {
            const inicio = document.getElementById('fecha_inicio').value;
            const fin = document.getElementById('fecha_fin').value;
            const select = document.getElementById('agentes');

            if (!inicio || !fin) return;

            fetch('/agentes-disponibles', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        fecha_inicio: inicio,
                        fecha_fin: fin
                    })
                })
                .then(res => res.json())
                .then(agentes => {
                    select.innerHTML = '';

                    agentes.forEach(agente => {
                        const option = document.createElement('option');
                        option.value = agente.id;
                        option.textContent = agente.name;

                        if (agente.ocupado) {
                            option.disabled = true;
                            option.textContent += ' (Ocupado)';
                        }

                        select.appendChild(option);
                    });
                });
        }
    </script>

    <script>
        let ubicacionIndex = 1;

        function agregarUbicacion() {
            const container = document.getElementById('ubicaciones-container');
            const nuevaUbicacion = document.createElement('div');
            nuevaUbicacion.className = 'ubicacion-item bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg';

            nuevaUbicacion.innerHTML = `
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Dirección
                </label>
                <input type="text" name="ubicaciones[${ubicacionIndex}][direccion]"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white mb-3"
                    placeholder="Ej. Calle X, Ciudad, Estado">

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    O ingresa latitud y longitud directamente:
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Latitud
                        </label>
                        <input type="text" name="ubicaciones[${ubicacionIndex}][latitud]"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Longitud
                        </label>
                        <input type="text" name="ubicaciones[${ubicacionIndex}][longitud]"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            `;
            container.appendChild(nuevaUbicacion);
            ubicacionIndex++;
        }
    </script>
</x-app-layout>
