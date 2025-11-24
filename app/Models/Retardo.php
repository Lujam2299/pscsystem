<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retardo extends Model
{
    protected $fillable = [
        'user_id',
        'asistencia_id',
        'fecha',
        'minutos_retardo',
        'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'minutos_retardo' => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asistencia()
    {
        return $this->belongsTo(Asistencia::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
