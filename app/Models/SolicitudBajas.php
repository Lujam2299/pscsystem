<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BajaAcuse;
class SolicitudBajas extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'fecha_solicitud',
        'motivo',
        'por',
        'incapacidad',
        'descuento',
        'ultima_asistencia',
        'calculo_finiquito',
        'arch_renuncia',
        'arch_equipo_entregado',
        'archivo_baja',
        'arch_cheque',
        'estatus',
        'fecha_baja',
        'observaciones',
        'autoriza',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function acuse()
    {
        return $this->hasOne(BajaAcuse::class, 'solicitud_baja_id');
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function finiquito()
    {
        return $this->hasOne(Finiquito::class, 'baja_id');
    }


}
