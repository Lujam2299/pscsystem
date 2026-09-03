<x-app-layout>
    <x-navbar></x-navbar>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">

            {{-- Alertas --}}
            @if(session('success'))
                <div class="mb-6 flex items-center p-4 text-green-800 bg-green-50 dark:bg-green-900/30 dark:text-green-300 rounded-xl border border-green-200 dark:border-green-800 shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 flex items-center p-4 text-red-800 bg-red-50 dark:bg-red-900/30 dark:text-red-300 rounded-xl border border-red-200 dark:border-red-800 shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                {{-- Encabezado y Tabs --}}
                <div class="px-6 pt-6 pb-4 sm:px-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-6">Edición de Información del Usuario</h2>

                    @php
                        $tipoSeleccionado = $solicitud->tipo_empleado ?? 'oficina';
                        $tabs = [
                            'oficina' => 'Personal de Oficina',
                            'armado' => 'Personal Armado',
                            'noarmado' => 'Personal No Armado',
                        ];
                    @endphp

                    <div class="flex flex-wrap justify-center gap-2 bg-gray-100 dark:bg-gray-700/50 p-1.5 rounded-xl max-w-2xl mx-auto">
                        @foreach ($tabs as $claveTipo => $label)
                            <a href="javascript:void(0);"
                               onclick="cambiarTipo('{{ $claveTipo }}')"
                               id="tab_{{ $claveTipo }}"
                               class="flex-1 min-w-[140px] text-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200
                               {{ $tipoSeleccionado === $claveTipo
                                   ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm ring-1 ring-black/5 dark:ring-white/10'
                                   : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-600/30' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                    <!-- Inputs ocultos para mantener el estado del tipo -->
                    <input type="hidden" id="tipo_actual" name="tipo_actual" value="{{ $tipoSeleccionado }}">
                </div>

                <form action="{{ route('admin.actualizarUsuario', $solicitud->id) }}" method="POST" class="px-6 pb-8 sm:px-8">
                    @csrf
                    <input type="hidden" name="tipo" id="tipo_hidden" value="{{ old('tipo', $tipoSeleccionado) }}">

                    {{-- SECCIÓN 1: Datos Personales --}}
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-5 bg-blue-500 rounded-full"></span> Datos Personales
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre(s)</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $solicitud->nombre) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="apellido_paterno" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apellido Paterno</label>
                                <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $solicitud->apellido_paterno) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="apellido_materno" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apellido Materno</label>
                                <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno', $solicitud->apellido_materno) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de Nacimiento</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $solicitud->fecha_nacimiento) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="curp" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CURP</label>
                                <input type="text" id="curp" name="curp" value="{{ old('curp', $solicitud->curp) }}" maxlength="18" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none uppercase">
                            </div>
                            <div>
                                <label for="nss" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NSS</label>
                                <input type="text" id="nss" name="nss" value="{{ old('nss', $solicitud->nss) }}" maxlength="11" inputmode="numeric" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="edo_civil" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado Civil</label>
                                <select id="edo_civil" name="edo_civil" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                    <option value="" disabled {{ is_null(old('edo_civil', $solicitud->estado_civil)) ? 'selected' : '' }}>Selecciona una opción</option>
                                    <option value="Soltero" {{ old('edo_civil', $solicitud->estado_civil) === 'Soltero' ? 'selected' : '' }}>Soltero/a</option>
                                    <option value="Casado" {{ old('edo_civil', $solicitud->estado_civil) === 'Casado' ? 'selected' : '' }}>Casado/a</option>
                                    <option value="Divorciado" {{ old('edo_civil', $solicitud->estado_civil) === 'Divorciado' ? 'selected' : '' }}>Divorciado/a</option>
                                    <option value="Viudo" {{ old('edo_civil', $solicitud->estado_civil) === 'Viudo' ? 'selected' : '' }}>Viudo/a</option>
                                    <option value="Union Civil" {{ old('edo_civil', $solicitud->estado_civil) === 'Union Civil' ? 'selected' : '' }}>Unión civil</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 2: Contacto y Domicilio Fiscal --}}
                    <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-5 bg-indigo-500 rounded-full"></span> Contacto y Domicilio Fiscal
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                            <div>
                                <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono', $solicitud->telefono) }}" inputmode="tel" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label for="calle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Calle</label>
                                <input type="text" id="calle" name="calle" value="{{ old('calle', $solicitud->domicilio_calle) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="num_ext" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número Exterior</label>
                                <input type="text" id="num_ext" name="num_ext" value="{{ old('num_ext', $solicitud->domicilio_numero) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="colonia" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Colonia</label>
                                <input type="text" id="colonia" name="colonia" value="{{ old('colonia', $solicitud->domicilio_colonia) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="cp_fiscal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CP Fiscal</label>
                                <input type="text" id="cp_fiscal" name="cp_fiscal" value="{{ old('cp_fiscal', $solicitud->cp_fiscal) }}" maxlength="5" inputmode="numeric" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="ciudad" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ciudad</label>
                                <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad', $solicitud->domicilio_ciudad) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                                <input type="text" id="estado" name="estado" value="{{ old('estado', $solicitud->domicilio_estado) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: Datos Fiscales y Complementarios --}}
                    <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-5 bg-emerald-500 rounded-full"></span> Datos Fiscales y Complementarios
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <label for="rfc" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">RFC</label>
                                <input type="text" id="rfc" name="rfc" value="{{ old('rfc', $solicitud->rfc) }}" maxlength="13" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none uppercase">
                            </div>
                            <div>
                                <label for="liga_rfc" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Liga RFC</label>
                                <input type="text" id="liga_rfc" name="liga_rfc" value="{{ old('liga_rfc', $solicitud->liga_rfc) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label for="domicilio_comprobante" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Domicilio de Comprobante</label>
                                <input type="text" id="domicilio_comprobante" name="domicilio_comprobante" value="{{ old('domicilio_comprobante', $solicitud->domicilio_comprobante) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="infonavit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Infonavit <span class="text-xs text-gray-500 font-normal">(Opcional)</span></label>
                                <input type="text" id="infonavit" name="infonavit" value="{{ old('infonavit', $solicitud->infonavit) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="fonacot" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fonacot <span class="text-xs text-gray-500 font-normal">(Opcional)</span></label>
                                <input type="text" id="fonacot" name="fonacot" value="{{ old('fonacot', $solicitud->fonacot) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 4: Datos Laborales y Físicos --}}
                    <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-5 bg-amber-500 rounded-full"></span> Datos Laborales y Físicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                            <div>
                                <label for="peso" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Peso (kg)</label>
                                <input type="text" id="peso" name="peso" value="{{ old('peso', $solicitud->peso) }}" inputmode="decimal" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="estatura" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estatura (m)</label>
                                <input type="text" id="estatura" name="estatura" value="{{ old('estatura', $solicitud->estatura) }}" inputmode="decimal" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>

                            {{-- Departamento (Condicional) --}}
                            <div id="departamento_group" style="{{ ($solicitud->tipo_empleado !== 'oficina' || Auth::user()->rol == 'Supervisor') ? 'display:none;' : '' }}">
                                <label for="departamento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Departamento</label>
                                <select id="departamento" name="departamento" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                    <option value="" disabled {{ is_null(old('departamento', $solicitud->departamento)) ? 'selected' : '' }}>Selecciona una opción</option>
                                    <option value="Recursos Humanos" {{ old('departamento', $solicitud->departamento) === 'Recursos Humanos' ? 'selected' : '' }}>Recursos Humanos</option>
                                    <option value="Nóminas" {{ old('departamento', $solicitud->departamento) === 'Nóminas' ? 'selected' : '' }}>Nóminas</option>
                                    <option value="Jurídico" {{ old('departamento', $solicitud->departamento) === 'Jurídico' ? 'selected' : '' }}>Jurídico</option>
                                    <option value="IMSS" {{ old('departamento', $solicitud->departamento) === 'IMSS' ? 'selected' : '' }}>IMSS</option>
                                    <option value="Monitoreo" {{ old('departamento', $solicitud->departamento) === 'Monitoreo' ? 'selected' : '' }}>Monitoreo</option>
                                    <option value="Custodios" {{ old('departamento', $solicitud->departamento) === 'Custodios' ? 'selected' : '' }}>Custodios</option>
                                    <option value="Compras" {{ old('departamento', $solicitud->departamento) === 'Compras' ? 'selected' : '' }}>Compras</option>
                                </select>
                            </div>

                            <div>
                                <label for="rol" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rol / Puesto</label>
                                <input type="text" id="rol" name="rol" value="{{ old('rol', $solicitud->rol) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div id="zona_supervisor_group" @if(strtoupper(trim((string) old('rol', $solicitud->rol))) !== 'SUPERVISOR') hidden @endif>
                                <label for="zona_supervisor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Zona del supervisor (opcional)</label>
                                <select id="zona_supervisor" name="zona_supervisor" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="">Sin cambio de zona</option>
                                    @foreach($zonasSupervisor as $zona => $referencia)
                                        <option value="{{ $zona }}" @selected((string) old('zona_supervisor', $solicitud->zona_supervisor) === (string) $zona)>{{ $referencia }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Opcional; dejarlo vacío conserva las asignaciones actuales.</p>
                                @error('zona_supervisor') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <script>
                                (() => {
                                    const role = document.getElementById('rol');
                                    const group = document.getElementById('zona_supervisor_group');
                                    const select = document.getElementById('zona_supervisor');
                                    const update = () => {
                                        const supervisor = role.value.trim().toUpperCase() === 'SUPERVISOR';
                                        group.hidden = !supervisor;
                                        select.disabled = !supervisor;
                                        if (supervisor) role.value = 'SUPERVISOR';
                                    };
                                    role.addEventListener('input', update);
                                    update();
                                })();
                            </script>
                            <div class="lg:col-span-2">
                                <label for="punto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Punto / Subpunto</label>
                                <select id="punto" name="punto" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                    <option disabled selected value="">Seleccione un subpunto</option>
                                    @foreach($puntos as $punto)
                                        <optgroup label="{{ $punto->nombre }}">
                                            @foreach($punto->subpuntos as $subpunto)
                                                <option value="{{ $subpunto->nombre }}" {{ old('punto', $solicitud->punto) === $subpunto->nombre ? 'selected' : '' }}>
                                                    @if($subpunto->codigo != null)({{ str_pad($subpunto->codigo, 3, '0', STR_PAD_LEFT) }}) @endif {{ $subpunto->nombre }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="reingreso" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reingreso</label>
                                <input type="text" id="reingreso" name="reingreso" value="{{ old('reingreso', $solicitud->reingreso) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>

                            {{-- NUEVOS CAMPOS BANCARIOS EN EDICIÓN --}}
                            <div>
                                <label for="tipo_periodo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Pago</label>
                                <select id="tipo_periodo" name="tipo_periodo" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                    <option value="" disabled {{ is_null(old('tipo_periodo', $solicitud->tipo_periodo)) ? 'selected' : '' }}>Selecciona frecuencia</option>
                                    <option value="semanal" {{ old('tipo_periodo', $solicitud->tipo_periodo) == 'semanal' ? 'selected' : '' }}>Semanal</option>
                                    <option value="quincenal" {{ old('tipo_periodo', $solicitud->tipo_periodo) == 'quincenal' ? 'selected' : '' }}>Quincenal</option>
                                </select>
                            </div>
                            <div class="lg:col-span-2">
                                <label for="banco" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Banco</label>
                                <input type="text" id="banco" name="banco" value="{{ old('banco', $solicitud->banco) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div class="md:col-span-2 lg:col-span-3">
                                <label for="cuenta_bancaria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cuenta Bancaria / CLABE</label>
                                <div class="relative">
                                    <input type="text" id="cuenta_bancaria" name="cuenta_bancaria" value="{{ old('cuenta_bancaria', $solicitud->cuenta_bancaria) }}" maxlength="20" inputmode="numeric" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none font-mono tracking-wide">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            {{-- FIN NUEVOS CAMPOS --}}

                            <div>
                                <label for="empresa" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                                <select id="empresa" name="empresa" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                    <option value="" disabled {{ is_null(old('empresa', $solicitud->empresa)) ? 'selected' : '' }}>Selecciona una empresa</option>
                                    <option value="CPKC" {{ old('empresa', $solicitud->empresa) == 'CPKC' ? 'selected' : '' }}>CPKC</option>
                                    <option value="SPYT" {{ old('empresa', $solicitud->empresa) == 'SPYT' ? 'selected' : '' }}>SPYT</option>
                                    <option value="Montana" {{ old('empresa', $solicitud->empresa) == 'Montana' ? 'selected' : '' }}>Montana</option>
                                    <option value="PSC" {{ old('empresa', $solicitud->empresa) == 'PSC' ? 'selected' : '' }}>PSC</option>
                                </select>
                            </div>
                            <div>
                                <label for="sueldo_mensual" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sueldo Mensual</label>
                                <input type="text" id="sueldo_mensual" name="sueldo_mensual" value="{{ old('sueldo_mensual', $solicitud->sueldo_mensual) }}" inputmode="decimal" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="fecha_ingreso" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de Ingreso</label>
                                <input type="date" id="fecha_ingreso" name="fecha_ingreso" value="{{ old('fecha_ingreso', $solicitud->fecha_ingreso) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 5: Cuenta de Acceso --}}
                    <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-5 bg-violet-500 rounded-full"></span> Cuenta de Acceso
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div class="md:col-span-2 lg:col-span-1">
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Correo Electrónico</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $solicitud->email) }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m0 0V4"/></svg>
                            Guardar Cambios
                        </button>
                        <a href="{{ route('user.verFicha', $user->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>

            <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                <span class="font-medium">Nota:</span> Al guardar los cambios, se creará un registro de edición y el usuario será actualizado según los nuevos datos proporcionados.
            </p>
        </div>
    </div>

    <script>
    function cambiarTipo(tipo) {
        console.log('Cambiando tipo a:', tipo);

        try {
            // Actualizar los campos ocultos
            const tipoHidden = document.getElementById('tipo_hidden');
            const tipoActualInput = document.getElementById('tipo_actual');

            if (tipoHidden) tipoHidden.value = tipo;
            if (tipoActualInput) tipoActualInput.value = tipo;

            // Obtener el rol del usuario
            const rolUsuario = '{{ Auth::user()->rol ?? "" }}';

            // Mostrar/ocultar campo departamento
            const departamentoGroup = document.getElementById('departamento_group');
            if (departamentoGroup) {
                // Lógica: Si es oficina Y el usuario NO es supervisor, mostramos departamento
                if (rolUsuario !== 'Supervisor' && tipo === 'oficina') {
                    departamentoGroup.style.display = 'block';
                } else {
                    departamentoGroup.style.display = 'none';
                }
            }

            // Actualizar la clase visual de las pestañas
            const tabs = ['oficina', 'armado', 'noarmado'];
            tabs.forEach(tabTipo => {
                const tabElement = document.getElementById('tab_' + tabTipo);
                if (tabElement) {
                    if (tabTipo === tipo) {
                        // Estilo Activo
                        tabElement.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-900', 'dark:hover:text-gray-200', 'hover:bg-white/50', 'dark:hover:bg-gray-600/30');
                        tabElement.classList.add('bg-white', 'dark:bg-gray-600', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm', 'ring-1', 'ring-black/5', 'dark:ring-white/10');
                    } else {
                        // Estilo Inactivo
                        tabElement.classList.remove('bg-white', 'dark:bg-gray-600', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm', 'ring-1', 'ring-black/5', 'dark:ring-white/10');
                        tabElement.classList.add('text-gray-600', 'dark:text-gray-400', 'hover:text-gray-900', 'dark:hover:text-gray-200', 'hover:bg-white/50', 'dark:hover:bg-gray-600/30');
                    }
                }
            });
        } catch (error) {
            console.error('Error en cambiarTipo:', error);
        }
    }

    // Ejecutar al cargar la página para establecer el estado inicial correcto
    document.addEventListener('DOMContentLoaded', function() {
        const tipoActual = document.getElementById('tipo_actual');
        if (tipoActual) {
            cambiarTipo(tipoActual.value);
        }
    });
    </script>
</x-app-layout>
