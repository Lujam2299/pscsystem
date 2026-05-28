<?php

namespace App\Models;

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

    public function getNombresAgentesAttribute(): string
    {
        if (!$this->agentes_id) {
            return '';
        }

        $ids = is_string($this->agentes_id)
            ? json_decode($this->agentes_id, true)
            : $this->agentes_id;

        if (!is_array($ids) || empty($ids)) {
            return '';
        }

        return \App\Models\User::whereIn('id', $ids)
            ->pluck('name')
            ->implode(', ');
    }

    /**
     * Accessor para verificar si la misión tiene gastos registrados
     * dentro de su rango de fechas y con sus agentes asignados
     */
    public function getHasGastosAttribute(): bool
    {
        // Obtener IDs de agentes asignados
        $agentesIds = $this->agentes_id;

        // Si es string (JSON), decodificar
        if (is_string($agentesIds)) {
            $agentesIds = json_decode($agentesIds, true);
        }

        // Validar que sea un array con datos
        if (!is_array($agentesIds) || empty($agentesIds)) {
            return false;
        }

        // Verificar existencia de gastos en el rango de fechas
        return \App\Models\Gasto::whereIn('user_id', $agentesIds)
            ->whereBetween('Fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->exists();
    }

    /**
     * Accessor para obtener los gastos de la misión
     * (útil para reutilizar la consulta en controller y vistas)
     */
    public function getGastosDelPeriodoAttribute()
    {
        $agentesIds = $this->agentes_id;

        if (is_string($agentesIds)) {
            $agentesIds = json_decode($agentesIds, true);
        }

        if (!is_array($agentesIds) || empty($agentesIds)) {
            return collect([]);
        }

        return \App\Models\Gasto::whereIn('user_id', $agentesIds)
            ->whereBetween('Fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->orderBy('Fecha', 'asc')
            ->orderBy('Hora', 'asc')
            ->get();
    }
}
