<?php

namespace Tests\Feature;

use App\Livewire\InspeccionesUnidades;
use App\Models\InspeccionEvidencia;
use App\Models\InspeccionUnidad;
use App\Models\Unidades;
use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Authorization\RolePermissionMap;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InspeccionesUnidadesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('rol')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('unidades', function (Blueprint $table): void {
            $table->id();
            $table->string('placas');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('estado_vehiculo')->nullable();
            $table->timestamps();
        });

        Schema::create('inspecciones_unidades', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('unidad_id');
            $table->dateTime('fecha_inspeccion');
            $table->string('tipo');
            $table->unsignedBigInteger('kilometraje')->nullable();
            $table->string('resultado');
            $table->text('observaciones')->nullable();
            $table->string('reportado_por')->nullable();
            $table->string('origen');
            $table->string('estado');
            $table->unsignedBigInteger('servicio_id')->nullable();
            $table->unsignedBigInteger('siniestro_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();
        });

        Schema::create('inspeccion_evidencias', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('inspeccion_id');
            $table->string('disk');
            $table->string('path');
            $table->string('nombre_original');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->char('sha256', 64);
            $table->unsignedSmallInteger('orden');
            $table->string('clasificacion');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('module');
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('inspeccion_evidencias');
        Schema::dropIfExists('inspecciones_unidades');
        Schema::dropIfExists('unidades');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_monitoring_role_can_view_and_manage_inspections(): void
    {
        $user = User::factory()->make(['rol' => 'Monitorista']);

        $this->assertTrue(RolePermissionMap::allows($user, Permission::INSPECTIONS_VIEW));
        $this->assertTrue(RolePermissionMap::allows($user, Permission::INSPECTIONS_MANAGE));
    }

    public function test_unrelated_role_cannot_view_or_manage_inspections(): void
    {
        $user = User::factory()->make(['rol' => 'Guardia']);

        $this->assertFalse(RolePermissionMap::allows($user, Permission::INSPECTIONS_VIEW));
        $this->assertFalse(RolePermissionMap::allows($user, Permission::INSPECTIONS_MANAGE));
    }

    public function test_monitorist_can_register_an_inspection_with_private_evidence(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['rol' => 'Monitorista']);
        $unidad = Unidades::create([
            'placas' => 'PM7547C',
            'marca' => 'Nissan',
            'modelo' => 'NP300',
        ]);

        $this->actingAs($user);

        Livewire::test(InspeccionesUnidades::class)
            ->call('mostrarAlta')
            ->set('form.unidad_id', (string) $unidad->id)
            ->set('form.fecha_inspeccion', '2026-08-31T10:08')
            ->set('form.tipo', 'cambio_turno')
            ->set('form.kilometraje', '2176')
            ->set('form.resultado', 'sin_novedad')
            ->set('form.reportado_por', 'SUP Eliseo Toluca')
            ->set('evidencias', [UploadedFile::fake()->image('unidad.jpg', 900, 1200)])
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertRedirect();

        $inspeccion = InspeccionUnidad::query()->firstOrFail();
        $evidencia = InspeccionEvidencia::query()->firstOrFail();

        $this->assertSame($unidad->id, $inspeccion->unidad_id);
        $this->assertSame(2176, $inspeccion->kilometraje);
        $this->assertSame('local', $evidencia->disk);
        $this->assertSame(64, strlen($evidencia->sha256));
        Storage::disk('local')->assertExists($evidencia->path);
    }

    public function test_new_inspection_only_lists_active_units_with_usable_plates(): void
    {
        $user = User::factory()->create(['rol' => 'Monitorista']);

        Unidades::create([
            'placas' => '28S555',
            'marca' => 'Toyota',
            'modelo' => 'Prius',
            'estado_vehiculo' => 'Activa',
        ]);
        Unidades::create([
            'placas' => 'PM7547C',
            'marca' => 'Nissan',
            'modelo' => 'NP300',
            'estado_vehiculo' => 'Inactiva',
        ]);
        Unidades::create([
            'placas' => '02/10/2024',
            'marca' => 'Registro',
            'modelo' => 'Histórico',
            'estado_vehiculo' => 'Activa',
        ]);

        $this->actingAs($user);

        Livewire::test(InspeccionesUnidades::class)
            ->call('mostrarAlta')
            ->assertSee('28S555')
            ->assertSee('PM7547C')
            ->assertDontSee('02/10/2024')
            ->assertViewHas('unidadesDisponibles', function ($unidades): bool {
                return $unidades->pluck('placas')->all() === ['28S555'];
            })
            ->assertViewHas('unidadesFiltro', function ($unidades): bool {
                return $unidades->pluck('placas')->all() === ['28S555', 'PM7547C'];
            });
    }

    public function test_private_evidence_requires_inspection_permission(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('monitoreo/inspecciones/test.jpg', 'image-content');

        $unidad = Unidades::create(['placas' => 'PM7547C']);
        $inspeccion = InspeccionUnidad::create([
            'unidad_id' => $unidad->id,
            'fecha_inspeccion' => now(),
            'tipo' => 'revision',
            'resultado' => 'sin_novedad',
            'origen' => 'manual',
            'estado' => 'validada',
        ]);
        $evidencia = $inspeccion->evidencias()->create([
            'disk' => 'local',
            'path' => 'monitoreo/inspecciones/test.jpg',
            'nombre_original' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 13,
            'sha256' => hash('sha256', 'image-content'),
            'orden' => 1,
            'clasificacion' => 'general',
        ]);

        $monitorista = User::factory()->create(['rol' => 'Monitorista']);
        $guardia = User::factory()->create(['rol' => 'Guardia']);

        $this->actingAs($monitorista)
            ->get(route('inspecciones.evidencias.show', $evidencia))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->actingAs($guardia)
            ->get(route('inspecciones.evidencias.show', $evidencia))
            ->assertForbidden();
    }
}
