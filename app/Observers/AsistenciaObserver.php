<?php

namespace App\Observers;

use App\Models\Asistencia;
use App\Services\RealtimeToast;

class AsistenciaObserver
{
    private const ROLES = ['AUXILIAR NOMINAS', 'JEFA NOMINAS'];

    public function created(Asistencia $asistencia): void
    {
        $asistencia->loadMissing('usuario');
        $punto = $asistencia->punto
            ?: $asistencia->usuario?->punto
            ?: 'sin punto especificado';

        RealtimeToast::toRoles(self::ROLES, [
            'icon' => 'success',
            'title' => 'Nuevo registro de asistencia',
            'text' => 'Se ha recibido un registro de asistencia del punto ' . $punto,
            'key' => 'asistencia:' . $asistencia->id,
        ]);
    }
}
