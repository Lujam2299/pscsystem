<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Geofence extends Model
{
    use HasFactory;

    protected $fillable = [
        'mision_id',
        'tipo',
        'centro',
        'radio_km',
        'nombre_referencia',
    ];

    protected $casts = [
        'centro' => 'array',
    ];

    public function mision()
    {
        return $this->belongsTo(Misiones::class, 'mision_id');
    }
}
