<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SolicitudBajas;

class BajaAcuse extends Model
{
    protected $fillable = ['solicitud_baja_id', 'archivo'];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudBajas::class, 'solicitud_baja_id');
    }

    public function solicitudBaja()
    {
        return $this->belongsTo(SolicitudBajas::class, 'solicitud_baja_id');
    }
}
