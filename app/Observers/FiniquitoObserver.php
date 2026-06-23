<?php

namespace App\Observers;

use App\Models\Finiquito;
use App\Services\RealtimeToast;

class FiniquitoObserver
{
    private const ROLES = [
        'AUXILIAR RECURSOS HUMANOS',
        'AUXILIAR RH',
        'RECURSOS HUMANOS',
        'JEFA RECURSOS HUMANOS',
        'AUXILIAR CONTABILIDAD',
        'CONTADOR',
        'CONTADORA',
    ];

    public function created(Finiquito $finiquito): void
    {
        $finiquito->loadMissing('baja.user');

        RealtimeToast::toRoles(self::ROLES, [
            'icon' => 'success',
            'title' => 'Cálculo de finiquito generado',
            'text' => ($finiquito->baja?->user?->name ?? 'Usuario')
                . ' · $' . number_format((float) $finiquito->monto, 2),
            'key' => 'finiquito:' . $finiquito->id . ':created',
        ]);
    }
}
