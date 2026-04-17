<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IsrTarifa extends Model
{
    protected $fillable = [
        'anio',
        'limite_inferior',
        'limite_superior',
        'cuota_fija',
        'porcentaje_excedente'
    ];

    protected $casts = [
        'limite_inferior' => 'float',
        'limite_superior' => 'float',
        'cuota_fija' => 'float',
        'porcentaje_excedente' => 'float',
    ];
}
