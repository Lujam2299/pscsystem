<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Turno
 *
 * Representa los turnos registrados en el sistema.
 *
 * @property int $id
 * @property int $User_id
 * @property string $Nombre_elemento
 * @property string $Tipo
 * @property string $Hora_inicio
 * @property string $Hora_final
 * @property float $Km_inicio
 * @property float $Km_final
 * @property string $Punto
 * @property string $Placas_unidad
 * @property float $Rayas_gasolina_inicio
 * @property float $Rayas_gasolina_final
 * @property string $Evidencia_inicio
 * @property string $Evidencia_final
 * @property \App\Models\User $user
 */
class Turno extends Model
{
  use HasFactory;
  protected $table = 'turno';
  protected $primaryKey = 'id';
  public $timestamps = true;

  protected $fillable = [
    'User_id',
    'Nombre_elemento',
    'Tipo',
    'Fecha',
    'Hora_inicio',
    'Hora_final',
    'Km_inicio',
    'Km_final',
    'Punto',
    'Placas_unidad',
    'Rayas_gasolina_inicio',
    'Rayas_gasolina_final',
    'Evidencia_inicio',
    'Evidencia_final',
  ];


  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'remember_token',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'Hora_inicio' => 'datetime:H:i',
    'Hora_final' => 'datetime:H:i',
    'Fecha' => 'date',
    'Km_inicio' => 'decimal:2',
    'Km_final' => 'decimal:2',
    'Rayas_gasolina_inicio' => 'decimal:2',
    'Rayas_gasolina_final' => 'decimal:2',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  /**
   * Relación: Usuario asociado al turno
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }

}
