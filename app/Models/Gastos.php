<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Gastos
 *
 * Representa los gastos registrados en el sistema.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $user_name
 * @property float $Monto
 * @property string $Fecha
 * @property string $Hora
 * @property string $Evidencia
 * @property string $Tipo
 * @property float|null $Km
 * @property float|null $Gasolina_antes_carga
 * @property float|null $Gasolina_despues_carga
 * @property \App\Models\User $user
 * @property-read int $userId
 * @property-write int $userId
 * @property-read string|null $userName
 * @property-write string|null $userName
 * @property-read float $monto
 * @property-write float $monto
 * @property-read Carbon $fecha
 * @property-write Carbon|string $fecha
 * @property-read string $hora
 * @property-write string $hora
 * @property-read string $evidencia
 * @property-write string $evidencia
 * @property-read string $tipo
 * @property-write string $tipo
 * @property-read float|null $km
 * @property-write float|null $km
 * @property-read float|null $gasolinaAntesCarga
 * @property-write float|null $gasolinaAntesCarga
 * @property-read float|null $gasolinaDespuesCarga
 * @property-write float|null $gasolinaDespuesCarga
 */
class Gastos extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gastos';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'Monto',
        'Fecha',
        'Litros',
        'Hora',
        'Evidencia',
        'Tipo',
        'Categoria',
        'Metodo_pago',
        'Descripcion',
        'Km',
        'Gasolina_antes_carga',
        'Gasolina_despues_carga',
    ];

    protected $casts = [
        'Fecha' => 'date',
        'Hora' => 'string',
        'Monto' => 'decimal:2',
        'Km' => 'decimal:2',
        'Gasolina_antes_carga' => 'decimal:2',
        'Gasolina_despues_carga' => 'decimal:2',
    ];

    /**
     * Relación: Usuario asociado al gasto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- Accessors (getters) ---

    public function getUserIdAttribute()
    {
        return $this->attributes['user_id'];
    }

    public function getUserNameAttribute()
    {
        return $this->attributes['user_name'];
    }

    public function getMontoAttribute()
    {
        return $this->attributes['Monto'];
    }

    public function getFechaAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    /**
     * Accessor para formatear la hora en formato HH:mm
     *
     * @param  string  $value
     * @return string
     */
    public function getHoraAttribute($value)
    {
        return date('H:i', strtotime($value));
    }

    public function getEvidenciaAttribute()
    {
        return $this->attributes['Evidencia'];
    }

    public function getTipoAttribute()
    {
        return $this->attributes['Tipo'];
    }

    public function getCategoriaEtiquetaAttribute(): string
    {
        return match ($this->attributes['Categoria'] ?? null) {
            'alimentos' => 'Alimentos',
            'propina' => 'Propina',
            'hotel' => 'Hotel',
            'peaje' => 'Peaje o caseta',
            'recarga_tag' => 'Recarga de TAG',
            'transporte' => 'Transporte o taxi',
            'estacionamiento' => 'Estacionamiento',
            'comision_atm' => 'Comisión bancaria o ATM',
            'lavado' => 'Lavado de vehículo',
            'otros' => 'Otros',
            'gasolina' => 'Gasolina',
            default => $this->Tipo === 'Viaticos' ? 'Viático sin categoría' : ($this->Tipo ?: 'Sin categoría'),
        };
    }

    public function getKmAttribute()
    {
        return $this->attributes['Km'];
    }

    public function getGasolinaAntesCargaAttribute()
    {
        return $this->attributes['Gasolina_antes_carga'];
    }

    public function getGasolinaDespuesCargaAttribute()
    {
        return $this->attributes['Gasolina_despues_carga'];
    }

    // --- Mutators (setters) ---

    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = $value;
    }

    public function setUserNameAttribute($value)
    {
        $this->attributes['user_name'] = $value;
    }

    public function setMontoAttribute($value)
    {
        $this->attributes['Monto'] = $value;
    }

    public function setFechaAttribute($value)
    {
        $this->attributes['Fecha'] = $value instanceof Carbon ? $value->toDateString() : $value;
    }

    public function setHoraAttribute($value)
    {
        $this->attributes['Hora'] = date('H:i:s', strtotime($value));
    }

    public function setEvidenciaAttribute($value)
    {
        $this->attributes['Evidencia'] = $value;
    }

    public function setTipoAttribute($value)
    {
        $this->attributes['Tipo'] = $value;
    }

    public function setKmAttribute($value)
    {
        $this->attributes['Km'] = $value;
    }

    public function setGasolinaAntesCargaAttribute($value)
    {
        $this->attributes['Gasolina_antes_carga'] = $value;
    }

    public function setGasolinaDespuesCargaAttribute($value)
    {
        $this->attributes['Gasolina_despues_carga'] = $value;
    }
}
