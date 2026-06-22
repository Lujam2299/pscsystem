<?php

namespace App\Observers;

use App\Models\RiesgoTrabajo;
use App\Services\RealtimeToast;

class RiesgoTrabajoObserver
{
    private const ROLES = ['AUXILIAR NOMINAS', 'JEFA NOMINAS'];

    public function created(RiesgoTrabajo $riesgo): void
    {
        $this->notify($riesgo, 'Nuevo riesgo de trabajo registrado', 'created');
    }

    public function updated(RiesgoTrabajo $riesgo): void
    {
        if (!$riesgo->wasChanged([
            'tipo_riesgo',
            'descripcion_observaciones',
            'ruta_archivo_pdf',
            'arch_alta',
            'fecha',
            'folio',
        ])) {
            return;
        }

        $this->notify($riesgo, 'Riesgo de trabajo actualizado', 'updated:' . $riesgo->updated_at?->timestamp);
    }

    private function notify(RiesgoTrabajo $riesgo, string $title, string $suffix): void
    {
        $riesgo->loadMissing('user');

        RealtimeToast::toRoles(self::ROLES, [
            'icon' => 'warning',
            'title' => $title,
            'text' => ($riesgo->user?->name ?? 'Usuario') . ' · ' . ($riesgo->tipo_riesgo ?? 'Sin tipo'),
            'key' => 'riesgo-trabajo:' . $riesgo->id . ':' . $suffix,
        ]);
    }
}
