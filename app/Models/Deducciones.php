<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Deducciones extends Model
{
    protected $fillable = [
        'user_id',
        'monto',
        'monto_pendiente',
        'num_quincenas',
        'fecha_inicio',
        'fecha_fin',
        'concepto',
        'status' // Agregamos status para mejor control
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'monto' => 'float',
        'num_quincenas' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Método para calcular el monto pendiente en un rango de fechas
    public function montoPendienteEnRango($fechaInicio, $fechaFin)
    {
        $fechaInicio = Carbon::parse($fechaInicio);
        $fechaFin = Carbon::parse($fechaFin);

        // Si la deducción aún no comienza en este rango
        if ($this->fecha_inicio->gt($fechaFin)) {
            return 0;
        }

        // Si la deducción ya terminó antes de este rango
        if ($this->fecha_fin && $this->fecha_fin->lt($fechaInicio)) {
            return 0;
        }

        // Calcular cuántas quincenas caen en este rango
        $quincenasTranscurridas = $this->calcularQuincenasTranscurridas($fechaInicio);
        $quincenasEnRango = $this->calcularQuincenasEnRango($fechaInicio, $fechaFin);

        $montoPorQuincena = $this->monto / $this->num_quincenas;
        $montoDescontado = $quincenasTranscurridas * $montoPorQuincena;
        $montoADescontarEnRango = $quincenasEnRango * $montoPorQuincena;

        // Limitar al monto total
        $montoDescontado = min($montoDescontado, $this->monto);
        $montoADescontarEnRango = min($montoADescontarEnRango, $this->monto - $montoDescontado);

        return round($montoADescontarEnRango, 2);
    }

    // Método para calcular el saldo pendiente total
    public function saldoPendiente($fechaReferencia = null)
    {
        if (!$fechaReferencia) {
            $fechaReferencia = Carbon::today();
        } else {
            $fechaReferencia = Carbon::parse($fechaReferencia);
        }

        $quincenasTranscurridas = $this->calcularQuincenasTranscurridas($fechaReferencia);
        $montoPorQuincena = $this->monto / $this->num_quincenas;
        $montoDescontado = $quincenasTranscurridas * $montoPorQuincena;

        $saldo = $this->monto - $montoDescontado;
        return max(0, round($saldo, 2)); // No devolver negativos
    }

    // Método para calcular cuántas quincenas han transcurrido hasta una fecha
    public function calcularQuincenasTranscurridas($fechaReferencia)
    {
        $fechaInicio = $this->fecha_inicio;
        $fechaReferencia = Carbon::parse($fechaReferencia);

        if ($fechaReferencia->lt($fechaInicio)) {
            return 0;
        }

        // Suponiendo quincenas del 1-15 y 16-fin de mes
        // Este es un ejemplo - ajusta según tus reglas de negocio
        $quincenas = 0;
        $current = $fechaInicio->copy();

        while ($current->lte($fechaReferencia)) {
            // Cada quincena: del 1-15 y del 16-fin de mes
            $quincena1 = Carbon::create($current->year, $current->month, 15);
            $quincena2 = Carbon::create($current->year, $current->month, $current->daysInMonth);

            if ($current->day <= 15 && $fechaReferencia->gte($quincena1)) {
                $quincenas++;
            }

            if ($current->day >= 16 && $fechaReferencia->gte($quincena2)) {
                $quincenas++;
            }

            $current->addMonth();
        }

        return min($quincenas, $this->num_quincenas);
    }

    // Método para calcular cuántas quincenas hay en un rango
    public function calcularQuincenasEnRango($fechaInicio, $fechaFin)
    {
        $fechaInicio = Carbon::parse($fechaInicio);
        $fechaFin = Carbon::parse($fechaFin);

        $quincenasInicio = $this->calcularQuincenasTranscurridas($fechaInicio);
        $quincenasFin = $this->calcularQuincenasTranscurridas($fechaFin);

        return $quincenasFin - $quincenasInicio;
    }

    // Método para verificar si la deducción está activa en un rango
    public function estaActivaEnRango($fechaInicio, $fechaFin)
    {
        $fechaInicio = Carbon::parse($fechaInicio);
        $fechaFin = Carbon::parse($fechaFin);

        $estaDespuesInicio = $this->fecha_inicio->lte($fechaFin);
        $estaAntesFin = !$this->fecha_fin || $this->fecha_fin->gte($fechaInicio);

        return $estaDespuesInicio && $estaAntesFin && $this->saldoPendiente($fechaFin) > 0;
    }

    // Getter para fecha_fin calculada
    public function getFechaFinAttribute()
    {
        // Calculamos la fecha de fin aproximada basada en el número de quincenas
        $fechaFin = $this->attributes['fecha_inicio'];

        // Asumiendo quincenas mensuales
        $meses = ceil($this->num_quincenas / 2); // Aproximadamente
        return Carbon::parse($fechaFin)->addMonths($meses)->format('Y-m-d');
    }

    // Scope para activas en un rango
    public function scopeActivasEnRango($query, $fechaInicio, $fechaFin)
    {
        return $query->where(function($q) use ($fechaInicio, $fechaFin) {
            $q->where('fecha_inicio', '<=', $fechaFin)
              ->where(function($sub) use ($fechaInicio) {
                  $sub->whereNull('fecha_fin')
                      ->orWhere('fecha_fin', '>=', $fechaInicio);
              });
        })->where('status', '!=', 'pagado');
    }

    // Scope para deducciones pendientes
    public function scopePendientes($query)
    {
        return $query->where('status', '!=', 'pagado');
    }

    // Scope para deducciones de un usuario en un rango
    public function scopeUsuarioEnRango($query, $userId, $fechaInicio, $fechaFin)
    {
        return $query->where('user_id', $userId)
                    ->activasEnRango($fechaInicio, $fechaFin);
    }
}
