<x-app-layout>
    <x-navbar />
    @if (Auth::user()->rol == 'admin')
    @if (session('success'))
    <div class="px-4 py-3 text-green-900 bg-green-100 border-t-4 border-green-500 rounded-b shadow-md" role="alert">
        <div class="flex">
            <div>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @else
    @endif
    <div>
        <x-admin-navbar></x-admin-navbar>
    </div>
    @else
    <div class="px-2 py-4 sm:py-6 sm:px-4">
        <div class="mx-auto max-w-7xl">
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div x-data="{ menu: 'admin' }" x-on:cambiar-menu.window="menu = $event.detail.menu" class="space-y-4">
                    @if (session('success'))
                        <div class="px-4 py-3 text-green-900 bg-green-100 border-t-4 border-green-500 rounded-b shadow-md"
                            role="alert">
                            <div class="flex">
                                <div>
                                    <p class="text-sm">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        @if(session('error'))
                            <div class="px-4 py-3 text-red-900 bg-red-100 border-t-4 border-red-500 rounded-b shadow-md"
                                role="alert">
                                <div class="flex">
                                    <div>
                                        <p class="text-sm">{{ session('error') }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                        @endif
                    @endif
                    @if (Auth::user()->rol != 'admin')
                    <p class="text-2xl text-gray-900 dark:text-gray-100">
                        Tablero de Opciones
                    </p>
                    <div class="">
                        @php
                        $user = Auth::user();
                        $rol = strtolower($user->rol ?? '');
                        $solicitud = $user->solicitudAlta ?? null;
                        $solicitudRol = strtolower($solicitud->rol ?? '');
                        $solicitudDepartamento = strtolower($solicitud->departamento ?? '');
                        $navbar = null;
                        $erpSupervisorDisabled = config('modules.disabled.erp_supervisores', false);
                        $erpCustodiosDisabled = config('modules.disabled.erp_custodios', false);

                        // NORMALIZACIÓN DE ROLES
                        // Algunos roles pueden tener prefijos como "auxiliar" o "aux", que para motivos del panel
                        // que se mostrará no afectan, por lo que podemos eliminarlos.
                        if (strpos($rol, 'auxiliar') !== false || strpos($rol, 'aux') !== false) {
                        $rol = str_replace(['auxiliar', 'aux'], '', $rol);
                        }
                        // Eliminamos caracteres especiales, excepto espacios.
                        $rol = trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $rol));

                        // Verificamos específicamente el rol "Auxiliar contabilidad"
                        $rolOriginal = strtolower(Auth::user()->rol ?? '');
                        if (strpos($rolOriginal, 'auxiliar contabilidad') !== false || strpos($rolOriginal, 'contadora') !== false) {
                            $navbar = 'auxcont-navbar';
                        }
                        // Considerando que los roles pueden ser muy variables, buscaremos cadenas básicas
                        // Roles como: supervisor, supervisora, supervisión, supervision deben ser
                        // considerados como iguales.
                        if (!$erpSupervisorDisabled && strpos($rol, 'supervis') !== false) {
                        $navbar = 'supervisor-navbar';
                        }

                        if (strpos($rol, 'operaciones') !== false) {
                        $navbar = 'operaciones-navbar';
                        }

                        // Roles como: nominas, nómina, nomina deben ser considerados como iguales.
                        if (strpos($rol, 'nomina') !== false || strpos($rol, 'nómina') !== false) {
                        $navbar = 'nominas-navbar';
                        }
                        // Roles como: recursos humanos o rh se consideran iguales.
                        if (strpos($rol, 'recursos humanos') !== false || strpos($rol, 'rh') !== false) {
                        $navbar = 'rh-navbar';
                        }
                        // Roles como que incluyen la cadena "monitor" se consideran iguales.
                        // P.e. monitorista, monitor, monitoreo.
                        if (strpos($rol, 'monitor') !== false) {
                        $navbar = 'monitoreo-navbar';
                        }
                        // Roles que incluyen la cadena "admin" se consideran iguales.
                        // P.e. auxiliar administrativo, administrativo.
                        if (strpos($rol, 'admin') !== false) {
                        $navbar = 'auxadmin-navbar';
                        }
                        // Los roles sin variaciones tienen su propia navbar.
                        $roles = [
                        'juridico' => 'juridico-navbar',
                        ];
                        if (!$erpCustodiosDisabled) {
                        $roles['custodios'] = 'custodios-navbar';
                        $roles['custodio'] = 'custodios-navbar';
                        }
                        // Si el rol del usuario está en la lista de roles, asignamos la navbar correspondiente.
                        if (array_key_exists($rol, $roles)) {
                        $navbar = $roles[$rol];
                        }
                        // Si la navbar aún no se ha asignado, verificamos el departamento de la solicitud.
                        $departamentos = [
                        'recursos humanos' => 'rh-navbar',
                        'monitoreo' => 'monitoreo-navbar',
                        ];
                        if (!$navbar && $solicitudDepartamento) {
                        // Normalizamos el departamento a minúsculas.
                        $solicitudDepartamento = strtolower($solicitudDepartamento);
                        // Verificamos si el departamento está en la lista de departamentos conocidos.
                        if (array_key_exists($solicitudDepartamento, $departamentos)) {
                        $navbar = $departamentos[$solicitudDepartamento];
                        }
                        }

                        $moduloOperativoDeshabilitado =
                        ($erpSupervisorDisabled && strpos($rol, 'supervis') !== false)
                        || ($erpCustodiosDisabled && in_array($rol, ['custodios', 'custodio'], true));
                        @endphp

                        @if ($moduloOperativoDeshabilitado)
                        <div class="p-5 border border-amber-200 rounded-lg bg-amber-50 text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-100">
                            <p class="font-semibold">Módulo deshabilitado</p>
                            <p class="mt-1 text-sm">
                                Este módulo fue deshabilitado por indicación operativa. La información histórica se conserva, pero el acceso desde el ERP ya no está disponible.
                            </p>
                        </div>
                        @elseif ($navbar)
                        @component('components.' . $navbar)
                        @endcomponent
                        @else
                        <x-user-navbar></x-user-navbar>
                        @endif
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
    @endif
</x-app-layout>
<style>
    button,
    [type="button"],
    [type="submit"] {
        touch-action: manipulation;
        min-height: 44px;
        min-width: 44px;
    }

    @media (prefers-color-scheme: dark) {
        .bg-gray-50 {
            background-color: #1a202c;
        }
    }
</style>
