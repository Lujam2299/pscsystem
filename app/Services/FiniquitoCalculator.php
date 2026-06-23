<?php

namespace App\Services;

use App\Models\SolicitudBajas;
use App\Models\SolicitudVacaciones;
use Carbon\CarbonImmutable;
use DomainException;

class FiniquitoCalculator
{
    public const VERSION = '2026.1';

    /**
     * Calcula el finiquito de una renuncia usando exclusivamente datos del servidor.
     */
    public function calculate(SolicitudBajas $baja): array
    {
        $baja->loadMissing('user.solicitudAlta');

        if ($baja->por !== 'Renuncia') {
            throw new DomainException('El cálculo de finiquito sólo está disponible para bajas por renuncia.');
        }

        $user = $baja->user;
        $alta = $user?->solicitudAlta;

        if (!$user || !$alta || !$user->fecha_ingreso || !$baja->fecha_baja || !$alta->sd) {
            throw new DomainException('La baja no cuenta con fecha de ingreso, fecha de baja o salario diario.');
        }

        $ingreso = CarbonImmutable::parse($user->fecha_ingreso)->startOfDay();
        $fechaBaja = CarbonImmutable::parse($baja->fecha_baja)->startOfDay();

        if ($fechaBaja->lt($ingreso)) {
            throw new DomainException('La fecha de baja no puede ser anterior a la fecha de ingreso.');
        }

        $ultimaAsistencia = $baja->ultima_asistencia
            ? CarbonImmutable::parse($baja->ultima_asistencia)->startOfDay()
            : $fechaBaja;
        $ultimaAsistencia = $ultimaAsistencia->min($fechaBaja)->max($ingreso);
        $salarioDiario = round((float) $alta->sd, 2);

        $salario = $this->salaryForCurrentFortnight($fechaBaja, $ultimaAsistencia, $salarioDiario);
        $vacaciones = $this->vacationAccrual($baja, $ingreso, $fechaBaja, $salarioDiario);
        $aguinaldo = $this->aguinaldoAccrual($ingreso, $ultimaAsistencia, $salarioDiario);
        $deduccion = max(0, round((float) ($baja->descuento ?? 0), 2));

        $subtotal = round(
            $salario['gross_amount']
            + $vacaciones['amount']
            + $vacaciones['premium_amount']
            + $aguinaldo['amount'],
            2
        );
        $total = round($subtotal - $salario['non_worked_deduction'] - $deduccion, 2);

        return [
            'version' => self::VERSION,
            'calculated_at' => now('America/Mexico_City')->toIso8601String(),
            'employee' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'entry_date' => $ingreso->toDateString(),
                'termination_date' => $fechaBaja->toDateString(),
                'last_attendance_date' => $ultimaAsistencia->toDateString(),
                'daily_salary' => $salarioDiario,
            ],
            'salary' => $salario,
            'vacation' => $vacaciones,
            'aguinaldo' => $aguinaldo,
            'deductions' => ['general' => $deduccion],
            'subtotal' => $subtotal,
            'total' => $total,
        ];
    }

    private function salaryForCurrentFortnight(
        CarbonImmutable $fechaBaja,
        CarbonImmutable $ultimaAsistencia,
        float $salarioDiario
    ): array {
        $inicioPeriodo = $fechaBaja->day <= 15
            ? $fechaBaja->startOfMonth()
            : $fechaBaja->setDay(16);

        $diasPeriodo = (int) $inicioPeriodo->diffInDays($fechaBaja) + 1;
        $diasNoLaborados = $ultimaAsistencia->lt($inicioPeriodo)
            ? $diasPeriodo
            : (int) $ultimaAsistencia->diffInDays($fechaBaja);

        return [
            'period_start' => $inicioPeriodo->toDateString(),
            'scheduled_days' => $diasPeriodo,
            'worked_days' => max(0, $diasPeriodo - $diasNoLaborados),
            'non_worked_days' => $diasNoLaborados,
            'gross_amount' => round($diasPeriodo * $salarioDiario, 2),
            'non_worked_deduction' => round($diasNoLaborados * $salarioDiario, 2),
            'net_amount' => round(max(0, $diasPeriodo - $diasNoLaborados) * $salarioDiario, 2),
        ];
    }

    private function vacationAccrual(
        SolicitudBajas $baja,
        CarbonImmutable $ingreso,
        CarbonImmutable $fechaBaja,
        float $salarioDiario
    ): array {
        $aniosCompletos = (int) floor($ingreso->diffInYears($fechaBaja));
        $inicioPeriodo = $ingreso->addYears($aniosCompletos);
        $finPeriodo = $inicioPeriodo->addYear();
        $diasPeriodo = max(1, (int) $inicioPeriodo->diffInDays($finPeriodo));
        $diasTranscurridos = (int) $inicioPeriodo->diffInDays($fechaBaja);

        $derechoVencido = $aniosCompletos > 0 ? $this->vacationDaysForYear($aniosCompletos) : 0;
        $derechoSiguiente = $this->vacationDaysForYear($aniosCompletos + 1);

        $diasUtilizados = SolicitudVacaciones::query()
            ->where('user_id', $baja->user_id)
            ->where('estatus', 'Aceptada')
            ->whereDate('fecha_inicio', '>=', $inicioPeriodo->toDateString())
            ->whereDate('fecha_inicio', '<=', $fechaBaja->toDateString())
            ->sum('dias_solicitados');

        $diasVencidosPendientes = max(0, $derechoVencido - (float) $diasUtilizados);
        $diasProporcionales = round($derechoSiguiente * $diasTranscurridos / $diasPeriodo, 4);
        $diasUsadosContraProporcional = max(0, (float) $diasUtilizados - $derechoVencido);
        $diasProporcionalesPendientes = max(0, $diasProporcionales - $diasUsadosContraProporcional);
        $diasAPagar = round($diasVencidosPendientes + $diasProporcionalesPendientes, 4);
        $monto = round($diasAPagar * $salarioDiario, 2);

        return [
            'service_years_completed' => $aniosCompletos,
            'period_start' => $inicioPeriodo->toDateString(),
            'period_end' => $finPeriodo->toDateString(),
            'period_days' => $diasPeriodo,
            'elapsed_days' => $diasTranscurridos,
            'vested_entitlement_days' => $derechoVencido,
            'next_entitlement_days' => $derechoSiguiente,
            'used_days' => (float) $diasUtilizados,
            'unused_vested_days' => round($diasVencidosPendientes, 4),
            'proportional_days' => $diasProporcionales,
            'proportional_payable_days' => round($diasProporcionalesPendientes, 4),
            'payable_days' => $diasAPagar,
            'amount' => $monto,
            'premium_rate' => 0.25,
            'premium_amount' => round($monto * 0.25, 2),
        ];
    }

    private function aguinaldoAccrual(
        CarbonImmutable $ingreso,
        CarbonImmutable $ultimaAsistencia,
        float $salarioDiario
    ): array {
        $inicio = $ingreso->max($ultimaAsistencia->startOfYear());
        $dias = (int) $inicio->diffInDays($ultimaAsistencia) + 1;
        $diasProporcionales = round(15 * $dias / $ultimaAsistencia->daysInYear, 4);

        return [
            'period_start' => $inicio->toDateString(),
            'worked_days' => $dias,
            'annual_entitlement_days' => 15,
            'year_days' => $ultimaAsistencia->daysInYear,
            'proportional_days' => $diasProporcionales,
            'amount' => round($diasProporcionales * $salarioDiario, 2),
        ];
    }

    public function vacationDaysForYear(int $serviceYear): int
    {
        if ($serviceYear <= 5) {
            return 10 + ($serviceYear * 2);
        }

        return 22 + (int) (floor(($serviceYear - 6) / 5) * 2);
    }
}
