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
    ];

    protected $casts = [
        'turnos' => 'array',
        'fecha' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function punto()
    {
        return $this->belongsTo(Punto::class);
    }
}
