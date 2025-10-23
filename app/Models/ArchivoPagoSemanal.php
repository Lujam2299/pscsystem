<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoPagoSemanal extends Model
{
    protected $table = 'archivos_pagos_semanals';
    protected $fillable = [
        'mes',
        'semana',
        'anio',
        'archivo_semanal',
        'total_semanal',
    ];

    protected $casts = [
        'mes' => 'integer',
        'semana' => 'integer',
        'total_semanal' => 'float',
    ];
}
