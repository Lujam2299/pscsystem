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
            \Log::warning('CALCULO_NOMINA: No se encontró sueldo para usuario', [
                'user_id' => $user->id,
                'user_puesto' => $user->puesto,
                'user_punto' => $user->punto,
            ]);
            return $this->respuestaError('No se encontró registro de sueldo para el usuario');
        }

        \Log::info('CALCULO_NOMINA: Sueldo encontrado', [
            'user_id' => $user->id,
            'sueldo_registro' => $sueldo->toArray(),
        ]);

        $sueldoDiario = floatval($sueldo->sd);

        // Calcular días pagados según concepto
        $diasPagados = $this->calcularDiasPagados(
            $user->id,
            $fechaInicio,
            $fechaFin,
            $datosAsistencias
        );

        \Log::info('CALCULO_NOMINA: Días pagados calculados', [
            'user_id' => $user->id,
            'dias_pagados' => $diasPagados,
        ]);

        // Calcular bonos
        $bonos = $this->calcularBonos(
            $user->id,
            $fechaInicio,
            $fechaFin,
            $datosAsistencias,
            $diasPagados
        );

        \Log::info('CALCULO_NOMINA: Bonos calculados', [
            'user_id' => $user->id,
            'bonos' => $bonos,
        ]);

        // Calcular horas extra
        $horasExtra = $this->calcularHorasExtra(
            $user->id,
            $fechaInicio,
            $fechaFin,
            $datosAsistencias,
            $sueldoDiario
        );

        \Log::info('CALCULO_NOMINA: Horas extra calculadas', [
            'user_id' => $user->id,
            'horas_extra' => $horasExtra,
        ]);

        // Subtotal percepciones
        $subtotal = ($diasPagados['total'] * $sueldoDiario) + $bonos['total'] + $horasExtra['monto'];

        $resultado = [
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

        \Log::info('CALCULO_NOMINA: Resultado final', [
            'user_id' => $user->id,
            'resultado' => $resultado,
        ]);

        return $resultado;
    }

    /**
 * Obtiene el registro de sueldo del usuario
 */
