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
        $sueldo = $this->obtenerSueldoUsuario($user);

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

        // Calcular neto bruto (antes de ajuste)
        $netoBruto = round($subtotal - $isr, 2);

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
            'dias_pagados' => $diasPagados,
            'bonos' => $bonos,
            'horas_extra' => $horasExtra,
            'isr' => round($isr, 2),
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
     * Obtiene el registro de sueldo del usuario
     */
    protected function obtenerSueldoUsuario(User $user): ?object
    {
        // Primero intenta con rol + punto tal cual
        $sueldo = Sueldo::where('puesto', $user->rol)
            ->where('punto', $user->punto)
            ->first();

        if ($sueldo) {
            return $sueldo;
        }

        // Si no encontró, intenta mapear código → nombre usando la tabla subpuntos
        $puntoNombre = $this->resolverPuntoNombre($user->punto);

        if ($puntoNombre) {
            $sueldo = Sueldo::where('puesto', $user->rol)
                ->where('punto', $puntoNombre)
                ->first();

            if ($sueldo) {
                return $sueldo;
            }
        }

        // CASO ESPECIAL: si rol es null o vacío, intentar buscar solo por punto (resuelto o no)
        if (empty($user->rol)) {
            // Buscar solo por punto original
            $sueldo = Sueldo::where('punto', $user->punto)->first();
            if ($sueldo) {
                return $sueldo;
            }

            // Buscar por punto resuelto
            if ($puntoNombre) {
                $sueldo = Sueldo::where('punto', $puntoNombre)->first();
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

            $asistencia = $asistenciasIndexadas->get($fecha);

            $enlistados = json_decode($asistencia?->elementos_enlistados, true) ?? [];
            $faltantes = json_decode($asistencia?->faltas, true) ?? [];
            $descansantes = json_decode($asistencia?->descansos, true) ?? [];

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
            }
        }

        // Total días pagados (excluye permisos sin goce y faltas injustificadas)
        $totalPagados = $asistencias + $descansos + $vacaciones + $faltasJustificadas + $permisosConGoce;

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
            $asistencia = $asistenciasIndexadas->get($fecha);

            $enlistados = json_decode($asistencia?->elementos_enlistados, true) ?? [];
            $faltantes = json_decode($asistencia?->faltas, true) ?? [];
            $descansantes = json_decode($asistencia?->descansos, true) ?? [];

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
