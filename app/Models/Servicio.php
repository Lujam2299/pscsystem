<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    protected $fillable = [
        'unidad_id',
        'fecha',
        'descripcion',
        'costo',
        'responsable',
        'tipo',
        'siniestro_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidades::class, 'unidad_id');
    }

    public function inspeccionesOrigen(): HasMany
    {
        return $this->hasMany(InspeccionUnidad::class, 'servicio_id');
    }
    // Relación con siniestro se agregará posteriormente
}
