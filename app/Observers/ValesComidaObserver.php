<?php

namespace App\Observers;

use App\Models\ValesComida;
use App\Services\RealtimeToast;

class ValesComidaObserver
{
    private const ACCOUNTING_ROLES = ['CONTADOR', 'CONTADORA'];
    private const OPERATIONS_ROLES = ['AUXILIAR OPERACIONES', 'OPERACIONES'];

    public function created(ValesComida $vale): void
    {
        $vale->loadMissing('user');

        RealtimeToast::toRoles(self::ACCOUNTING_ROLES, [
            'icon' => 'info',
            'title' => 'Nueva solicitud de vale de comida',
            'text' => ($vale->user?->name ?? 'Usuario') . ' · $' . number_format((float) $vale->monto, 2),
            'key' => 'vale-comida:' . $vale->id . ':created',
        ]);
    }

    public function updated(ValesComida $vale): void
    {
        if (!$vale->wasChanged('estatus')) {
            return;
        }

        $vale->loadMissing('user');
        $status = (string) $vale->estatus;
        $icon = str_contains(strtoupper($status), 'RECHAZ') ? 'warning' : 'success';

        RealtimeToast::toRoles(self::OPERATIONS_ROLES, [
            'icon' => $icon,
            'title' => 'Vale de comida: ' . $status,
            'text' => ($vale->user?->name ?? 'Usuario') . ' · $' . number_format((float) $vale->monto, 2),
            'key' => 'vale-comida:' . $vale->id . ':status:' . strtoupper($status) . ':' . $vale->updated_at?->timestamp,
        ]);
    }
}
