<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaPunto extends Model
{
    protected $fillable = [
        'asistencia_id',
        'user_id',
        'punto',
        'turno'
    ];
}
