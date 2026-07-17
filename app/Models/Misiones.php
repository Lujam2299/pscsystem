<?php

namespace App\Models;

use App\Support\Custodios\MissionStatus;
use Illuminate\Database\Eloquent\Model;

class Misiones extends Model
{
    protected $fillable = [
        'agentes_id',
        'nivel_amenaza',
        'tipo_servicio',
        'nombre_clave',
        'ubicacion',
        'armados',
        'fecha_inicio',
        'fecha_fin',
        'cliente',
        'pasajeros',
        'tipo_operacion',
        'num_vehiculos',
        'tipo_vehiculos',
        'arch_mision',
        'datos_hotel',
        'datos_aeropuerto',
        'datos_vuelo',
        'datos_hospital',
        'datos_embajada',
        'lat',
        'lng',
        'estatus',
        'revision_estado',
        'revision_observaciones',
        'revision_user_id',
        'revision_at',
    ];

    protected $casts = [
        'ubicacion' => 'array',
        'agentes_id' => 'array',
        'tipo_vehiculos' => 'array',
        'datos_hotel' => 'array',
        'datos_aeropuerto' => 'array',
        'datos_vuelo' => 'array',
        'datos_hospital' => 'array',
        'datos_embajada' => 'array',
        'revision_at' => 'datetime',
    ];

    public function geofences()
    {
        return $this->hasMany(Geofence::class, 'mision_id');
    }

    public function gastos()
    {
        return $this->hasMany(Gastos::class, 'mision_id');
    }

    public function cierresOperativos()
    {
        return $this->hasMany(MisionCierreOperativo::class, 'mision_id');
    }

    public function revisionUser()
    {
        return $this->belongsTo(User::class, 'revision_user_id');
    }

    public function getRevisionEstadoNormalizadoAttribute(): string
    {
        $estado = trim((string) ($this->revision_estado ?: 'Pendiente de revisión'));

        return match ($estado) {
            'En revisión', 'Lista para facturar', 'Observada / requiere corrección' => $estado,
            default => 'Pendiente de revisión',
        };
    }

    public function getRevisionToneAttribute(): string
    {
        return match ($this->revision_estado_normalizado) {
            'Lista para facturar' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
            'En revisión' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
            'Observada / requiere corrección' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        };
    }

    public function getEstadoNormalizadoAttribute(): string
    {
        return MissionStatus::normalize($this->estatus);
    }

    /** @return array<int, string> */
    public function getTransicionesEstadoAttribute(): array
    {
        return MissionStatus::transitionsFrom($this->estatus);
    }

    public function getNombresAgentesAttribute(): string
    {
        $ids = $this->agentesIdsNormalizados();

        if (empty($ids)) {
            return '';
        }

        return \App\Models\User::whereIn('id', $ids)
            ->pluck('name')
            ->implode(', ');
    }

    /**
     * Obtiene los IDs de agentes como enteros y soporta registros históricos
     * cuyo contenido JSON quedó codificado más de una vez.
     *
     * @return array<int, int>
     */
    public function agentesIdsNormalizados(): array
    {
        $ids = $this->agentes_id;

        for ($intento = 0; $intento < 2 && is_string($ids); $intento++) {
            $decodificado = json_decode($ids, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }

            $ids = $decodificado;
        }

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_map(
            'intval',
            array_filter($ids, fn ($id) => is_numeric($id) && (int) $id > 0)
        )));
    }

    /**
     * Accessor para verificar si la misión tiene gastos registrados
     * dentro de su rango de fechas y con sus agentes asignados
     */
    public function getHasGastosAttribute(): bool
    {
        $agentesIds = $this->agentesIdsNormalizados();

        // Validar que sea un array con datos
        if (! is_array($agentesIds) || empty($agentesIds) || ! $this->fecha_inicio || ! $this->fecha_fin) {
            return false;
        }

        // Verificar existencia de gastos en el rango de fechas
        return Gastos::query()->forMission($this)->exists();
    }

    public function getHasCierresOperativosAttribute(): bool
    {
        return $this->cierresOperativos()->exists();
    }

    /**
     * Accessor para obtener los gastos de la misión
     * (útil para reutilizar la consulta en controller y vistas)
     */
    public function getGastosDelPeriodoAttribute()
    {
        $agentesIds = $this->agentesIdsNormalizados();

        if (! is_array($agentesIds) || empty($agentesIds) || ! $this->fecha_inicio || ! $this->fecha_fin) {
            return collect([]);
        }

        return Gastos::query()->forMission($this)
            ->orderBy('Fecha', 'asc')
            ->orderBy('Hora', 'asc')
            ->get();
    }
}
