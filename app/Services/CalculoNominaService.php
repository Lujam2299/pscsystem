<?php

namespace App\Services;

use App\Models\User;
use App\Models\Sueldo;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalculoNominaService
{
    protected const BONO_ASISTENCIA = 0.10;
    protected const BONO_PUNTUALIDAD = 0.10;
    protected const MINUTOS_TOLERANCIA_PUNTUALIDAD = 20;
    protected const DIAS_PENALIZACION_FALTA = 1;

    /**
     * Calcula las percepciones de un usuario para un periodo
     */
    public function calcularPercepciones(
        User $user,
        string $fechaInicio,
        string $fechaFin,
        array $datosAsistencias
    ): array {
        $sueldo = $this->obtenerSueldoUsuario($user, $fechaInicio);

        if (!$sueldo) {
            return $this->respuestaError('No se encontró registro de sueldo para el usuario');
        }

        $sueldoDiario = floatval($sueldo->sd); // ✅ Campo correcto

        // Calcular días pagados según concepto
        $diasPagados = $this->calcularDiasPagados(
            $user->id,
            $fechaInicio,
            $fechaFin,
            $datosAsistencias
        );

        // Calcular bonos
        $bonos = $this->calcularBonos(
            $user->id,
            $fechaInicio,
            $fechaFin,
            $datosAsistencias,
            $diasPagados,
            $sueldoDiario
        );

        // Calcular horas extra
        $horasExtra = $this->calcularHorasExtra(
            $user->id,
            $fechaInicio,
            $fechaFin,
            $datosAsistencias,
            $sueldoDiario
        );

        // Subtotal percepciones
        $subtotal = ($diasPagados['total'] * $sueldoDiario) + $bonos['total'] + $horasExtra['monto'];

        // Calcular ISR
        $anio = Carbon::parse($fechaInicio)->year;
        $isr = $this->calcularIsrBruto($subtotal, $anio);

        // 🔑 NUEVO: Calcular deducciones especiales
        $deduccionesEspeciales = $this->calcularDeduccionesEspeciales($user->id, $fechaInicio, $fechaFin);

        // Calcular neto bruto (antes de ajuste) - incluyendo deducciones especiales
        $netoBruto = round($subtotal - $isr - $deduccionesEspeciales, 2);

        // 🔑 Ajuste al neto según ÚLTIMO DÍGITO DECIMAL (ej: 2141.84 → 4)
        $decimalStr = number_format($netoBruto, 2, '.', '');
        $ultimoDigito = (int) substr($decimalStr, -1); // Último carácter: '0' a '9'

        $ajusteMonto = 0;
        $ajusteTipo = 'ninguno';

        if ($ultimoDigito > 5) {
            // Redondear hacia arriba: + (10 - últimoDigito) / 100
            $ajusteMonto = (10 - $ultimoDigito) / 100;
            $ajusteTipo = 'percepcion';
            $netoFinal = $netoBruto + $ajusteMonto;
        } elseif ($ultimoDigito < 5) {
            // Redondear hacia abajo: - (últimoDigito) / 100
            $ajusteMonto = $ultimoDigito / 100;
            $ajusteTipo = 'deduccion';
            $netoFinal = $netoBruto - $ajusteMonto;
        } else {
            // Último dígito = 5 → sin ajuste
            $netoFinal = $netoBruto;
        }

        return [
            'success' => true,
            'user_id' => $user->id,
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'sueldo_diario' => $sueldoDiario,
            'sueldo_quincenal' => floatval($sueldo->sueldo_quincenal ?? 0),
            'salario_vigencia' => [
                'desde' => $sueldo->vigente_desde?->format('Y-m-d'),
                'hasta' => $sueldo->vigente_hasta?->format('Y-m-d'),
            ],
            'dias_pagados' => $diasPagados,
            'bonos' => $bonos,
            'horas_extra' => $horasExtra,
            'isr' => round($isr, 2),
            'deducciones_especiales' => round($deduccionesEspeciales, 2),
            'detalle_deducciones_especiales' => $this->obtenerDetalleDeducciones($user->id, $fechaInicio, $fechaFin),
            'ajuste_al_neto' => [
                'monto' => round($ajusteMonto, 2),
                'tipo' => $ajusteTipo,
            ],
            'total_neto' => round($netoFinal, 2),
            'subtotal_percepciones' => round($subtotal, 2),
            'desglose' => [
                'concepto_base' => round($diasPagados['total'] * $sueldoDiario, 2),
                'concepto_bonos' => round($bonos['total'], 2),
                'concepto_horas_extra' => round($horasExtra['monto'], 2),
            ]
        ];
    }

    /**
     * Calcula el total de deducciones especiales para un usuario en un periodo
     */
    public function calcularDeduccionesEspeciales(
    int $userId,
    string $fechaInicio,
    string $fechaFin
): float {
    \Log::info('DEBUG - CalcularDeduccionesEspeciales', [
        'userId' => $userId,
        'fechaInicio' => $fechaInicio,
        'fechaFin' => $fechaFin,
    ]);

    $deducciones = \App\Models\Deducciones::where('user_id', $userId)
        ->where('fecha_inicio', '<=', $fechaFin) // comienza antes o durante el rango
        ->where(function($q) use ($fechaInicio) {
            // Si fecha_fin es NULL → sigue activa indefinidamente
            // Si tiene fecha_fin → debe ser >= fechaInicio (aún no terminó)
            $q->whereNull('fecha_fin')
              ->orWhere('fecha_fin', '>=', $fechaInicio);
        })
        ->where('status', '!=', 'Pagado')
        ->get();

    \Log::info('DEBUG - Deducciones encontradas', [
        'count' => $deducciones->count(),
        'deducciones' => $deducciones->map(fn($d) => [
            'id' => $d->id,
            'concepto' => $d->concepto,
            'monto' => $d->monto,
            'num_quincenas' => $d->num_quincenas,
            'fecha_inicio' => $d->fecha_inicio,
            'fecha_fin' => $d->fecha_fin,
            'status' => $d->status,
        ])->toArray(),
    ]);

    $totalDeducciones = 0;
    foreach ($deducciones as $deduccion) {
        $monto = $this->calcularMontoDeduccionEnRango($deduccion, $fechaInicio, $fechaFin);
        \Log::info("DEBUG - Monto deduccion {$deduccion->id}: $monto", [
            'monto_por_quincena' => $deduccion->monto / $deduccion->num_quincenas,
            'quincenas_inicio' => $this->calcularQuincenasTranscurridas($deduccion, $fechaInicio),
            'quincenas_fin' => $this->calcularQuincenasTranscurridas($deduccion, $fechaFin),
        ]);
        $totalDeducciones += $monto;
    }

    return round($totalDeducciones, 2);
}

    /**
     * Obtiene el detalle de deducciones para mostrar en el modal
     */
    public function obtenerDetalleDeducciones(
        int $userId,
        string $fechaInicio,
        string $fechaFin
    ): array {
        $deducciones = \App\Models\Deducciones::where('user_id', $userId)
            ->where(function($q) use ($fechaInicio, $fechaFin) {
                $q->where('fecha_inicio', '<=', $fechaFin)
                  ->where(function($sub) use ($fechaInicio) {
                      $sub->whereNull('fecha_fin')
                          ->orWhere('fecha_fin', '>=', $fechaInicio);
                  });
            })
            ->where('status', '!=', 'Pagado')
            ->get();

        $detalle = [];
        foreach ($deducciones as $deduccion) {
            $montoEnPeriodo = $this->calcularMontoDeduccionEnRango($deduccion, $fechaInicio, $fechaFin);
            if ($montoEnPeriodo > 0) {
                $detalle[] = [
                    'id' => $deduccion->id,
                    'concepto' => $deduccion->concepto,
                    'monto_periodo' => $montoEnPeriodo,
                    'monto_total' => $deduccion->monto,
                    'saldo_pendiente' => $this->calcularSaldoPendienteDeduccion($deduccion, $fechaFin),
                    'num_quincenas' => $deduccion->num_quincenas,
                    'fecha_inicio' => $deduccion->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $this->calcularFechaFinDeduccion($deduccion)->format('Y-m-d'),
                ];
            }
        }

        return $detalle;
    }

    /**
 * Calcula el monto pendiente de una deducción en un rango de fechas
 */
private function calcularMontoDeduccionEnRango($deduccion, $fechaInicio, $fechaFin)
{
    $fechaInicio = Carbon::parse($fechaInicio);
    $fechaFin = Carbon::parse($fechaFin);

    // Si la deducción aún no comienza en este rango
    if ($deduccion->fecha_inicio->gt($fechaFin)) {
        return 0;
    }

    $montoPorQuincena = $deduccion->monto / $deduccion->num_quincenas;

    // Contar cuántas quincenas activas hay en el periodo
    $quincenasEnRango = 0;
    $current = $deduccion->fecha_inicio->copy();

    // Asumimos quincenas del 1-15 y del 16-fin de mes
    while ($current->lte($fechaFin)) {
        // Quincena del 1-15
        $quincena1 = Carbon::create($current->year, $current->month, 15);
        if ($current->day <= 15 && $fechaInicio->lte($quincena1) && $quincena1->lte($fechaFin)) {
            $quincenasEnRango++;
        }

        // Quincena del 16-fin de mes
        $quincena2 = Carbon::create($current->year, $current->month, $current->daysInMonth());
        if ($current->day >= 16 && $fechaInicio->lte($quincena2) && $quincena2->lte($fechaFin)) {
            $quincenasEnRango++;
        }

        // Avanzar al siguiente mes
        $current->addMonth();

        // Límite para evitar bucles infinitos
        if ($current->gt($fechaFin->copy()->addMonths(24))) {
            break;
        }
    }

    // Limitar a las quincenas restantes de la deducción
    $saldoPendienteInicio = $this->calcularSaldoPendienteDeduccion($deduccion, $fechaInicio);
    $montoMaximoAPagar = min($saldoPendienteInicio, $quincenasEnRango * $montoPorQuincena);

    return round($montoMaximoAPagar, 2);
}

    /**
     * Calcula el saldo pendiente de una deducción hasta una fecha
     */
    private function calcularSaldoPendienteDeduccion($deduccion, $fechaReferencia)
    {
        $fechaReferencia = Carbon::parse($fechaReferencia);

        $quincenasTranscurridas = $this->calcularQuincenasTranscurridas($deduccion, $fechaReferencia);
        $montoPorQuincena = $deduccion->monto / $deduccion->num_quincenas;
        $montoDescontado = $quincenasTranscurridas * $montoPorQuincena;

        $saldo = $deduccion->monto - $montoDescontado;
        return max(0, round($saldo, 2)); // No devolver negativos
    }

    /**
 * Calcula cuántas quincenas han transcurrido para una deducción hasta una fecha
 */
private function calcularQuincenasTranscurridas($deduccion, $fechaReferencia)
{
    $fechaInicio = $deduccion->fecha_inicio;
    $fechaReferencia = Carbon::parse($fechaReferencia);

    if ($fechaReferencia->lt($fechaInicio)) {
        return 0;
    }

    $quincenas = 0;
    $current = $fechaInicio->copy();

    // Asumimos quincenas del 1-15 y 16-fin de mes
    while ($current->lte($fechaReferencia)) {
        // Quincena del 1-15
        $quincena1 = Carbon::create($current->year, $current->month, 15);
        if ($current->day <= 15 && $fechaReferencia->gte($quincena1)) {
            $quincenas++;
        }

        // Quincena del 16-fin de mes
        $quincena2 = Carbon::create($current->year, $current->month, $current->daysInMonth());
        if ($current->day >= 16 && $fechaReferencia->gte($quincena2)) {
            $quincenas++;
        }

        $current->addMonth();

        // Límite para evitar bucles infinitos
        if ($quincenas >= $deduccion->num_quincenas) {
            $quincenas = $deduccion->num_quincenas;
            break;
        }
    }

    return min($quincenas, $deduccion->num_quincenas);
}

    /**
     * Calcula la fecha de fin estimada de una deducción
     */
    private function calcularFechaFinDeduccion($deduccion)
    {
        $fechaInicio = $deduccion->fecha_inicio;
        $numQuincenas = $deduccion->num_quincenas;

        $current = $fechaInicio->copy();
        $quincenasContadas = 0;

        while ($quincenasContadas < $numQuincenas) {
            $quincena1 = Carbon::create($current->year, $current->month, 15);
            $quincena2 = Carbon::create($current->year, $current->month, $current->daysInMonth());

            if ($current->day <= 15) {
                $current = $quincena1;
                $quincenasContadas++;
            } else {
                $current = $quincena2;
                $quincenasContadas++;
            }

            if ($quincenasContadas < $numQuincenas) {
                $current->addDay(); // Pasar al siguiente día para continuar
            }
        }

        return $current;
    }

    /**
     * Obtiene el registro de sueldo del usuario
     */
    protected function obtenerSueldoUsuario(User $user, string $fechaReferencia): ?object
    {
        $aplicarVigencia = function ($query) use ($fechaReferencia) {
            return $query->where(function ($q) use ($fechaReferencia) {
                $q->whereNull('vigente_desde')->orWhereDate('vigente_desde', '<=', $fechaReferencia);
            })->where(function ($q) use ($fechaReferencia) {
                $q->whereNull('vigente_hasta')->orWhereDate('vigente_hasta', '>=', $fechaReferencia);
            })->orderByRaw('vigente_desde IS NULL ASC')->orderByDesc('vigente_desde');
        };

        // Primero intenta con rol + punto tal cual
        $sueldo = $aplicarVigencia(Sueldo::where('puesto', $user->rol)
            ->where('punto', $user->punto))->first();

        if ($sueldo) {
            return $sueldo;
        }

        // Si no encontró, intenta mapear código → nombre usando la tabla subpuntos
        $puntoNombre = $this->resolverPuntoNombre($user->punto);

        if ($puntoNombre) {
            $sueldo = $aplicarVigencia(Sueldo::where('puesto', $user->rol)
                ->where('punto', $puntoNombre))->first();

            if ($sueldo) {
                return $sueldo;
            }
        }

        // CASO ESPECIAL: si rol es null o vacío, intentar buscar solo por punto (resuelto o no)
        if (empty($user->rol)) {
            // Buscar solo por punto original
            $sueldo = $aplicarVigencia(Sueldo::where('punto', $user->punto))->first();
            if ($sueldo) {
                return $sueldo;
            }

            // Buscar por punto resuelto
            if ($puntoNombre) {
                $sueldo = $aplicarVigencia(Sueldo::where('punto', $puntoNombre))->first();
                if ($sueldo) {
                    return $sueldo;
                }
            }
        }

        return null;
    }

    /**
     * Resuelve el nombre del punto a partir de su código (o viceversa)
     */
    protected function resolverPuntoNombre(string $valor): ?string
    {
        // Caso 1: Si es un código numérico (con o sin ceros adelante)
        if (preg_match('/^\d+$/', $valor)) {
            $codigoInt = (int) $valor;
            $subpunto = \App\Models\Subpunto::where('codigo', $codigoInt)->first();
            if ($subpunto) {
                return $subpunto->nombre;
            }
        }

        // Caso 2: Nombre directo
        $subpuntoPorNombre = \App\Models\Subpunto::where('nombre', $valor)->first();
        if ($subpuntoPorNombre) {
            return $subpuntoPorNombre->nombre;
        }

        // Caso 3: Búsqueda insensible a mayúsculas
        $subpuntoFuzzy = \App\Models\Subpunto::whereRaw('LOWER(nombre) = LOWER(?)', [$valor])->first();
        if ($subpuntoFuzzy) {
            return $subpuntoFuzzy->nombre;
        }

        return null;
    }

    /**
     * Calcula el total de días pagados por concepto
     */
    protected function calcularDiasPagados(
        int $userId,
        string $fechaInicio,
        string $fechaFin,
        array $datosAsistencias
    ): array {
        $fechas = $this->generarFechas($fechaInicio, $fechaFin);

        $asistencias = 0;
        $descansos = 0;
        $vacaciones = 0;
        $faltasJustificadas = 0;
        $permisosConGoce = 0;
        $permisosSinGoce = 0;
        $faltasInjustificadas = 0;
        $pendientesCaptura = 0;

        $vacacionesPorUsuario = $datosAsistencias['vacacionesPorUsuario'][$userId] ?? [];
        $permisosPorUsuario = $datosAsistencias['permisosPorUsuario'][$userId] ?? [];
        $faltasJustificadasData = $datosAsistencias['faltasJustificadas'][$userId] ?? [];
        $asistenciasIndexadas = $datosAsistencias['asistenciasIndexadas'];
        $incapacidadesDelUsuario = $datosAsistencias['incapacidadesPorUsuario'][$userId] ?? [];

        foreach ($fechas as $fecha) {
            // Ignorar días de incapacidad
            if (in_array($fecha, $incapacidadesDelUsuario)) {
                continue;
            }

            $asistencia = $this->buscarAsistenciaUsuario($asistenciasIndexadas, $fecha, $userId);

            $enlistados = json_decode($asistencia?->elementos_enlistados ?? '[]', true) ?? [];
            $faltantes = json_decode($asistencia?->faltas ?? '[]', true) ?? [];
            $descansantes = json_decode($asistencia?->descansos ?? '[]', true) ?? [];

            $esAsistencia = in_array($userId, $enlistados);
            $esFalta = in_array($userId, $faltantes);
            $esDescanso = in_array($userId, $descansantes);

            // Permiso especial (prioridad máxima después de incapacidad)
            $permiso = $permisosPorUsuario[$fecha] ?? null;
            if ($permiso) {
                if ($permiso['con_goce']) {
                    $permisosConGoce++;
                } else {
                    $permisosSinGoce++;
                }
                continue; // Salta el resto: permiso ya lo define
            }

            // Vacaciones
            if (in_array($fecha, $vacacionesPorUsuario)) {
                $vacaciones++;
                continue;
            }

            // Descanso
            if ($esDescanso) {
                $descansos++;
                continue;
            }

            // Falta
            if ($esFalta) {
                if (!empty($faltasJustificadasData[$fecha])) {
                    $faltasJustificadas++;
                } else {
                    $faltasInjustificadas++;
                }
                continue;
            }

            // Asistencia
            if ($esAsistencia) {
                $asistencias++;
            } else {
                $pendientesCaptura++;
            }
        }

        // Total días pagados (excluye permisos sin goce y faltas injustificadas)
        // Un día sin captura no se convierte en descuento automático. Se incluye
        // provisionalmente y el resultado queda marcado como incompleto.
        $totalPagados = $asistencias + $descansos + $vacaciones + $faltasJustificadas
            + $permisosConGoce + $pendientesCaptura;

        return [
            'total' => $totalPagados,
            'desglose' => [
                'asistencias' => $asistencias,
                'descansos' => $descansos,
                'vacaciones' => $vacaciones,
                'faltas_justificadas' => $faltasJustificadas,
                'permisos_con_goce' => $permisosConGoce,
                'permisos_sin_goce' => $permisosSinGoce,
                'faltas_injustificadas' => $faltasInjustificadas,
                'pendientes_captura' => $pendientesCaptura,
            ]
        ];
    }

    /**
     * Calcula los bonos de asistencia y puntualidad
     */
    protected function calcularBonos(
        int $userId,
        string $fechaInicio,
        string $fechaFin,
        array $datosAsistencias,
        array $diasPagados,
        float $sueldoDiario
    ): array {
        $fechas = $this->generarFechas($fechaInicio, $fechaFin);
        $asistenciasIndexadas = $datosAsistencias['asistenciasIndexadas'];
        $retardosPorUsuario = $datosAsistencias['retardosPorUsuario'][$userId] ?? [];
        $vacacionesPorUsuario = $datosAsistencias['vacacionesPorUsuario'][$userId] ?? [];
        $permisosPorUsuario = $datosAsistencias['permisosPorUsuario'][$userId] ?? [];
        $faltasJustificadasData = $datosAsistencias['faltasJustificadas'][$userId] ?? [];
        $incapacidadesDelUsuario = $datosAsistencias['incapacidadesPorUsuario'][$userId] ?? [];

        // Contadores
        $totalMinutosRetardo = 0;
        $tieneFaltaInjustificada = false;
        $tienePermisoSinGoce = false;
        $tieneIncapacidad = count($incapacidadesDelUsuario) > 0;

        foreach ($fechas as $fecha) {
            $asistencia = $this->buscarAsistenciaUsuario($asistenciasIndexadas, $fecha, $userId);

            $enlistados = json_decode($asistencia?->elementos_enlistados ?? '[]', true) ?? [];
            $faltantes = json_decode($asistencia?->faltas ?? '[]', true) ?? [];
            $descansantes = json_decode($asistencia?->descansos ?? '[]', true) ?? [];

            $esAsistencia = in_array($userId, $enlistados);
            $esFalta = in_array($userId, $faltantes);
            $esDescanso = in_array($userId, $descansantes);

            // Verificar permisos especiales
            $permiso = $permisosPorUsuario[$fecha] ?? null;
            if ($permiso) {
                if (!(int)$permiso['con_goce']) {
                    $tienePermisoSinGoce = true;
                }
            }

            // Detectar falta injustificada (solo si no hay permiso ni incapacidad en esa fecha)
            if ($esFalta && !isset($permiso) && !in_array($fecha, $incapacidadesDelUsuario)) {
                $esJustificada = $faltasJustificadasData[$fecha] ?? false;
                if (!$esJustificada) {
                    $tieneFaltaInjustificada = true;
                }
            }

            // Sumar minutos de retardo
            $totalMinutosRetardo += $retardosPorUsuario[$fecha] ?? 0;
        }

        // Determinar si aplica bono
        $aplicaBonoAsistencia = !$tieneFaltaInjustificada && !$tienePermisoSinGoce && !$tieneIncapacidad;
        $aplicaBonoPuntualidad = $aplicaBonoAsistencia && $totalMinutosRetardo === 0;

        // Calcular subtotal base
        $subtotalBase = $diasPagados['total'] * $sueldoDiario;

        // Calcular montos de bonos
        $bonoAsistencia = $aplicaBonoAsistencia ? $subtotalBase * self::BONO_ASISTENCIA : 0;
        $bonoPuntualidad = $aplicaBonoPuntualidad ? $subtotalBase * self::BONO_PUNTUALIDAD : 0;

        return [
            'asistencia' => [
                'aplica' => $aplicaBonoAsistencia,
                'porcentaje' => self::BONO_ASISTENCIA,
                'monto' => round($bonoAsistencia, 2),
            ],
            'puntualidad' => [
                'aplica' => $aplicaBonoPuntualidad,
                'porcentaje' => self::BONO_PUNTUALIDAD,
                'monto' => round($bonoPuntualidad, 2),
                'minutos_retardo_total' => $totalMinutosRetardo,
            ],
            'penalizacion_faltas' => [
                'aplica' => !$aplicaBonoAsistencia,
                'dias_restantes' => 0,
            ],
            'total' => round($bonoAsistencia + $bonoPuntualidad, 2),
        ];
    }

    /**
     * Calcula el pago de horas extra
     */
    protected function calcularHorasExtra(
        int $userId,
        string $fechaInicio,
        string $fechaFin,
        array $datosAsistencias,
        float $sueldoDiario
    ): array {
        $horasExtrasPorUsuario = $datosAsistencias['horasExtrasPorUsuario'][$userId] ?? [];
        $totalHoras = array_sum($horasExtrasPorUsuario);

        $zona = $this->resolverZonaUsuario($userId);

        if (!$zona) {
            return [
                'total_horas' => $totalHoras,
                'valor_hora' => 0,
                'monto' => 0,
                'desglose_diario' => $horasExtrasPorUsuario,
                'zona' => null,
                'costo_12h' => 0,
            ];
        }

        $costoModel = \App\Models\TiempoExtraCosto::where('zona', $zona)->first();

        if (!$costoModel) {
            return [
                'total_horas' => $totalHoras,
                'valor_hora' => 0,
                'monto' => 0,
                'desglose_diario' => $horasExtrasPorUsuario,
                'zona' => $zona,
                'costo_12h' => 0,
            ];
        }

        $costo12Horas = floatval($costoModel->costo_12_horas);
        $valorPorHora = $costo12Horas / 12;
        $monto = $totalHoras * $valorPorHora;

        return [
            'total_horas' => $totalHoras,
            'valor_hora' => round($valorPorHora, 2),
            'monto' => round($monto, 2),
            'desglose_diario' => $horasExtrasPorUsuario,
            'zona' => $zona,
            'costo_12h' => $costo12Horas,
        ];
    }

    /**
     * Calcula ISR bruto (antes de deducciones personales)
     */
    protected function calcularIsrBruto(float $gravable, int $anio): float
    {
        $tarifa = \App\Models\IsrTarifa::where('anio', $anio)
            ->where('limite_inferior', '<=', $gravable)
            ->orderByDesc('limite_inferior')
            ->first();

        if (!$tarifa) {
            return 0.0;
        }

        $excedente = max(0, $gravable - $tarifa->limite_inferior);
        $isr = $tarifa->cuota_fija + ($excedente * ($tarifa->porcentaje_excedente / 100));

        return round($isr, 2);
    }

    /**
     * Resuelve la zona principal (punto) a partir del punto/código del usuario
     */
    protected function resolverZonaUsuario(int $userId): ?string
    {
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return null;
        }

        $puntoNombre = $this->resolverPuntoNombre($user->punto) ?? $user->punto;

        $subpunto = \App\Models\Subpunto::where(function($q) use ($puntoNombre, $user) {
            $q->where('nombre', $puntoNombre)
              ->orWhere('codigo', (int)$user->punto);
        })->first();

        if ($subpunto) {
            $punto = \App\Models\Punto::find($subpunto->punto_id);
            return $punto?->nombre;
        }

        return $puntoNombre;
    }

    /**
     * Genera un array de fechas entre inicio y fin
     */
    protected static function generarFechas(string $inicio, string $fin): array
    {
        $fechas = [];
        $current = Carbon::parse($inicio);
        $end = Carbon::parse($fin);

        while ($current->lte($end)) {
            $fechas[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $fechas;
    }

    /** Localiza el registro diario que realmente contiene al empleado. */
    protected function buscarAsistenciaUsuario(Collection $indexadas, string $fecha, int $userId): ?object
    {
        $registros = $indexadas->get($fecha, collect());
        if (!($registros instanceof Collection)) {
            $registros = collect([$registros]);
        }

        return $registros->first(function ($registro) use ($userId) {
            foreach (['elementos_enlistados', 'faltas', 'descansos'] as $campo) {
                if (in_array($userId, json_decode($registro->{$campo} ?? '[]', true) ?? [])) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Respuesta estandarizada para errores
     */
    protected function respuestaError(string $mensaje): array
    {
        return [
            'success' => false,
            'error' => $mensaje,
            'subtotal_percepciones' => 0,
        ];
    }
}
