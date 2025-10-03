<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudVacaciones extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'periodo',
        'dias_por_derecho',
        'fecha_inicio',
        'fecha_fin',
        'monto',
        'observaciones',
        'tipo',
        'autorizado_por',
        'fecha_autorizacion',
        'dias_ya_utilizados',
        'dias_disponibles',
        'dias_solicitados',
        'codigo_empleado',
        'fecha_solicitud',
        'estatus',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
