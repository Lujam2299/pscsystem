<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiempoExtraCosto extends Model
{
    protected $fillable = ['zona', 'costo_12_horas'];

    protected $table = 'tiempos_extra_costos';

    // No timestamps
    public $timestamps = false;
}
