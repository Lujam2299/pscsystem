<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Sueldo extends Model
{
    use HasFactory;

    protected $fillable = [
        'punto',
        'puesto',
        'sd',
        'sdi',
        'compensacion',
        'nomina_quincenal',
        'sueldo_quincenal',
        'sueldo_mensual',
        'vigente_desde',
        'vigente_hasta',
    ];

    protected $casts = [
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];
}