protected function obtenerSueldoUsuario(User $user): ?object
{
    \Log::info('CALCULO_NOMINA: Buscando sueldo para usuario', [
        'user_id' => $user->id,
        'user_rol' => $user->rol,          // ← Ahora usamos rol
        'user_punto' => $user->punto,      // ← punto sigue siendo punto
    ]);

    // Primero intenta con rol + punto tal cual
    $sueldo = Sueldo::where('puesto', $user->rol)  // ✅ rol → puesto
        ->where('punto', $user->punto)
        ->first();

    if ($sueldo) {
        \Log::info('CALCULO_NOMINA: Sueldo encontrado por rol + punto directo', [
            'user_id' => $user->id,
            'sueldo' => $sueldo->toArray(),
        ]);
        return $sueldo;
    }

    // Si no encontró, intenta mapear código → nombre usando la tabla subpuntos
    $puntoNombre = $this->resolverPuntoNombre($user->punto);

    \Log::info('CALCULO_NOMINA: Resolución de punto', [
        'user_id' => $user->id,
        'user_punto_original' => $user->punto,
        'punto_resuelto' => $puntoNombre,
    ]);

    if ($puntoNombre) {
        $sueldo = Sueldo::where('puesto', $user->rol)  // ✅ aún rol
            ->where('punto', $puntoNombre)
            ->first();

        if ($sueldo) {
            \Log::info('CALCULO_NOMINA: Sueldo encontrado por rol + punto resuelto', [
                'user_id' => $user->id,
                'sueldo' => $sueldo->toArray(),
            ]);
            return $sueldo;
        }
    }

    // CASO ESPECIAL: si rol es null o vacío, intentar buscar solo por punto (resuelto o no)
    if (empty($user->rol)) {
        \Log::warning('CALCULO_NOMINA: Usuario sin rol, buscando solo por punto', [
            'user_id' => $user->id,
            'user_punto' => $user->punto,
            'punto_resuelto' => $puntoNombre,
        ]);

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

    \Log::warning('CALCULO_NOMINA: No se encontró sueldo para usuario', [
        'user_id' => $user->id,
        'user_rol' => $user->rol,
        'user_punto' => $user->punto,
        'punto_resuelto' => $puntoNombre,
    ]);

    return null;
}

    /**
     * Resuelve el nombre del punto a partir de su código (o viceversa)
     */
    protected function resolverPuntoNombre(string $valor): ?string
    {
        \Log::info('CALCULO_NOMINA: Resolver punto nombre', ['entrada' => $valor]);

        // Caso 1: Si es un código numérico (con o sin ceros adelante)
        if (preg_match('/^\d+$/', $valor)) {
            // Convertir a int para eliminar ceros adelante
            $codigoInt = (int) $valor;

            \Log::info('CALCULO_NOMINA: Código numérico detectado', [
                'original' => $valor,
                'convertido_a_int' => $codigoInt,
            ]);

            // Buscar en Subpunto por código (como int, ya que en DB es int)
            $subpunto = \App\Models\Subpunto::where('codigo', $codigoInt)->first();

            if ($subpunto) {
                \Log::info('CALCULO_NOMINA: Subpunto encontrado por código', [
                    'codigo_buscado' => $codigoInt,
                    'nombre_resultado' => $subpunto->nombre,
                ]);
                return $subpunto->nombre;
            } else {
                \Log::warning('CALCULO_NOMINA: No se encontró subpunto por código', [
                    'codigo_buscado' => $codigoInt,
                ]);
            }
        }

        // Caso 2: Si es un nombre directo (ej. "DALTILE"), verificar si existe
        $subpuntoPorNombre = \App\Models\Subpunto::where('nombre', $valor)->first();
        if ($subpuntoPorNombre) {
            \Log::info('CALCULO_NOMINA: Subpunto encontrado por nombre directo', [
                'nombre_buscado' => $valor,
                'nombre_resultado' => $subpuntoPorNombre->nombre,
            ]);
            return $subpuntoPorNombre->nombre;
        }

        // Caso 3: Intentar buscar por nombre ignorando mayúsculas/espacios
        $subpuntoFuzzy = \App\Models\Subpunto::whereRaw('LOWER(nombre) = LOWER(?)', [$valor])->first();
        if ($subpuntoFuzzy) {
            \Log::info('CALCULO_NOMINA: Subpunto encontrado por búsqueda fuzzy', [
                'nombre_buscado' => $valor,
                'nombre_resultado' => $subpuntoFuzzy->nombre,
            ]);
            return $subpuntoFuzzy->nombre;
        }

        \Log::warning('CALCULO_NOMINA: No se pudo resolver el punto', ['entrada' => $valor]);

        return null;
    }

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

    protected function calcularBonos(
        int $userId,
        string $fechaInicio,
        string $fechaFin,
        array $datosAsistencias,
        array $diasPagados
    ): array {
        $fechas = $this->generarFechas($fechaInicio, $fechaFin);
        $asistenciasIndexadas = $datosAsistencias['asistenciasIndexadas'];
        $retardosPorUsuario = $datosAsistencias['retardosPorUsuario'][$userId] ?? [];

        $tieneFaltas = $diasPagados['desglose']['faltas_injustificadas'] > 0;
        $totalMinutosRetardo = 0;

        foreach ($fechas as $fecha) {
            $totalMinutosRetardo += $retardosPorUsuario[$fecha] ?? 0;
        }

        $bonoAsistencia = 0;
        $bonoPuntualidad = 0;
        $aplicaBonoAsistencia = false;
        $aplicaBonoPuntualidad = false;

        if (!$tieneFaltas) {
            $aplicaBonoAsistencia = true;
            $aplicaBonoPuntualidad = $totalMinutosRetardo <= self::MINUTOS_TOLERANCIA_PUNTUALIDAD;

            // Calcular montos de bonos si aplican
            if ($aplicaBonoAsistencia) {
                $bonoAsistencia = floatval($datosAsistencias['sueldo_diario'] ?? 0) * self::BONO_ASISTENCIA;
            }
            if ($aplicaBonoPuntualidad) {
                $bonoPuntualidad = floatval($datosAsistencias['sueldo_diario'] ?? 0) * self::BONO_PUNTUALIDAD;
            }
        }

        // Si hay faltas, se pierde 1 día de pago adicional
        $diasPenalizados = $tieneFaltas ? self::DIAS_PENALIZACION_FALTA : 0;

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
                'aplica' => $tieneFaltas,
                'dias_restantes' => $diasPenalizados,
            ],
            'total' => round($bonoAsistencia + $bonoPuntualidad, 2),
        ];
    }

    protected function calcularHorasExtra(
        int $userId,
        string $fechaInicio,
        string $fechaFin,
        array $datosAsistencias,
        float $sueldoDiario
    ): array {
        $horasExtrasPorUsuario = $datosAsistencias['horasExtrasPorUsuario'][$userId] ?? [];
        $totalHoras = array_sum($horasExtrasPorUsuario);

        // Valor hora normal = sueldo diario / 8 horas (jornada estándar)
        $valorHoraNormal = $sueldoDiario / 8;

        // Hora extra = 2x valor hora normal (ajustar según política de la empresa)
        $valorHoraExtra = $valorHoraNormal * 2;

        $monto = $totalHoras * $valorHoraExtra;

        return [
            'total_horas' => $totalHoras,
            'valor_hora' => round($valorHoraExtra, 2),
            'monto' => round($monto, 2),
            'desglose_diario' => $horasExtrasPorUsuario,
        ];
    }

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

    protected function respuestaError(string $mensaje): array
    {
        return [
            'success' => false,
            'error' => $mensaje,
            'subtotal_percepciones' => 0,
        ];
    }
}
