<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Eventuales extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fecha',
        'subpunto_id',
        'turnos',
        'tipo_pago',
        'arch_pago',
        'tipo_servicio',
        'elemento_relacionado_id',
        'motivo'
    ];

    protected $casts = [
        'turnos' => 'array',
        'fecha' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function elementoRelacionado()
    {
        return $this->belongsTo(User::class, 'elemento_relacionado_id');
    }

    public function subpunto()
    {
        return $this->belongsTo(Subpunto::class);
    }
}
