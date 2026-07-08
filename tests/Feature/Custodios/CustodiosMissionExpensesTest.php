<?php

namespace Tests\Feature\Custodios;

use App\Models\Gastos;
use App\Models\Misiones;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustodiosMissionExpensesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('misiones', function (Blueprint $table): void {
            $table->id();
            $table->json('agentes_id')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('estatus')->nullable();
            $table->timestamps();
        });

        Schema::create('gastos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('mision_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->decimal('Monto', 10, 2);
            $table->date('Fecha');
            $table->time('Hora');
            $table->string('Evidencia')->nullable();
            $table->string('Tipo');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropAllTables();
        parent::tearDown();
    }

    public function test_explicit_expenses_do_not_leak_between_overlapping_missions(): void
    {
        $missionA = $this->mission();
        $missionB = $this->mission();

        $expenseA = $this->expense($missionA->id, 100);
        $expenseB = $this->expense($missionB->id, 200);

        $result = Gastos::query()->forMission($missionA)->pluck('id');

        $this->assertTrue($result->contains($expenseA->id));
        $this->assertFalse($result->contains($expenseB->id));
    }

    public function test_legacy_expenses_without_mission_keep_the_previous_fallback(): void
    {
        $mission = $this->mission();
        $legacy = $this->expense(null, 150);

        $this->assertTrue(
            Gastos::query()->forMission($mission)->whereKey($legacy->id)->exists()
        );
    }

    private function mission(): Misiones
    {
        return Misiones::query()->create([
            'agentes_id' => [5],
            'fecha_inicio' => '2026-07-01',
            'fecha_fin' => '2026-07-05',
            'estatus' => 'En Curso',
        ]);
    }

    private function expense(?int $missionId, int $amount): Gastos
    {
        return Gastos::query()->create([
            'mision_id' => $missionId,
            'user_id' => 5,
            'Monto' => $amount,
            'Fecha' => '2026-07-02',
            'Hora' => '10:00',
            'Tipo' => 'Viaticos',
        ]);
    }
}
