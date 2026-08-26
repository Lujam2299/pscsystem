<x-app-layout>
    <x-navbar />
    @if (\App\Support\Authorization\RoleNormalizer::isAdministrator(Auth::user()))
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
                    @if (! \App\Support\Authorization\RoleNormalizer::isAdministrator(Auth::user()))
                    <div class="">
                        @php
                        $user = Auth::user();
                        $roleCategory = \App\Support\Authorization\RoleNormalizer::for($user);
                        $erpSupervisorDisabled = config('modules.disabled.erp_supervisores', false);
                        $erpCustodiosDisabled = config('modules.disabled.erp_custodios', false);

                        $navbar = match ($roleCategory) {
                            \App\Support\Authorization\RoleNormalizer::ACCOUNTING => 'auxcont-navbar',
                            \App\Support\Authorization\RoleNormalizer::PAYROLL => 'nominas-navbar',
                            \App\Support\Authorization\RoleNormalizer::HUMAN_RESOURCES => 'rh-navbar',
                            \App\Support\Authorization\RoleNormalizer::OPERATIONS => 'operaciones-navbar',
                            \App\Support\Authorization\RoleNormalizer::IMSS => 'auxadmin-navbar',
                            \App\Support\Authorization\RoleNormalizer::MONITORING => 'monitoreo-navbar',
                            \App\Support\Authorization\RoleNormalizer::LEGAL => 'juridico-navbar',
                            \App\Support\Authorization\RoleNormalizer::SUPERVISOR => $erpSupervisorDisabled ? null : 'supervisor-navbar',
                            \App\Support\Authorization\RoleNormalizer::CUSTODIAN => $erpCustodiosDisabled ? null : 'custodios-navbar',
                            default => null,
                        };

                        $moduloOperativoDeshabilitado =
                            ($erpSupervisorDisabled && $roleCategory === \App\Support\Authorization\RoleNormalizer::SUPERVISOR)
                            || ($erpCustodiosDisabled && $roleCategory === \App\Support\Authorization\RoleNormalizer::CUSTODIAN);
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
