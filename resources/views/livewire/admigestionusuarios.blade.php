@php
    $currentPage = $users->currentPage();
    $lastPage = $users->lastPage();
@endphp

@php
    function calcularProgresoDocumentos($tipoEmpleado, $documentacion) {
        $documentosBase = [
            'arch_ine', 'arch_solicitud_empleo', 'arch_curp', 'arch_rfc', 'arch_nss',
            'arch_acta_nacimiento', 'arch_comprobante_estudios', 'arch_comprobante_domicilio',
            'arch_carta_rec_laboral', 'arch_carta_rec_personal','arch_contrato', 'arch_foto'
        ];

        $documentosExtraArmado = [
            'arch_cartilla_militar', 'arch_carta_no_penales', 'arch_antidoping',
        ];

        if ($tipoEmpleado === 'armado') {
            $documentos = array_merge($documentosBase, $documentosExtraArmado);
        } else {
            $documentos = $documentosBase;
        }

        $completados = 0;
        foreach ($documentos as $campo) {
            if (!empty($documentacion?->$campo)) {
                $completados++;
            }
        }

        $total = count($documentos);
        return $total > 0 ? round(($completados / $total) * 100) : 0;
    }

    function claseBarraProgreso($porcentaje) {
        return match(true) {
            $porcentaje >= 75 => 'bg-blue-600',
            $porcentaje >= 50 => 'bg-yellow-500',
            default => 'bg-red-500',
        };
    }
@endphp


<div>
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <!-- Filtro de búsqueda -->
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por nombre..."
                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
            >
            <div wire:loading class="text-sm text-gray-500 mt-1">
                Buscando...
            </div>
        </div>

        <!-- Filtro de tipo de pago -->
        <div class="w-full sm:w-48">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Pago</label>
            <select wire:model.live="tipo_pago" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="todos">Todos</option>
                <option value="semanal">Semanal</option>
                <option value="quincenal">Quincenal</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        No.
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Núm. Empleado
                    </th>
                    <th scope="col" class="cursor-pointer px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        wire:click="sortBy('name')">
                        Nombre
                        @if ($sortField === 'name')
                            @if ($sortDirection === 'asc') ▲ @else ▼ @endif
                        @endif
                    </th>
                    <th scope="col" class="cursor-pointer px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        wire:click="sortBy('punto')">
                        Punto
                        @if ($sortField === 'punto')
                            @if ($sortDirection === 'asc') ▲ @else ▼ @endif
                        @endif
                    </th>
                    <th scope="col" class="cursor-pointer px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        wire:click="sortBy('rol')">
                        Rol
                        @if ($sortField === 'rol')
                            @if ($sortDirection === 'asc') ▲ @else ▼ @endif
                        @endif
                    </th>
                    <th scope="col" class="cursor-pointer px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        wire:click="sortBy('tipo_periodo')">
                        Tipo de Periodo
                        @if ($sortField === 'tipo_periodo')
                            @if ($sortDirection === 'asc') ▲ @else ▼ @endif
                        @endif
                    </th>
                    <th scope="col" class="cursor-pointer px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        wire:click="sortBy('progreso_documentos')">
                        Progreso Docs.
                        @if ($sortField === 'progreso_documentos')
                            @if ($sortDirection === 'asc') ▲ @else ▼ @endif
                        @endif
                    </th>

                    <th scope="col" class="cursor-pointer px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        wire:click="sortBy('estatus')">
                        Estatus
                        @if ($sortField === 'estatus')
                            @if ($sortDirection === 'asc') ▲ @else ▼ @endif
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                        {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                            {{ $user->num_empleado }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                            {{ $user->name }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                        {{ $user->punto ?? 'No Disponible' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                        @if($user->rol == 'admin')
                            Administrador
                        @else
                            {{ $user->rol }}
                        @endif
                        </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                            {{ $user->solicitudAlta->tipo_periodo ?? 'No Disponible' }}
                        </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                        @php
                            $tipoEmpleado = $user->solicitudAlta?->tipo_empleado;
                            $documentacion = $user->solicitudAlta?->documentacion;
                            $porcentaje = calcularProgresoDocumentos($tipoEmpleado, $documentacion);
                            $colorBarra = claseBarraProgreso($porcentaje);
                        @endphp
                        @if($user->estatus == 'Activo')
                        <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                            <div class="{{ $colorBarra }} h-4 rounded-full transition-all duration-500" style="width: {{ $porcentaje }}%"></div>
                        </div>
                        <div class="text-xs mt-1 text-gray-600 dark:text-gray-400">{{ $porcentaje }}%</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                        @if($user->estatus == 'Activo')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                {{ $user->estatus }}
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                {{ $user->estatus }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{route('user.verFicha', $user->id)}}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-500 mr-3">Ver Ficha</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <ul class="flex justify-center space-x-2">
            @if ($users->onFirstPage())
                <li class="px-3 py-1 text-gray-500" aria-disabled="true">&laquo;</li>
            @else
                <li>
                    <button wire:click="previousPage" class="px-3 py-1 text-blue-600 hover:text-blue-800">&laquo;</button>
                </li>
            @endif

            @if ($currentPage > 2)
                <li>
                    <button wire:click="gotoPage(1)" class="px-3 py-1 text-blue-600 hover:text-blue-800">1</button>
                </li>
                @if ($currentPage > 3)
                    <li class="px-3 py-1 text-gray-500">...</li>
                @endif
            @endif

            @for ($i = max(1, $currentPage - 1); $i <= min($lastPage, $currentPage + 1); $i++)
                <li>
                    @if ($i == $currentPage)
                        <span class="px-3 py-1 bg-blue-500 text-white rounded">{{ $i }}</span>
                    @else
                        <button wire:click="gotoPage({{ $i }})" class="px-3 py-1 text-blue-600 hover:text-blue-800">{{ $i }}</button>
                    @endif
                </li>
            @endfor

            @if ($currentPage < $lastPage - 1)
                @if ($currentPage < $lastPage - 2)
                    <li class="px-3 py-1 text-gray-500">...</li>
                @endif
                <li>
                    <button wire:click="gotoPage({{ $lastPage }})" class="px-3 py-1 text-blue-600 hover:text-blue-800">{{ $lastPage }}</button>
                </li>
            @endif

            @if ($users->hasMorePages())
                <li>
                    <button wire:click="nextPage" class="px-3 py-1 text-blue-600 hover:text-blue-800">&raquo;</button>
                </li>
            @else
                <li class="px-3 py-1 text-gray-500" aria-disabled="true">&raquo;</li>
            @endif
        </ul>

    </div><br>
        <center>
            @if(Auth::user()->rol == 'admin')
            <a href="{{ route('rh.generarNuevaAltaForm') }}" class="inline-block bg-blue-300 text-gray-800 py-2 px-4 rounded-md hover:bg-blue-400 mr-2">
                Nuevo Usuario
            </a>
            @endif
            <a href="{{ route('dashboard') }}" class="inline-block bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400 mr-2">
                Regresar
            </a></center>
</div>
