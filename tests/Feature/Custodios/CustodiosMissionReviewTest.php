<?php

namespace Tests\Feature\Custodios;

use App\Models\AuditLog;
use App\Models\MisionCierreOperativo;
use App\Models\Misiones;
use App\Models\User;
use App\Support\Custodios\MissionStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustodiosMissionReviewTest extends TestCase
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

    public function test_no_permite_marcar_lista_para_facturar_si_faltan_cierres(): void
    {
        $actor = $this->user('CUSTODIOS');
        $mission = $this->mission(MissionStatus::REPORTED, [11]);

        $this->actingAs($actor)
            ->from(route('misiones.reporte-operativo.show', $mission))
            ->patch(route('misiones.revision.update', $mission), [
                'revision_estado' => 'Lista para facturar',
                'revision_observaciones' => 'Todo correcto.',
            ])
            ->assertRedirect(route('misiones.reporte-operativo.show', $mission))
            ->assertSessionHasErrors('revision_estado');

        $this->assertSame('Pendiente de revisión', $mission->refresh()->revision_estado);
    }

    public function test_permite_marcar_lista_para_facturar_si_cumple_minimos(): void
    {
        $actor = $this->user('ADMINISTRADOR');
        $agent = $this->user('ESCOLTA');
        $mission = $this->mission(MissionStatus::REPORTED, [$agent->id]);

        MisionCierreOperativo::query()->create([
            'mision_id' => $mission->id,
            'user_id' => $agent->id,
            'fecha' => '2026-07-08',
            'resumen' => 'Operación validada.',
        ]);

        $this->actingAs($actor)
            ->from(route('misiones.reporte-operativo.show', $mission))
            ->patch(route('misiones.revision.update', $mission), [
                'revision_estado' => 'Lista para facturar',
                'revision_observaciones' => 'Lista para prefacturación.',
            ])
            ->assertRedirect(route('misiones.reporte-operativo.show', $mission))
            ->assertSessionHas('success');

        $mission->refresh();
        $this->assertSame('Lista para facturar', $mission->revision_estado);
        $this->assertSame($actor->id, $mission->revision_user_id);
        $this->assertNotNull($mission->revision_at);
        $log = AuditLog::query()->where('action', 'Revisión administrativa actualizada')->firstOrFail();
        $this->assertSame('Pendiente de revisión', $log->old_values['revision_estado']);
        $this->assertSame('Lista para facturar', $log->new_values['revision_estado']);
    }

    public function test_observada_requiere_observaciones(): void
    {
        $actor = $this->user('CUSTODIOS');
        $mission = $this->mission(MissionStatus::REPORTED, [11]);

        $this->actingAs($actor)
            ->from(route('misiones.reporte-operativo.show', $mission))
            ->patch(route('misiones.revision.update', $mission), [
                'revision_estado' => 'Observada / requiere corrección',
                'revision_observaciones' => '',
            ])
            ->assertRedirect(route('misiones.reporte-operativo.show', $mission))
            ->assertSessionHasErrors('revision_observaciones');
    }

    private function user(string $role): User
    {
        return User::query()->create([
            'name' => 'Usuario '.$role,
            'email' => uniqid('review', true).'@example.test',
            'password' => 'secret',
            'rol' => $role,
            'estatus' => 'Activo',
        ]);
    }

    private function mission(string $status, array $agents): Misiones
    {
        return Misiones::query()->create([
            'agentes_id' => $agents,
            'fecha_inicio' => '2026-07-08',
            'fecha_fin' => '2026-07-09',
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
            $table->string('revision_estado')->default('Pendiente de revisión');
            $table->text('revision_observaciones')->nullable();
            $table->unsignedBigInteger('revision_user_id')->nullable();
            $table->timestamp('revision_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mision_cierres_operativos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('mision_id');
            $table->unsignedBigInteger('user_id');
            $table->date('fecha');
            $table->text('resumen');
            $table->text('novedades')->nullable();
            $table->text('incidencias')->nullable();
            $table->text('pendientes')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('client_operation_id', 100)->nullable();
            $table->timestamp('client_created_at')->nullable();
            $table->timestamps();
        });

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
