<?php

namespace Tests\Feature\Custodios;

use App\Models\AuditLog;
use App\Models\Misiones;
use App\Models\User;
use App\Support\Custodios\MissionStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustodiosMissionStatusTest extends TestCase
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

    public function test_legacy_statuses_are_normalized_without_a_data_migration(): void
    {
        $this->assertSame(MissionStatus::IN_PROGRESS, MissionStatus::normalize(' Activa '));
        $this->assertSame(MissionStatus::IN_PROGRESS, MissionStatus::normalize('en curso'));
        $this->assertSame(MissionStatus::FINISHED, MissionStatus::normalize('Completada'));
        $this->assertSame(MissionStatus::FINISHED, MissionStatus::normalize('Terminada'));
        $this->assertSame(MissionStatus::FINISHED, MissionStatus::normalize('Finalizada'));
        $this->assertSame(MissionStatus::CANCELLED, MissionStatus::normalize(' cancelada '));
        $this->assertSame(MissionStatus::PENDING, MissionStatus::normalize('valor desconocido'));
    }

    public function test_transition_matrix_and_terminal_states_are_enforced(): void
    {
        $this->assertSame(
            [MissionStatus::SCHEDULED, MissionStatus::CANCELLED],
            MissionStatus::transitionsFrom(MissionStatus::PENDING),
        );
        $this->assertSame(
            [MissionStatus::IN_PROGRESS, MissionStatus::CANCELLED],
            MissionStatus::transitionsFrom(MissionStatus::SCHEDULED),
        );
        $this->assertSame(
            [MissionStatus::REPORTED, MissionStatus::CANCELLED],
            MissionStatus::transitionsFrom(MissionStatus::IN_PROGRESS),
        );
        $this->assertSame(
            [MissionStatus::FINISHED, MissionStatus::IN_PROGRESS],
            MissionStatus::transitionsFrom(MissionStatus::REPORTED),
        );
        $this->assertSame([], MissionStatus::transitionsFrom(MissionStatus::FINISHED));
        $this->assertSame([], MissionStatus::transitionsFrom(MissionStatus::CANCELLED));
    }

    public function test_authorized_user_can_apply_a_valid_transition(): void
    {
        $actor = $this->user('CUSTODIOS');
        $mission = $this->mission(MissionStatus::SCHEDULED);

        $this->actingAs($actor)
            ->from(route('admin.detalleMision', $mission))
            ->patch(route('misiones.estado.update', $mission), [
                'estatus' => MissionStatus::IN_PROGRESS,
            ])
            ->assertRedirect(route('admin.detalleMision', $mission))
            ->assertSessionHas('success');

        $this->assertSame(MissionStatus::IN_PROGRESS, $mission->refresh()->estatus);
        $log = AuditLog::query()->where('action', 'Estado de misión actualizado')->firstOrFail();
        $this->assertSame(MissionStatus::SCHEDULED, $log->old_values['estatus']);
        $this->assertSame(MissionStatus::IN_PROGRESS, $log->new_values['estatus']);
    }

    public function test_invalid_transition_is_rejected_and_does_not_change_the_mission(): void
    {
        $actor = $this->user('ADMINISTRADOR');
        $mission = $this->mission(MissionStatus::SCHEDULED);

        $this->actingAs($actor)
            ->from(route('admin.detalleMision', $mission))
            ->patch(route('misiones.estado.update', $mission), [
                'estatus' => MissionStatus::FINISHED,
            ])
            ->assertRedirect(route('admin.detalleMision', $mission))
            ->assertSessionHasErrors('estatus');

        $this->assertSame(MissionStatus::SCHEDULED, $mission->refresh()->estatus);
    }

    private function user(string $role): User
    {
        return User::query()->create([
            'name' => 'Operador de custodios',
            'email' => uniqid('custodios', true).'@example.test',
            'password' => 'secret',
            'rol' => $role,
            'estatus' => 'Activo',
        ]);
    }

    private function mission(string $status): Misiones
    {
        return Misiones::query()->create([
            'agentes_id' => [],
            'fecha_inicio' => '2026-08-01',
            'fecha_fin' => '2026-08-02',
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
            $table->rememberToken();
            $table->softDeletes();
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

        $this->createAuditSchema();
    }

    private function createAuditSchema(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('module');
            $table->string('action');
            $table->nullableMorphs('subject');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }
}
