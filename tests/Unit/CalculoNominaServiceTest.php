<?php

namespace Tests\Unit;

use App\Services\CalculoNominaService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CalculoNominaServiceTest extends TestCase
{
    private function calcularDias(Collection $registros, string $inicio, string $fin): array
    {
        $metodo = new ReflectionMethod(CalculoNominaService::class, 'calcularDiasPagados');
        $metodo->setAccessible(true);

        return $metodo->invoke(new CalculoNominaService(), 7, $inicio, $fin, [
            'vacacionesPorUsuario' => [],
            'permisosPorUsuario' => [],
            'faltasJustificadas' => [],
            'incapacidadesPorUsuario' => [],
            'asistenciasIndexadas' => $registros,
        ]);
    }

    public function test_conserva_varios_registros_de_una_misma_fecha(): void
    {
        $otroPunto = (object) ['elementos_enlistados' => '[3]', 'faltas' => '[]', 'descansos' => '[]'];
        $puntoEmpleado = (object) ['elementos_enlistados' => '[7]', 'faltas' => '[]', 'descansos' => '[]'];
        $indexadas = collect(['2026-07-01' => collect([$otroPunto, $puntoEmpleado])]);

        $resultado = $this->calcularDias($indexadas, '2026-07-01', '2026-07-01');

        $this->assertSame(1, $resultado['desglose']['asistencias']);
        $this->assertSame(0, $resultado['desglose']['pendientes_captura']);
    }

    public function test_dia_sin_captura_es_pendiente_y_no_descuento_automatico(): void
    {
        $resultado = $this->calcularDias(collect(), '2026-07-01', '2026-07-01');

        $this->assertSame(1, $resultado['desglose']['pendientes_captura']);
        $this->assertSame(1, $resultado['total']);
        $this->assertSame(0, $resultado['desglose']['faltas_injustificadas']);
    }
}
