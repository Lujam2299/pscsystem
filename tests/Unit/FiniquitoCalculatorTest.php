<?php

namespace Tests\Unit;

use App\Models\SolicitudAlta;
use App\Models\SolicitudBajas;
use App\Models\User;
use App\Services\FiniquitoCalculator;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FiniquitoCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('solicitud_altas', function (Blueprint $table) {
            $table->id();
            $table->decimal('sd', 12, 2)->nullable();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('rol')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->unsignedBigInteger('sol_alta_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('solicitud_bajas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('por')->nullable();
            $table->string('estatus')->nullable();
            $table->date('ultima_asistencia')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->decimal('descuento', 12, 2)->nullable();
            $table->timestamps();
        });
        Schema::create('solicitud_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('fecha_inicio');
            $table->string('estatus');
            $table->integer('dias_solicitados');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('solicitud_vacaciones');
        Schema::dropIfExists('solicitud_bajas');
        Schema::dropIfExists('users');
        Schema::dropIfExists('solicitud_altas');
        parent::tearDown();
    }

    public function test_calcula_correctamente_el_dia_quince_y_vacaciones_por_aniversario(): void
    {
        $baja = $this->createBaja('2025-06-15', '2026-06-15');

        $resultado = app(FiniquitoCalculator::class)->calculate($baja);

        $this->assertSame(15, $resultado['salary']['scheduled_days']);
        $this->assertSame(1500.0, $resultado['salary']['gross_amount']);
        $this->assertSame(12.0, $resultado['vacation']['payable_days']);
        $this->assertSame(1200.0, $resultado['vacation']['amount']);
        $this->assertSame(300.0, $resultado['vacation']['premium_amount']);
    }

    public function test_segunda_quincena_comienza_el_dia_dieciseis(): void
    {
        $baja = $this->createBaja('2026-01-01', '2026-06-16');

        $resultado = app(FiniquitoCalculator::class)->calculate($baja);

        $this->assertSame(1, $resultado['salary']['scheduled_days']);
        $this->assertSame(100.0, $resultado['salary']['gross_amount']);
    }

    public function test_rechaza_bajas_que_no_son_renuncia(): void
    {
        $baja = $this->createBaja('2026-01-01', '2026-06-16', 'Ausentismo');

        $this->expectException(DomainException::class);
        app(FiniquitoCalculator::class)->calculate($baja);
    }

    public function test_descuenta_vacaciones_anticipadas_del_proporcional(): void
    {
        $baja = $this->createBaja('2026-01-01', '2026-07-01');
        DB::table('solicitud_vacaciones')->insert([
            'user_id' => $baja->user_id,
            'fecha_inicio' => '2026-05-01',
            'estatus' => 'Aceptada',
            'dias_solicitados' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resultado = app(FiniquitoCalculator::class)->calculate($baja->fresh());

        $this->assertSame(2.9507, $resultado['vacation']['payable_days']);
    }

    private function createBaja(string $ingreso, string $fechaBaja, string $motivo = 'Renuncia'): SolicitudBajas
    {
        $alta = new SolicitudAlta();
        $alta->forceFill(['sd' => 100])->save();

        $user = new User();
        $user->forceFill([
            'name' => 'Persona de prueba',
            'fecha_ingreso' => $ingreso,
            'sol_alta_id' => $alta->id,
        ])->save();

        $baja = new SolicitudBajas();
        $baja->forceFill([
            'user_id' => $user->id,
            'por' => $motivo,
            'estatus' => 'Aceptada',
            'ultima_asistencia' => $fechaBaja,
            'fecha_baja' => $fechaBaja,
            'descuento' => 0,
        ])->save();

        return $baja;
    }
}
