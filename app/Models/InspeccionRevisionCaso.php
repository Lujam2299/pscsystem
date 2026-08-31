<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspeccionRevisionCaso extends Model
{
    protected $fillable = [
        'estado', 'unidad_sugerida_id', 'unidad_confirmada_id', 'inspeccion_id',
        'placas_candidatas', 'confianza', 'notas_revision', 'created_by',
        'reviewed_by', 'confirmed_at',
    ];

    protected $casts = [
        'placas_candidatas' => 'array',
        'confianza' => 'integer',
        'confirmed_at' => 'datetime',
    ];

    public function mensajes(): HasMany
    {
        return $this->hasMany(InspeccionMensaje::class, 'caso_id')->orderBy('fecha_mensaje')->orderBy('id');
    }

    public function unidadSugerida(): BelongsTo
    {
        return $this->belongsTo(Unidades::class, 'unidad_sugerida_id');
    }

    public function unidadConfirmada(): BelongsTo
    {
        return $this->belongsTo(Unidades::class, 'unidad_confirmada_id');
    }

    public function inspeccion(): BelongsTo
    {
        return $this->belongsTo(InspeccionUnidad::class, 'inspeccion_id');
    }
}
