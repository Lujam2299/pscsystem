<?php

namespace Tests\Feature\Custodios;

use App\Models\Misiones;
use App\Models\User;
use App\Services\CustodiosAvailabilityService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CustodiosAvailabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['modules.disabled.erp_custodios' => false]);
        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Schema::dropAllTables();
        parent::tearDown();
    }

    public function test_detects_any_mission_overlap_and_ignores_cancelled_missions(): void
    {
        $blocked = $this->agent('Escolta ocupado');
        $available = $this->agent('Escolta disponible');

        $this->mission([$blocked->id], '2026-08-01', '2026-08-10', 'Pendiente');
        $this->mission([$available->id], '2026-08-01', '2026-08-10', ' cancelada ');

        $result = $this->service()->forPeriod(
            Carbon::parse('2026-08-03')->startOfDay(),
            Carbon::parse('2026-08-05')->endOfDay(),
        )->keyBy('id');

        $this->assertTrue($result[$blocked->id]['ocupado']);
        $this->assertStringContainsString('misión #', $result[$blocked->id]['motivo']);
        $this->assertFalse($result[$available->id]['ocupado']);
    }

    public function test_approved_absences_block_but_pending_requests_do_not(): void
    {
        $vacation = $this->agent('Escolta vacaciones');
        $permission = $this->agent('Escolta permiso');
        $disability = $this->agent('Escolta incapacidad');
        $pending = $this->agent('Escolta pendiente');

        DB::table('solicitud_vacaciones')->insert([
            ['user_id' => $vacation->id, 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2026-09-05', 'estatus' => ' Aceptada '],
            ['user_id' => $pending->id, 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2026-09-05', 'estatus' => 'En Proceso'],
        ]);
        DB::table('permiso_especials')->insert([
            'user_id' => $permission->id,
            'fecha_inicio' => '2026-09-02',
            'fecha_fin' => '2026-09-03',
            'estatus' => ' aprobado ',
        ]);
        DB::table('incapacidades')->insert([
            'user_id' => $disability->id,
            'fecha_inicio' => '2026-08-30',
            'dias_incapacidad' => 5,
        ]);

        $result = $this->service()->forPeriod(
            Carbon::parse('2026-09-02')->startOfDay(),
            Carbon::parse('2026-09-03')->endOfDay(),
        )->keyBy('id');

        $this->assertSame('vacaciones', $result[$vacation->id]['motivo']);
        $this->assertSame('permiso', $result[$permission->id]['motivo']);
        $this->assertSame('incapacidad', $result[$disability->id]['motivo']);
        $this->assertFalse($result[$pending->id]['ocupado']);
    }

    public function test_editing_can_exclude_the_current_mission(): void
    {
        $agent = $this->agent('Escolta asignado');
        $mission = $this->mission([$agent->id], '2026-10-01', '2026-10-04', 'Pendiente');

        $result = $this->service()->forPeriod(
            Carbon::parse('2026-10-01')->startOfDay(),
            Carbon::parse('2026-10-04')->endOfDay(),
            $mission->id,
        )->firstWhere('id', $agent->id);

        $this->assertFalse($result['ocupado']);
    }

    public function test_server_validation_rejects_an_unavailable_agent(): void
    {
        $agent = $this->agent('Escolta ocupado');
        $this->mission([$agent->id], '2026-11-01', '2026-11-03', 'Pendiente');

        $this->expectException(ValidationException::class);

        $this->service()->assertAvailable(
            [$agent->id],
            Carbon::parse('2026-11-02')->startOfDay(),
            Carbon::parse('2026-11-02')->endOfDay(),
        );
    }

    public function test_endpoint_returns_photo_and_rejects_an_invalid_period(): void
    {
        $actor = $this->user('Operador', 'CUSTODIOS', 'Activo');
        $agent = $this->agent('Escolta con foto');

        $documentId = DB::table('documentacion_altas')->insertGetId([
            'arch_foto' => 'storage/fotos/escolta.jpg',
        ]);
        $agent->forceFill(['sol_docs_id' => $documentId])->save();

        $this->actingAs($actor)
            ->postJson(route('custodios.agentesDisponibles'), [
                'fecha_inicio' => '2026-12-01',
                'fecha_fin' => '2026-12-02',
            ])
            ->assertOk()
            ->assertJsonFragment([
                'id' => $agent->id,
                'foto_url' => 'http://localhost/storage/fotos/escolta.jpg',
                'ocupado' => false,
            ]);

        $this->actingAs($actor)
            ->postJson(route('custodios.agentesDisponibles'), [
                'fecha_inicio' => '2026-12-03',
                'fecha_fin' => '2026-12-01',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('fecha_fin');
    }

    private function service(): CustodiosAvailabilityService
    {
        return app(CustodiosAvailabilityService::class);
    }

    private function agent(string $name): User
    {
        return $this->user($name, 'Escolta', 'Activo');
    }

    private function user(string $name, string $role, string $status): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => uniqid('user', true).'@example.test',
            'password' => 'secret',
            'rol' => $role,
            'estatus' => $status,
        ]);
    }

    private function mission(array $agentIds, string $start, ?string $end, string $status): Misiones
    {
        return Misiones::query()->create([
            'agentes_id' => $agentIds,
            'fecha_inicio' => $start,
            'fecha_fin' => $end,
            'estatus' => $status,
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('rol')->nullable();
            $table->string('estatus')->nullable();
            $table->unsignedBigInteger('sol_docs_id')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('documentacion_altas', function (Blueprint $table): void {
            $table->id();
            $table->string('arch_foto')->nullable();
            $table->timestamps();
        });

        Schema::create('misiones', function (Blueprint $table): void {
            $table->id();
            $table->json('agentes_id')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('estatus')->nullable();
            $table->timestamps();
        });

        Schema::create('solicitud_vacaciones', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estatus');
            $table->timestamps();
        });

        Schema::create('permiso_especials', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estatus');
            $table->timestamps();
        });

        Schema::create('incapacidades', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('fecha_inicio');
            $table->unsignedInteger('dias_incapacidad');
            $table->timestamps();
        });
    }
}
