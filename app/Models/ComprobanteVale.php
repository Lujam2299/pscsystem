<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobanteVale extends Model
{
    protected $fillable = ['vale_comida_id', 'user_id', 'archivo', 'monto'];
    protected $casts = ['monto' => 'decimal:2'];

    public function vale()
    {
        return $this->belongsTo(ValesComida::class, 'vale_comida_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
