<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\DocumentacionAltas;
use App\Models\User;

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
        'ultima_edicion',
        'created_at',
        'updated_at',
    ];

    public function documentacion()
    {
        return $this->hasOne(DocumentacionAltas::class, 'solicitud_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'sol_alta_id');
    }
    public function usuario() {
        return $this->hasOne(User::class, 'sol_alta_id');
    }

}
