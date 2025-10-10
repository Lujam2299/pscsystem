<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValesComida extends Model
{
    protected $fillable = [
        'id',
        'fecha',
        'monto',
        'user_id',
        'num_elementos',
        'estatus',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comprobantes()
    {
        return $this->hasMany(ComprobanteVale::class, 'vale_comida_id');
    }
}
