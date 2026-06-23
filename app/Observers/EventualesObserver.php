<?php

namespace App\Observers;

use App\Models\Eventuales;
use App\Services\RealtimeToast;

class EventualesObserver
{
    private const ROLES = ['CONTADOR', 'CONTADORA'];

    public function created(Eventuales $eventual): void
    {
        $eventual->loadMissing(['user', 'subpunto']);
        $subpunto = $eventual->subpunto?->nombre ?? 'sin subpunto';

        RealtimeToast::toRoles(self::ROLES, [
            'icon' => 'info',
            'title' => 'Nuevo registro eventual',
            'text' => ($eventual->user?->name ?? 'Usuario') . ' · ' . $subpunto,
            'key' => 'eventual:' . $eventual->id . ':created',
        ]);
    }

    public function updated(Eventuales $eventual): void
    {
        if (!$eventual->wasChanged('arch_pago') || !$eventual->arch_pago) {
            return;
        }

        $eventual->loadMissing('user');

        RealtimeToast::toRoles(self::ROLES, [
            'icon' => 'success',
            'title' => 'Comprobante de pago eventual disponible',
            'text' => $eventual->user?->name ?? 'Usuario',
            'key' => 'eventual:' . $eventual->id . ':payment:' . $eventual->updated_at?->timestamp,
        ]);
    }
}
