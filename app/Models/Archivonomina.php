<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archivonomina extends Model
{
    protected $fillable = [
        'arch_nomina',
        'arch_nomina_spyt',
        'arch_nomina_montana',
        'arch_destajo',
        'periodo',
        'subtotal',
        'total_destajos'
    ];
}
