<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finiquito extends Model
{
    protected $fillable = [
        'baja_id',
        'monto',
        'salario_diario',
        'desglose',
        'version_formula',
        'calculado_por',
        'calculado_en',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'salario_diario' => 'decimal:2',
        'desglose' => 'array',
        'calculado_en' => 'datetime',
    ];

    public function baja()
{
    return $this->belongsTo(\App\Models\SolicitudBajas::class, 'baja_id');
}

    public function calculadoPor()
    {
        return $this->belongsTo(User::class, 'calculado_por');
    }
}
