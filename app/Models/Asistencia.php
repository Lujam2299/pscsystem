<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fecha',
        'hora_asistencia',
        'elementos_enlistados',
        'descansos',
        'faltas',
        'turnos',
        'fotos_asistentes',
        'observaciones',
        'coberturas',
        'punto',
        'empresa',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function puntosAsignados()
    {
        return $this->hasMany(\App\Models\AsistenciaPunto::class);
    }

    public function tiemposExtra()
    {
        return $this->hasMany(TiemposExtra::class);
    }
}
