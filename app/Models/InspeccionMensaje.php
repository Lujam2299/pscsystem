<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspeccionMensaje extends Model
{
    protected $fillable = [
        'caso_id', 'origen', 'external_id', 'conversacion', 'remitente',
        'fecha_mensaje', 'tipo', 'texto', 'incluido', 'estado', 'metadata', 'created_by',
    ];

    protected $casts = [
        'fecha_mensaje' => 'datetime',
        'incluido' => 'boolean',
        'metadata' => 'array',
    ];

    public function caso(): BelongsTo
    {
        return $this->belongsTo(InspeccionRevisionCaso::class, 'caso_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(InspeccionMensajeArchivo::class, 'mensaje_id')->orderBy('orden');
    }
}
