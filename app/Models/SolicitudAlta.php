<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudAlta extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'solicitante',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'fecha_ingreso',
        'registro_patronal',
        'tipo_cotizacion',
        'sbc_fijo',
        'sbc_variable',
        'sbc_topado',
        'tipo_periodo',
        'curp',
        'rfc',
        'tipo_empleado',
        'nss',
        'fonacot',
        'infonavit',
        'reingreso',
        'entra_por',
        'estado_civil',
        'domicilio_calle',
        'domicilio_numero',
        'domicilio_colonia',
        'domicilio_ciudad',
        'domicilio_estado',
        'cp_fiscal',
        'liga_rfc',
        'domicilio comporbante',
        'sueldo_menusal',
        'tipo_cuenta_bancaria',
        'banco',
        'cuenta_bancaria',
        'sd',
        'sdi',
        'fdi',
        'telefono',
        'email',
        'modificacion_salario',
        'cuota_fija',
        'factor_descuento',
        'estatura',
        'peso',
        'status',
        'observaciones',
        'departamento',
        'empresa',
        'rol',
        'punto',
        'zona_supervisor',
        'ultima_edicion',
        'created_at',
        'updated_at',
    ];

    public function documentacion()
    {
        return $this->hasOne(DocumentacionAltas::class, 'solicitud_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'sol_alta_id');
    }

    public function usuario()
    {
        return $this->hasOne(User::class, 'sol_alta_id');
    }

    public function getTipoPeriodoFormattedAttribute()
    {
        switch ($this->tipo_periodo) {
            case 'quincenal':
                return 'Quincenal';
            case 'semanal':
                return 'Semanal';
            default:
                return 'No Disponible';
        }
    }
}
