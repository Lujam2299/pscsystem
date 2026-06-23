<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiemposExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'asistencia_id',
        'user_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'total_horas',
        'autorizado_por',
        'observaciones',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asistencia()
    {
        return $this->belongsTo(Asistencia::class);
    }
}
