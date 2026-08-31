<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeccionMensajeArchivo extends Model
{
    protected $fillable = [
        'mensaje_id', 'disk', 'path', 'nombre_original', 'mime_type', 'size', 'sha256', 'orden',
    ];

    protected $casts = [
        'size' => 'integer',
        'orden' => 'integer',
    ];

    public function mensaje(): BelongsTo
    {
        return $this->belongsTo(InspeccionMensaje::class, 'mensaje_id');
    }
}
