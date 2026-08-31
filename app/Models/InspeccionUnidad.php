<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspeccionUnidad extends Model
{
    protected $table = 'inspecciones_unidades';

    protected $fillable = [
        'unidad_id',
        'fecha_inspeccion',
        'tipo',
        'kilometraje',
        'resultado',
        'observaciones',
        'reportado_por',
        'origen',
        'estado',
        'servicio_id',
        'siniestro_id',
        'created_by',
        'reviewed_by',
    ];

    protected $casts = [
        'fecha_inspeccion' => 'datetime',
        'kilometraje' => 'integer',
    ];

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidades::class, 'unidad_id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(InspeccionEvidencia::class, 'inspeccion_id')->orderBy('orden');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function siniestro(): BelongsTo
    {
        return $this->belongsTo(Siniestro::class, 'siniestro_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
