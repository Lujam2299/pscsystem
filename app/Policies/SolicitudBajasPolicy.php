<?php

namespace App\Policies;

use App\Models\SolicitudBajas;
use App\Models\User;

class SolicitudBajasPolicy
{
    private const FINIQUITO_ROLES = [
        'ADMIN',
        'ADMINISTRADOR',
        'AUXILIAR NOMINAS',
        'NOMINAS',
        'AUXILIAR RECURSOS HUMANOS',
        'AUXILIAR RH',
        'RECURSOS HUMANOS',
        'JEFA RECURSOS HUMANOS',
        'AUXILIAR CONTABILIDAD',
        'CONTADOR',
        'CONTADORA',
    ];

    public function viewFiniquitos(User $user): bool
    {
        $roles = [
            $user->rol,
            $user->solicitudAlta?->rol,
        ];

        if (strcasecmp((string) $user->solicitudAlta?->departamento, 'Recursos Humanos') === 0) {
            $roles[] = 'RECURSOS HUMANOS';
        }

        foreach ($roles as $role) {
            if (in_array(strtoupper(trim((string) $role)), self::FINIQUITO_ROLES, true)) {
                return true;
            }
        }

        return false;
    }

    public function processFiniquito(User $user, SolicitudBajas $baja): bool
    {
        return $this->viewFiniquitos($user)
            && $baja->estatus === 'Aceptada'
            && $baja->por === 'Renuncia';
    }
}
