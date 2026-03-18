<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Edición de Información del Usuario</h2>

                @if(session('success'))
                    <div class="bg-green-100 border-t-4 border-green-500 rounded-b text-green-900 px-4 py-3 shadow-md" role="alert">
                        <div class="flex">
                            <div>
                                <p class="text-sm">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error bg-red-200 text-red-800 p-4 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="flex justify-center mb-6">
                    @php
                        $tipoSeleccionado = $solicitud->tipo_empleado ?? 'oficina';
                        $tabs = [
                            'oficina' => 'Personal de Oficina',
                            'armado' => 'Personal Armado',
                            'noarmado' => 'Personal No Armado',
                        ];
                    @endphp

                    @foreach ($tabs as $claveTipo => $label)
                        <a href="javascript:void(0);"
                           onclick="cambiarTipo('{{ $claveTipo }}')"
                           class="px-4 py-2 mx-1 rounded-t-lg border-b-2 {{ $tipoSeleccionado === $claveTipo ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-500 hover:text-blue-600' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                    <input type="hidden" id="tipo_actual" name="tipo_actual" value="{{ $tipoSeleccionado }}">
                </div>

                <form action="{{ route('sup.editarInformacionSolicitud', $solicitud->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="tipo" id="tipo_hidden" value="{{ old('tipo', $tipoSeleccionado) }}">

                    <div class="form-group mb-4">
                        <label for="name" class="block text-sm font-semibold text-gray-600">Nombre(s)</label>
                        <input type="text" id="name" name="name" placeholder="Nombre completo" value="{{ old('name', $solicitud->nombre) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="apellido_paterno" class="block text-sm font-semibold text-gray-600">Apellido Paterno</label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno"  placeholder="Apellido Paterno" value="{{ old('apellido_paterno', $solicitud->apellido_paterno) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="apellido_materno" class="block text-sm font-semibold text-gray-600">Apellido Materno</label>
                        <input type="text" id="apellido_materno" name="apellido_materno"  placeholder="Apellido Materno" value="{{ old('apellido_materno', $solicitud->apellido_materno) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="fecha_nacimiento" class="block text-sm font-semibold text-gray-600">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="fecha de nacimiento" value="{{ old('fecha_nacimiento', $solicitud->fecha_nacimiento) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="curp" class="block text-sm font-semibold text-gray-600" minlength="18" maxlength="18">CURP</label>
                        <input type="text" id="curp" name="curp" placeholder="CURP" value="{{ old('curp', $solicitud->curp) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="nss" class="block text-sm font-semibold text-gray-600" minlength="11" maxlength="11">NSS</label>
                        <input type="text" id="nss" name="nss" placeholder="NSS" value="{{ old('nss', $solicitud->nss) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-6">
                        <label for="edo_civil" class="block text-sm font-semibold text-gray-600">Estado Civil</label>
                        <select id="edo_civil" name="edo_civil" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                            <option value="" disabled {{ is_null(old('edo_civil', $solicitud->estado_civil)) ? 'selected' : '' }}>Selecciona una opción</option>
                            <option value="Soltero" {{ old('edo_civil', $solicitud->estado_civil) === 'Soltero' ? 'selected' : '' }}>Soltero/a</option>
                            <option value="Casado" {{ old('edo_civil', $solicitud->estado_civil) === 'Casado' ? 'selected' : '' }}>Casado/a</option>
                            <option value="Divorciado" {{ old('edo_civil', $solicitud->estado_civil) === 'Divorciado' ? 'selected' : '' }}>Divorciado/a</option>
                            <option value="Viudo" {{ old('edo_civil', $solicitud->estado_civil) === 'Viudo' ? 'selected' : '' }}>Viudo/a</option>
                            <option value="Union Civil" {{ old('edo_civil', $solicitud->estado_civil) === 'Union Civil' ? 'selected' : '' }}>Unión civil</option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label for="rfc" class="block text-sm font-semibold text-gray-600" minlength="13" maxlength="13">RFC</label>
                        <input type="text" id="rfc" name="rfc" placeholder="RFC" value="{{ old('rfc', $solicitud->rfc) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="telefono" class="block text-sm font-semibold text-gray-600">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" placeholder="Telefono" value="{{ old('telefono', $solicitud->telefono) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="calle" class="block text-sm font-semibold text-gray-600">Domicilio Fiscal (Calle)</label>
                        <input type="text" id="calle" name="calle" placeholder="Calle" value="{{ old('calle', $solicitud->domicilio_calle) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="num_ext" class="block text-sm font-semibold text-gray-600">Domicilio Fiscal(Numero)</label>
                        <input type="text" id="num_ext" name="num_ext" placeholder="Numero" value="{{ old('num_ext', $solicitud->domicilio_numero) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="colonia" class="block text-sm font-semibold text-gray-600">Domicilio Fiscal(Colonia)</label>
                        <input type="text" id="colonia" name="colonia" placeholder="Colonia" value="{{ old('colonia', $solicitud->domicilio_colonia) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="cp_fiscal" class="block text-sm font-semibold text-gray-600">CP Fiscal</label>
                        <input type="text" id="cp_fiscal" name="cp_fiscal" placeholder="CP Fiscal" value="{{ old('cp_fiscal', $solicitud->cp_fiscal) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="ciudad" class="block text-sm font-semibold text-gray-600">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" placeholder="Ciudad" value="{{ old('ciudad', $solicitud->domicilio_ciudad) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="estado" class="block text-sm font-semibold text-gray-600">Estado</label>
                        <input type="text" id="estado" name="estado" placeholder="Estado" value="{{ old('estado', $solicitud->domicilio_estado) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="liga_rfc" class="block text-sm font-semibold text-gray-600">Liga RFC</label>
                        <input type="text" id="liga_rfc" name="liga_rfc" placeholder="Liga RFC" value="{{ old('liga_rfc', $solicitud->liga_rfc) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="domicilio_comprobante" class="block text-sm font-semibold text-gray-600">Domicilio de Comprobante</label>
                        <input type="text" id="domicilio_comprobante" name="domicilio_comprobante" placeholder="Domicilio de Comprobante" value="{{ old('domicilio_comprobante', $solicitud->domicilio_comprobante) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="infonavit" class="block text-sm font-semibold text-gray-600">Infonavit (Opcional)</label>
                        <input type="text" id="infonavit" name="infonavit" placeholder="Infonavit" value="{{ old('infonavit', $solicitud->infonavit) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="fonacot" class="block text-sm font-semibold text-gray-600">Fonacot (Opcional)</label>
                        <input type="text" id="fonacot" name="fonacot" placeholder="Fonacot" value="{{ old('fonacot', $solicitud->fonacot) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="peso" class="block text-sm font-semibold text-gray-600">Peso</label>
                        <input type="text" id="peso" name="peso" placeholder="Peso" value="{{ old('peso', $solicitud->peso) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="estatura" class="block text-sm font-semibold text-gray-600">Estatura</label>
                        <input type="text" id="estatura" name="estatura" placeholder="Estatura" value="{{ old('estatura', $solicitud->estatura) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-6" id="departamento_group" style="{{ $solicitud->tipo_empleado !== 'oficina' ? 'display:none;' : '' }}">
    <label for="departamento" class="block text-sm font-semibold text-gray-600">Departamento</label>
    <select id="departamento" name="departamento" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
        <option value="" disabled selected>Selecciona una opción</option>
        <option value="Recursos Humanos" {{ old('departamento', $solicitud->departamento) === 'Recursos Humanos' ? 'selected' : '' }}>Recursos Humanos</option>
        <option value="Nóminas" {{ old('departamento', $solicitud->departamento) === 'Nóminas' ? 'selected' : '' }}>Nóminas</option>
        <option value="Jurídico" {{ old('departamento', $solicitud->departamento) === 'Jurídico' ? 'selected' : '' }}>Jurídico</option>
        <option value="IMSS" {{ old('departamento', $solicitud->departamento) === 'IMSS' ? 'selected' : '' }}>IMSS</option>
        <option value="Monitoreo" {{ old('departamento', $solicitud->departamento) === 'Monitoreo' ? 'selected' : '' }}>Monitoreo</option>
        <option value="Custodios" {{ old('departamento', $solicitud->departamento) === 'Custodios' ? 'selected' : '' }}>Custodios</option>
        <option value="Compras" {{ old('departamento', $solicitud->departamento) === 'Compras' ? 'selected' : '' }}>Compras</option>
    </select>
</div>

                    <div class="form-group mb-6">
                        <label for="rol" class="block text-sm font-semibold text-gray-600">Rol/Puesto</label>
                        <input type="text" id="rol" name="rol" placeholder="Rol" value="{{ old('rol', $solicitud->rol) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-6">
                        <label for="punto" class="block text-sm font-semibold text-gray-600">Punto</label>
                        <select id="punto" name="punto" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                            <option disabled selected value="">Seleccione un subpunto</option>
                            @foreach($puntos as $punto)
                                <optgroup label="{{ $punto->nombre }}">
                                    @foreach($punto->subpuntos as $subpunto)
                                        <option value="{{ $subpunto->nombre }}"
                                            {{ old('punto', $solicitud->punto) === $subpunto->nombre ? 'selected' : '' }}>
                                            @if($subpunto->codigo != null)({{ str_pad($subpunto->codigo, 3, '0', STR_PAD_LEFT) }}) @endif {{ $subpunto->nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-6">
                        <label for="reingreso" class="block text-sm font-semibold text-gray-600">Reingreso</label>
                        <input type="text" id="reingreso" name="reingreso" placeholder="Reingreso" value="{{ old('reingreso', $solicitud->reingreso) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-6">
                        <label for="sueldo_mensual" class="block text-sm font-semibold text-gray-600">Sueldo Mensual</label>
                        <input type="text" id="sueldo_mensual" name="sueldo_mensual" placeholder="Sueldo Mensual" value="{{ old('sueldo_mensual', $solicitud->sueldo_mensual) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-6">
                        <label for="fecha_ingreso" class="block text-sm font-semibold text-gray-600">Fecha de Ingreso</label>
                        <input type="date" id="fecha_ingreso" name="fecha_ingreso" placeholder="Fecha de Ingreso" value="{{ old('fecha_ingreso', $solicitud->fecha_ingreso) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                    </div>

                    <div class="form-group mb-4">
                        <label for="email" class="block text-sm font-semibold text-gray-600">Correo Electrónico</label>
                        <input type="email" id="email" name="email" placeholder="Correo electrónico" value="{{ old('email', $solicitud->email) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md mt-2">
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group flex items-center justify-center">
                        <button type="submit" class="inline-block bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                            Guardar Cambios
                        </button>
                        <a href="{{ route('user.verFicha', $user->id) }}" class="inline-block bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400 ml-2 mr-2">
                            Cancelar
                        </a>
                    </div>
                </form>

                <p class="text-justify mt-4">Nota: Al guardar los cambios, se creará un registro de edición y el usuario será actualizado según los nuevos datos proporcionados.</p>
            </div>
        </div>
    </div>

    <script>
function cambiarTipo(tipo) {
    console.log('Cambiando tipo a:', tipo); // Para depuración

    try {
        // Actualizar los campos ocultos
        const tipoHidden = document.getElementById('tipo_hidden');
        const tipoActualInput = document.getElementById('tipo_actual');

        if (tipoHidden) {
            tipoHidden.value = tipo;
        }
        if (tipoActualInput) {
            tipoActualInput.value = tipo;
        }

        // Obtener el rol del usuario (asegúrate que esto se renderiza bien)
        const rolUsuario = '{{ Auth::user()->rol ?? "" }}';
        console.log('Rol del usuario:', rolUsuario); // Para depuración

        // Mostrar/ocultar campo departamento
        const departamentoGroup = document.getElementById('departamento_group');
        if (departamentoGroup) {
            if (rolUsuario !== 'Supervisor' && tipo === 'oficina') {
                departamentoGroup.style.display = 'block';
                console.log('Mostrando departamento'); // Para depuración
            } else {
                departamentoGroup.style.display = 'none';
                console.log('Ocultando departamento'); // Para depuración
            }
        } else {
            console.error('No se encontró el elemento departamento_group');
        }

        // Actualizar la clase visual de las pestañas
        const tabs = document.querySelectorAll('[onclick*="cambiarTipo"]');
        tabs.forEach(tab => {
            const match = tab.getAttribute('onclick').match(/'([^']+)'/);
            if (match) {
                const tabTipo = match[1];
                if (tabTipo === tipo) {
                    tab.classList.remove('border-transparent', 'text-gray-500');
                    tab.classList.add('border-blue-600', 'text-blue-600', 'font-semibold');
                } else {
                    tab.classList.remove('border-blue-600', 'text-blue-600', 'font-semibold');
                    tab.classList.add('border-transparent', 'text-gray-500');
                }
            }
        });
    } catch (error) {
        console.error('Error en cambiarTipo:', error);
    }
}

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const tipoActual = document.getElementById('tipo_actual');
    if (tipoActual) {
        console.log('Tipo actual al cargar:', tipoActual.value); // Para depuración
        cambiarTipo(tipoActual.value);
    }
});
</script>
</x-app-layout>
