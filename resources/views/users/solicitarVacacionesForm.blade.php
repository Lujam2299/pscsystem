@php
    $rol = strtolower(Auth()->user()->rol);
@endphp
<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Ficha Técnica
                    </h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-2">Datos Personales</h3>
                                <div class="space-y-2">
                                    <p class="text-sm"><span class="font-medium">Nombre:</span> {{ $solicitud->nombre }} {{ $solicitud->apellido_paterno }} {{ $solicitud->apellido_materno }}</p>
                                    <p class="text-sm"><span class="font-medium">CURP:</span> {{ $solicitud->curp }}</p>
                                    <p class="text-sm"><span class="font-medium">NSS:</span> {{ $solicitud->nss }}</p>
                                    <p class="text-sm"><span class="font-medium">RFC:</span> {{ $solicitud->rfc }}</p>
                                    <p class="text-sm"><span class="font-medium">Fecha Nac.:</span> {{ Carbon\Carbon::parse($solicitud->fecha_nacimiento)->format('d/m/Y') }}</p>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-2">Contacto</h3>
                                <div class="space-y-2">
                                    <p class="text-sm"><span class="font-medium">Email:</span> {{ $solicitud->email }}</p>
                                    <p class="text-sm"><span class="font-medium">Teléfono:</span> {{ $solicitud->telefono }}</p>
                                    <p class="text-sm"><span class="font-medium">Estado Civil:</span> {{ $solicitud->estado_civil }}</p>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg md:col-span-2">
                                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-2">Dirección</h3>
                                <p class="text-sm">{{ $solicitud->domicilio_calle }} #{{ $solicitud->domicilio_numero }}, {{ $solicitud->domicilio_colonia }}, {{ $solicitud->domicilio_ciudad }}, {{ $solicitud->domicilio_estado }}</p>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-2">Laboral</h3>
                                <div class="space-y-2">
                                    <p class="text-sm"><span class="font-medium">Puesto:</span> {{ $solicitud->rol }}</p>
                                    <p class="text-sm"><span class="font-medium">Empresa:</span> {{ $solicitud->empresa }}</p>
                                    <p class="text-sm"><span class="font-medium">Punto:</span> {{ $solicitud->punto }}</p>
                                    <p class="text-sm"><span class="font-medium">Ingreso:</span> {{ Carbon\Carbon::parse($user->fecha_ingreso)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Foto -->
                    <div class="flex flex-col items-center justify-start">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-lg w-full">
                            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-4 text-center">Foto del Solicitante</h3>
                            <div class="flex flex-col items-center space-y-4">
                                @if ($documentacion && $documentacion->arch_foto)
                                    <div class="relative">
                                        <img src="{{ asset($documentacion->arch_foto) }}" alt="Foto del usuario" class="w-40 h-40 object-cover rounded-full shadow-lg border-4 border-white dark:border-gray-600">
                                        <div class="absolute inset-0 rounded-full bg-black bg-opacity-0 hover:bg-opacity-10 transition-all duration-300 flex items-center justify-center">
                                            <a href="{{ asset($documentacion->arch_foto) }}" target="_blank" class="opacity-0 hover:opacity-100 transition-opacity duration-300 text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                    <a href="{{ asset($documentacion->arch_foto) }}" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Ver foto completa
                                    </a>
                                @else
                                    <div class="w-40 h-40 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay foto cargada</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos y Solicitud de Vacaciones -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Datos y Solicitud de Vacaciones
                    </h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Resumen de Vacaciones -->
<div class="lg:col-span-1">
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Resumen de Vacaciones
        </h3>

        <div class="space-y-4">
            <div class="flex justify-between items-center p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Antigüedad</span>
                <span class="text-lg font-bold text-blue-600">{{ $antiguedad }} años</span>
            </div>

            <div class="flex justify-between items-center p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Periodo</span>
                <span id="periodoDisplay" class="text-lg font-bold text-purple-600">{{ $antiguedad }}</span>
            </div>

            <div class="flex justify-between items-center p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Días por Ley</span>
                <span id="diasPorDerechoDisplay" class="text-lg font-bold text-green-600">{{ $dias }} días</span>
            </div>

            <div class="flex justify-between items-center p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Ya Utilizados</span>
                <span id="diasUtilizadosDisplay" class="text-lg font-bold text-yellow-600">{{ $diasUtilizados }} días</span>
            </div>

            <div class="flex justify-between items-center p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm border-2 border-blue-200 dark:border-blue-800">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Disponibles</span>
                <span id="diasDisponiblesDisplay" class="text-xl font-bold text-blue-600">{{ $diasDisponibles }} días</span>
            </div>
        </div>

        @if($diasDisponibles <= 0)
            <div class="mt-4 p-3 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-sm text-red-700 dark:text-red-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    No hay días disponibles para solicitar vacaciones.
                </p>
            </div>
        @endif
    </div>
</div>

                    <!-- Formulario de Solicitud -->
                    <div class="lg:col-span-2">
                        @if($diasDisponibles > 0)
                            <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Solicitar Vacaciones
                                </h3>

                                <form action="{{ route('user.solicitarVacaciones', $user->id) }}" method="POST" class="space-y-6">
                                    @csrf

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Vacaciones</label>
                                            <select name="tipo" id="tipo" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white" required>
                                                <option value="" disabled selected>Selecciona una opción</option>
                                                <option value="Disfrutadas">Disfrutadas</option>
                                                <option value="Pagadas">Pagadas</option>
                                            </select>
                                        </div>
                                            <div>
                                                <label for="periodo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Periodo (opcional)</label>
                                                <input type="number" name="periodo" id="periodo" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                                                    min="0" placeholder="Ej: 3">
                                            </div>

                                        <div>
                                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha de Inicio</label>
                                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white" required>
                                        </div>

                                        <div>
                                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha de Fin</label>
                                            <input type="date" name="fecha_fin" id="fecha_fin" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white" required>
                                        </div>

                                        <div>
                                            <label for="dias_solicitados" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Días Solicitados</label>
                                            <input type="number" name="dias_solicitados" id="dias_solicitados" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-800 cursor-not-allowed" readonly>
                                        </div>

                                        @if (str_contains(strtoupper($user->punto), 'MARY KAY') || str_contains(strtoupper($user->punto), 'MARYKAY'))
            <div class="md:col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="turno_doble" id="turno_doble" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Turno Doble (Sumar un día adicional)</span>
                </label>
            </div>
        @endif
                                    </div>

                                    <input type="hidden" name="dias_disponibles" value="{{ $diasDisponibles }}">
                                    <input type="hidden" name="dias_utilizados" value="{{ $diasUtilizados }}">
                                    <input type="hidden" name="dias_por_derecho" value="{{ $dias }}">

                                    <div class="flex items-center justify-between pt-4">
                                        <button type="submit" id="btn_solicitar"
                                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Solicitar Vacaciones
                                        </button>
                                    </div>
                                </form>

                                <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                    <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 flex items-center mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Notas importantes
                                    </h4>
                                    <ul class="text-xs text-yellow-700 dark:text-yellow-300 space-y-1">
                                        <li>• La fecha inicial debe ser mayor a la fecha actual</li>
                                        <li>• Los días solicitados no pueden exceder los días disponibles</li>
                                        <li>• La fecha de fin debe ser mayor o igual a la fecha inicial</li>
                                    </ul>
                                </div>
                            </div>
                        @else
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-8 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-red-500 dark:text-red-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <h3 class="text-lg font-medium text-red-800 dark:text-red-200 mb-2">No hay días disponibles</h3>
                                <p class="text-red-600 dark:text-red-400">No puedes solicitar vacaciones porque no tienes días disponibles.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const inicio = document.getElementById('fecha_inicio');
        const fin = document.getElementById('fecha_fin');
        const diasInput = document.getElementById('dias_solicitados');
        const btnSubmit = document.getElementById('btn_solicitar');
        const periodoInput = document.getElementById('periodo');
        const turnoDobleCheckbox = document.getElementById('turno_doble');

        // Elementos del resumen
        const periodoDisplay = document.getElementById('periodoDisplay');
        const diasPorDerechoDisplay = document.getElementById('diasPorDerechoDisplay');
        const diasUtilizadosDisplay = document.getElementById('diasUtilizadosDisplay');
        const diasDisponiblesDisplay = document.getElementById('diasDisponiblesDisplay');

        // Valores iniciales
        const antiguedadInicial = {{ $antiguedad }};
        const diasPorDerechoInicial = {{ $dias }};
        const diasUtilizadosInicial = {{ $diasUtilizados }};
        const diasDisponiblesInicial = {{ $diasDisponibles }};
        const userId = {{ $user->id }};

        let diasDisponiblesActuales = diasDisponiblesInicial;

        function calcularDias() {
            if (inicio.value && fin.value) {
                const fechaInicio = new Date(inicio.value);
                const fechaFin = new Date(fin.value);

                if (fechaFin >= fechaInicio) {
                    let diffDays = Math.ceil((fechaFin.getTime() - fechaInicio.getTime()) / (1000 * 60 * 60 * 24)) + 1;

                    // ✅ Si turno doble está marcado, sumar 1 día a los solicitados
                    if (turnoDobleCheckbox && turnoDobleCheckbox.checked) {
                        diffDays += 1;
                    }

                    diasInput.value = diffDays;

                    // ✅ Validar contra los días disponibles (sin restar nada por turno doble)
                    if (diffDays > diasDisponiblesActuales) {
                        btnSubmit.disabled = true;
                        btnSubmit.classList.add('bg-red-400', 'cursor-not-allowed');
                        btnSubmit.classList.remove('hover:bg-blue-700', 'bg-blue-600');
                    } else {
                        btnSubmit.disabled = false;
                        btnSubmit.classList.remove('bg-red-400', 'cursor-not-allowed');
                        btnSubmit.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    }
                } else {
                    diasInput.value = '';
                    btnSubmit.disabled = true;
                }
            }
        }

        function calcularDiasYaUtilizados(periodo) {
            return fetch(`/api/dias-utilizados/${userId}/${periodo}`)
                .then(response => response.json())
                .then(data => data.dias_utilizados || 0)
                .catch(error => {
                    console.error('Error obteniendo días utilizados:', error);
                    return 0;
                });
        }

        function actualizarValores(periodo) {
            if (!periodo) {
                periodoDisplay.textContent = `${antiguedadInicial}`;
                diasPorDerechoDisplay.textContent = `${diasPorDerechoInicial} días`;
                diasUtilizadosDisplay.textContent = `${diasUtilizadosInicial} días`;
                diasDisponiblesDisplay.textContent = `${diasDisponiblesInicial} días`;
                diasDisponiblesActuales = diasDisponiblesInicial;
                return;
            }

            periodoDisplay.textContent = `${periodo}`;

            let diasPorDerecho;
            if (periodo < 2) {
                diasPorDerecho = 12;
            } else if (periodo == 2) {
                diasPorDerecho = 14;
            } else if (periodo == 3) {
                diasPorDerecho = 16;
            } else if (periodo == 4) {
                diasPorDerecho = 18;
            } else if (periodo == 5) {
                diasPorDerecho = 20;
            } else if (periodo > 5 && periodo <= 10) {
                diasPorDerecho = 22;
            } else if (periodo > 10 && periodo <= 15) {
                diasPorDerecho = 24;
            } else if (periodo > 15 && periodo <= 20) {
                diasPorDerecho = 26;
            } else if (periodo > 20 && periodo <= 25) {
                diasPorDerecho = 28;
            } else if (periodo > 25 && periodo <= 30) {
                diasPorDerecho = 30;
            } else {
                diasPorDerecho = 32;
            }

            calcularDiasYaUtilizados(periodo).then(diasUtilizados => {
                const diasDisponibles = Math.max(0, diasPorDerecho - diasUtilizados);

                diasPorDerechoDisplay.textContent = `${diasPorDerecho} días`;
                diasUtilizadosDisplay.textContent = `${diasUtilizados} días`;
                diasDisponiblesDisplay.textContent = `${diasDisponibles} días`;

                diasDisponiblesActuales = diasDisponibles;
            });
        }

        if (turnoDobleCheckbox) {
            turnoDobleCheckbox.addEventListener('change', () => {
                const periodo = periodoInput.value ? parseInt(periodoInput.value) : null;
                if (periodo) {
                    actualizarValores(periodo);
                } else {
                    actualizarValores(null);
                }
                calcularDias();
            });
        }

        periodoInput.addEventListener('input', (e) => {
            const periodo = parseInt(e.target.value);
            if (periodo && periodo >= 0) {
                actualizarValores(periodo);
            } else {
                actualizarValores(null);
            }
        });

        inicio.addEventListener('change', calcularDias);
        fin.addEventListener('change', calcularDias);
    });
</script>
