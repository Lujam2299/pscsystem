<?php

namespace App\Observers;

use App\Models\Incapacidad;
use App\Services\RealtimeToast;

class IncapacidadObserver
{
    private const ROLES = ['AUXILIAR NOMINAS', 'JEFA NOMINAS'];

    public function created(Incapacidad $incapacidad): void
    {
        $incapacidad->loadMissing('user');

        RealtimeToast::toRoles(self::ROLES, [
            'icon' => 'warning',
            'title' => 'Nueva incapacidad registrada',
            'text' => ($incapacidad->user?->name ?? 'Usuario') . ' · Folio ' . ($incapacidad->folio ?? 'sin folio'),
            'key' => 'incapacidad:' . $incapacidad->id,
        ]);
    }
}
