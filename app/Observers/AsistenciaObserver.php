<?php

namespace App\Observers;

use App\Models\Asistencia;
use App\Services\RealtimeToast;

class AsistenciaObserver
{
    private const ROLES = ['AUXILIAR NOMINAS', 'JEFA NOMINAS'];

    public function created(Asistencia $asistencia): void
    {
        $this->notificar($asistencia, 'Nuevo registro de asistencia', 'Se ha recibido un registro de asistencia del punto ');
    }

    public function updated(Asistencia $asistencia): void
    {
        $this->notificar($asistencia, 'Asistencia actualizada', 'Se actualizó el registro de asistencia del punto ');
    }

    private function notificar(Asistencia $asistencia, string $titulo, string $mensaje): void
    {
        $asistencia->loadMissing('usuario');
        $punto = $asistencia->punto
            ?: $asistencia->usuario?->punto
            ?: 'sin punto especificado';

        RealtimeToast::toRoles(self::ROLES, [
            'icon' => 'success',
            'title' => $titulo,
            'text' => $mensaje.$punto,
            'key' => 'asistencia:'.$asistencia->id.':'.$asistencia->updated_at?->getTimestamp(),
        ]);
    }
}
