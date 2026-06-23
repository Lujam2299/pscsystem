<?php

namespace App\Observers;

use App\Models\SolicitudBajas;
use App\Services\RealtimeToast;

class SolicitudBajasObserver
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

    public function updated(SolicitudBajas $solicitud): void
    {
        $solicitud->loadMissing('user');
        $userName = $solicitud->user?->name ?? 'Usuario';
        $timestamp = $solicitud->updated_at?->timestamp;

        if ($solicitud->wasChanged('calculo_finiquito') && $solicitud->calculo_finiquito) {
            RealtimeToast::toRoles(self::ROLES, [
                'icon' => 'success',
                'title' => 'Finiquito enviado a revisión',
                'text' => $userName,
                'key' => 'solicitud-baja:' . $solicitud->id . ':finiquito:' . $timestamp,
            ]);
        }

        if ($solicitud->wasChanged('arch_cheque') && $solicitud->arch_cheque) {
            $cancelled = str_contains(strtoupper((string) $solicitud->observaciones), 'CANCELADO');
            RealtimeToast::toRoles(self::ROLES, [
                'icon' => $cancelled ? 'warning' : 'success',
                'title' => $cancelled ? 'Cheque de finiquito cancelado' : 'Cheque de finiquito disponible',
                'text' => $userName,
                'key' => 'solicitud-baja:' . $solicitud->id . ':cheque:' . $timestamp,
            ]);
        }
    }
}
