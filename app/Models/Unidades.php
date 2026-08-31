<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unidades extends Model
{
    protected $fillable = [
        'id',
        'nombre_propietario',
        'zona',
        'marca',
        'modelo',
        'placas',
        'kms',
        'asignacion_punto',
        'estado_vehiculo',
        'observaciones',
    ];

    // Relación: una unidad tiene muchas placas
    public function placas(): HasMany
    {
        return $this->hasMany(Placa::class, 'unidad_id');
    }

    // Placa vigente (la más reciente y activa)
    public function placa(): HasOne
    {
        return $this->hasOne(Placa::class, 'unidad_id')->whereNull('fecha_baja')->latest('fecha_asignacion');
    }

    public function inspecciones(): HasMany
    {
        return $this->hasMany(InspeccionUnidad::class, 'unidad_id');
    }

    // Accesor para obtener si la unidad está activa
    public function getIsActivoAttribute()
    {
        $valor = strtolower(trim($this->estado_vehiculo));
        $activos = ['activo', 'activa', 'en servicio', 'en uso', 'disponible', 'operando', 'operativa'];

        return in_array($valor, $activos);
    }
}
