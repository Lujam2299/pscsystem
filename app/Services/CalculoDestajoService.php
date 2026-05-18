<?php

namespace App\Services;

use App\Models\User;
use App\Models\Sueldo;
use Carbon\Carbon;

class CalculoDestajoService
{
    /**
     * Calcula el destajo basado en la compensación quincenal (15 días)
     * Fórmula: (Compensación / 15) * (Asistencias + Descansos)
     */
    public function calcularDestajo(
        User $user,
        string $fechaInicio,
        string $fechaFin,
        array $datosAsistencias
    ): array {
        // 1. Obtener la tarifa desde el campo 'compensacion' de la tabla sueldos
        $sueldo = $this->obtenerSueldoUsuario($user);

        if (!$sueldo || !$sueldo->compensacion) {
            return [
                'success' => false,
                'error' => 'Sin compensación configurada',
                'dias_laborados' => 0,
                'tarifa_diaria' => 0,
                'total_monto' => 0,
                'conteos' => [],
                'desglose_diario' => []
            ];
        }

        $compensacionQuincenal = floatval($sueldo->compensacion);
        $tarifaDiaria = $compensacionQuincenal / 15;

        // 2. Calcular días efectivos (Asistencias + Descansos)
        $diasData = $this->calcularDiasEfectivos(
            $user->id,
            $fechaInicio,
            $fechaFin,
            $datosAsistencias
        );

        $totalDiasLaborados = $diasData['total']; // A + D
        $totalMonto = $totalDiasLaborados * $tarifaDiaria;

        return [
            'success' => true,
            'user_id' => $user->id,
            'dias_laborados' => $totalDiasLaborados,
            'tarifa_diaria' => round($tarifaDiaria, 2),
            'total_monto' => round($totalMonto, 2),
            'conteos' => $diasData['conteos'], // Para mostrar columnas de Faltas, Incap, etc.
            'desglose_diario' => $diasData['desglose_diario'] // Para pintar la cuadrícula
        ];
    }

    protected function calcularDiasEfectivos(
        int $userId,
        string $fechaInicio,
        string $fechaFin,
        array $datosAsistencias
    ): array {
        $fechas = $this->generarFechas($fechaInicio, $fechaFin);

        $vacacionesPorUsuario = $datosAsistencias['vacacionesPorUsuario'][$userId] ?? [];
        $permisosPorUsuario = $datosAsistencias['permisosPorUsuario'][$userId] ?? [];
        $asistenciasIndexadas = $datosAsistencias['asistenciasIndexadas'];
        $incapacidadesDelUsuario = $datosAsistencias['incapacidadesPorUsuario'][$userId] ?? [];

        $totalDiasLaborados = 0;
        $desgloseDiario = [];

        // Contadores para las columnas nuevas
        $conteos = [
            'asistencias' => 0,
            'descansos' => 0,
            'faltas' => 0,
            'incapacidades' => 0,
            'vacaciones' => 0,
            'permisos_cg' => 0,
            'permisos_sg' => 0,
        ];

        foreach ($fechas as $fecha) {
            $codigoDia = ''; // Código visual para la tabla (A, D, F, V, I, etc.)
            $cuentaParaDestajo = 0;

            // 1. Prioridad: Incapacidad
            if (in_array($fecha, $incapacidadesDelUsuario)) {
                $codigoDia = 'I';
                $conteos['incapacidades']++;
            }
            // 2. Permiso Especial
            elseif (isset($permisosPorUsuario[$fecha])) {
                $permiso = $permisosPorUsuario[$fecha];
                if ((int)$permiso['con_goce'] === 1) {
                    $codigoDia = 'PE-CG';
                    $conteos['permisos_cg']++;
                    // ¿Cuenta para destajo? Usualmente NO, pero si quieres que sí, cambia a 1.
                    $cuentaParaDestajo = 0;
                } else {
                    $codigoDia = 'PE-SG';
                    $conteos['permisos_sg']++;
                    $cuentaParaDestajo = 0;
                }
            }
            // 3. Vacaciones
            elseif (in_array($fecha, $vacacionesPorUsuario)) {
                $codigoDia = 'V';
                $conteos['vacaciones']++;
                $cuentaParaDestajo = 0; // Vacaciones no suelen generar destajo (producción)
            }
            // 4. Lógica de Asistencia/Falta/Descanso desde la tabla Asistencias
            else {
                $asistencia = $asistenciasIndexadas->get($fecha);

                if ($asistencia) {
                    $enlistados = json_decode($asistencia->elementos_enlistados, true) ?? [];
                    $faltantes = json_decode($asistencia->faltas, true) ?? [];
                    $descansantes = json_decode($asistencia->descansos, true) ?? [];

                    if (in_array($userId, $descansantes)) {
                        $codigoDia = 'D';
                        $conteos['descansos']++;
                        $cuentaParaDestajo = 1; // ✅ DESCANSO CUENTA COMO LABORADO PARA DESTAJO
                    } elseif (in_array($userId, $enlistados)) {
                        $codigoDia = 'A';
                        $conteos['asistencias']++;
                        $cuentaParaDestajo = 1; // ✅ ASISTENCIA CUENTA
                    } elseif (in_array($userId, $faltantes)) {
                        $codigoDia = 'F';
                        $conteos['faltas']++;
                        $cuentaParaDestajo = 0;
                    } else {
                        $codigoDia = ''; // Sin registro
                    }
                } else {
                    $codigoDia = ''; // No hay registro de asistencia ese día
                }
            }

            $totalDiasLaborados += $cuentaParaDestajo;
            $desgloseDiario[$fecha] = $codigoDia;
        }

        return [
            'total' => $totalDiasLaborados,
            'conteos' => $conteos,
            'desglose_diario' => $desgloseDiario
        ];
    }

    protected function obtenerSueldoUsuario(User $user): ?object
    {
        $puntoNombre = $this->resolverPuntoNombre($user->punto) ?? $user->punto;

        // Buscar por Puesto y Punto
        $sueldo = Sueldo::where('puesto', $user->rol)
            ->where('punto', $puntoNombre)
            ->first();

        if ($sueldo) return $sueldo;

        // Fallback solo por punto
        $sueldo = Sueldo::where('punto', $puntoNombre)->first();

        return $sueldo;
    }

    protected function resolverPuntoNombre(string $valor): ?string
    {
        if (preg_match('/^\d+$/', $valor)) {
            $subpunto = \App\Models\Subpunto::where('codigo', (int)$valor)->first();
            if ($subpunto) return $subpunto->nombre;
        }

        $subpunto = \App\Models\Subpunto::whereRaw('LOWER(nombre) = LOWER(?)', [$valor])->first();
        return $subpunto?->nombre;
    }

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
}
