<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaltaJustificada extends Model
{
    protected $fillable = [
        'asistencia_id',
        'user_id',
        'fecha',
        'tipo',
        'motivo',
        'archivo_justificante',
        'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function asistencia()
    {
        return $this->belongsTo(Asistencia::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
