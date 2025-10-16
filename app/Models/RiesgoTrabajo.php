<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class RiesgoTrabajo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tipo_riesgo',
        'descripcion_observaciones',
        'ruta_archivo_pdf',
        'arch_alta',
        'fecha',
        'folio',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Define la relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
