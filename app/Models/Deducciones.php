<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deducciones extends Model
{
    protected $fillable = [
        'user_id',
        'monto',
        'num_quincenas',
        'fecha_inicio',
        'concepto'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto' => 'float',
        'monto_pendiente' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope opcional para filtrar por status
    public function scopePorStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    // Scope opcional para filtrar por rango de fechas
    public function scopeEntreFechas($query, $inicio, $fin)
    {
        if ($inicio && $fin) {
            return $query->whereBetween('fecha_inicio', [$inicio, $fin]);
        } elseif ($inicio) {
            return $query->where('fecha_inicio', '>=', $inicio);
        } elseif ($fin) {
            return $query->where('fecha_inicio', '<=', $fin);
        }
        return $query;
    }

    // Scope opcional para buscar por nombre de usuario
    public function scopeBuscarUsuario($query, $nombre)
    {
        if ($nombre) {
            return $query->whereHas('user', function ($q) use ($nombre) {
                $q->where('name', 'like', "%{$nombre}%")
                  ->orWhere('email', 'like', "%{$nombre}%");
            });
        }
        return $query;
    }
}
