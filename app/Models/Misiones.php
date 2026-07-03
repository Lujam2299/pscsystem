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
    ];

    public function geofences()
    {
        return $this->hasMany(Geofence::class, 'mision_id');
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
        return Gastos::whereIn('user_id', $agentesIds)
            ->whereBetween('Fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->exists();
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

        return Gastos::whereIn('user_id', $agentesIds)
            ->whereBetween('Fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->orderBy('Fecha', 'asc')
            ->orderBy('Hora', 'asc')
            ->get();
    }
}
