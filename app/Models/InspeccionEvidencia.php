<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeccionEvidencia extends Model
{
    protected $fillable = [
        'inspeccion_id',
        'disk',
        'path',
        'nombre_original',
        'mime_type',
        'size',
        'sha256',
        'orden',
        'clasificacion',
    ];

    protected $casts = [
        'size' => 'integer',
        'orden' => 'integer',
    ];

    public function inspeccion(): BelongsTo
    {
        return $this->belongsTo(InspeccionUnidad::class, 'inspeccion_id');
    }
}
