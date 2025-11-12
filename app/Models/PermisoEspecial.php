<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoEspecial extends Model
{
    protected $fillable = [
        'user_id',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'con_goce',
        'motivo',
        'archivo_justificante',
        'registrado_por',
        'estatus', // 'Pendiente', 'Aprobado', 'Rechazado'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'con_goce' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
