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
            $sueldoDiario // ✅ Nuevo parámetro
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
        $sueldo = Sueldo::where('puesto', $user->rol)  // ✅ rol → puesto
            ->where('punto', $user->punto)
            ->first();

        if ($sueldo) {
            return $sueldo;
        }

        // Si no encontró, intenta mapear código → nombre usando la tabla subpuntos
        $puntoNombre = $this->resolverPuntoNombre($user->punto);

        if ($puntoNombre) {
            $sueldo = Sueldo::where('puesto', $user->rol)  // ✅ aún rol
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
            // Convertir a int para eliminar ceros adelante, luego formatear como 3 dígitos para comparar con DB
            $codigoInt = (int) $valor;

            // Buscar en Subpunto por código (como int, ya que en DB es int)
            $subpunto = \App\Models\Subpunto::where('codigo', $codigoInt)->first();
            if ($subpunto) {
                return $subpunto->nombre;
            }
        }

        // Caso 2: Si es un nombre directo (ej. "DALTILE"), verificar si existe
        $subpuntoPorNombre = \App\Models\Subpunto::where('nombre', $valor)->first();
        if ($subpuntoPorNombre) {
            return $subpuntoPorNombre->nombre;
        }

        // Caso 3: Intentar buscar por nombre ignorando mayúsculas/espacios
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

        foreach ($fechas as $fecha) {
            $asistencia = $asistenciasIndexadas->get($fecha);

            $enlistados = json_decode($asistencia?->elementos_enlistados, true) ?? [];
            $faltantes = json_decode($asistencia?->faltas, true) ?? [];
            $descansantes = json_decode($asistencia?->descansos, true) ?? [];

            $esAsistencia = in_array($userId, $enlistados);
            $esFalta = in_array($userId, $faltantes);
            $esDescanso = in_array($userId, $descansantes);

            // Verificar permisos especiales primero (tienen prioridad)
            if (isset($permisosPorUsuario[$fecha])) {
                if ($permisosPorUsuario[$fecha]['con_goce']) {
                    $permisosConGoce++;
                } else {
                    $permisosSinGoce++;
                }
            }
            // Verificar vacaciones
            elseif (in_array($fecha, $vacacionesPorUsuario)) {
                $vacaciones++;
            }
            // Verificar descanso
            elseif ($esDescanso) {
                $descansos++;
            }
            // Verificar falta
            elseif ($esFalta) {
                if (!empty($faltasJustificadasData[$fecha])) {
                    $faltasJustificadas++;
                } else {
                    $faltasInjustificadas++;
                }
            }
            // Si asistió
            elseif ($esAsistencia) {
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

    // Contadores
    $totalDias = count($fechas);
    $diasAsistenciaODescanso = 0;
    $totalMinutosRetardo = 0;

    foreach ($fechas as $fecha) {
        $asistencia = $asistenciasIndexadas->get($fecha);

        $enlistados = json_decode($asistencia?->elementos_enlistados, true) ?? [];
        $faltantes = json_decode($asistencia?->faltas, true) ?? [];
        $descansantes = json_decode($asistencia?->descansos, true) ?? [];

        $esAsistencia = in_array($userId, $enlistados);
        $esFalta = in_array($userId, $faltantes);
        $esDescanso = in_array($userId, $descansantes);

        // Verificar tipo de registro
        $esAsistenciaDirecta = $esAsistencia && !$esFalta && !$esDescanso;
        $esDescansoReal = $esDescanso;
        $esVacacion = in_array($fecha, $vacacionesPorUsuario);
        $esPermisoConGoce = isset($permisosPorUsuario[$fecha]) && $permisosPorUsuario[$fecha]['con_goce'];
        $esFaltaJustificada = !empty($faltasJustificadasData[$fecha]);

        // Sumar minutos de retardo
        $totalMinutosRetardo += $retardosPorUsuario[$fecha] ?? 0;

        // Verificar si el día es "asistencia o descanso puro"
        if ($esAsistenciaDirecta || $esDescansoReal) {
            $diasAsistenciaODescanso++;
        } elseif ($esVacacion || $esPermisoConGoce || $esFaltaJustificada) {
            // Estos también se consideran "buenos", no rompen la regla
            $diasAsistenciaODescanso++;
        }
        // Faltas injustificadas (F) o cualquier otro tipo rompen la regla
    }

    // Determinar si aplica bono
    $aplicaBonoAsistencia = ($diasAsistenciaODescanso === $totalDias) && ($totalDias > 0);
    $aplicaBonoPuntualidad = $aplicaBonoAsistencia && $totalMinutosRetardo === 0;

    // Calcular subtotal base (usado para calcular los bonos)
    $subtotalBase = $diasPagados['total'] * $sueldoDiario;

    // Calcular montos de bonos (10% del subtotal base cada uno)
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
            'dias_restantes' => 0, // Ya no aplicamos penalización de días aquí
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

    // Obtener zona del usuario (resolver punto → zona)
    $zona = $this->resolverZonaUsuario($userId);

    if (!$zona) {
        // Zona no registrada, no aplica pago de horas extra
        return [
            'total_horas' => $totalHoras,
            'valor_hora' => 0,
            'monto' => 0,
            'desglose_diario' => $horasExtrasPorUsuario,
            'zona' => null,
            'costo_12h' => 0,
        ];
    }

    // Buscar costo de 12 horas para esta zona
    $costoModel = \App\Models\TiempoExtraCosto::where('zona', $zona)->first();

    if (!$costoModel) {
        // Zona no tiene costo registrado
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

    // Calcular valor por hora (basado en costo de 12 horas)
    $valorPorHora = $costo12Horas / 12;

    // Calcular monto total
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
 * Resuelve la zona principal (punto) a partir del punto/código del usuario
 */
protected function resolverZonaUsuario(int $userId): ?string
{
    $user = \App\Models\User::find($userId);
    if (!$user) {
        return null;
    }

    // Si el punto es código numérico, resolver a nombre
    $puntoNombre = $this->resolverPuntoNombre($user->punto) ?? $user->punto;

    // Buscar en subpuntos para obtener el punto_id (zona)
    $subpunto = \App\Models\Subpunto::where(function($q) use ($puntoNombre, $user) {
        $q->where('nombre', $puntoNombre)
          ->orWhere('codigo', (int)$user->punto);
    })->first();

    if ($subpunto) {
        // Obtener el nombre del punto/zona principal
        $punto = \App\Models\Punto::find($subpunto->punto_id);
        return $punto?->nombre;
    }

    // Si no es subpunto, podría ser un punto directo
    return $puntoNombre;
}

    /**
     * Genera un array de fechas entre inicio y fin
     */
    protected function generarFechas(string $inicio, string $fin): array
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
