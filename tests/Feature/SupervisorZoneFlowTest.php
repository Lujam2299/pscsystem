<?php

namespace Tests\Feature;

use App\Models\SolicitudAlta;
use App\Models\User;
use App\Services\SupervisorZoneService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SupervisorZoneFlowTest extends TestCase
{
    private bool $isolated = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (! app()->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
            || config('database.connections.sqlite.url')) {
            throw new \RuntimeException('Zone tests require isolated in-memory SQLite without a database URL.');
        }
        $this->assertSame('sqlite', DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME));
        $this->isolated = true;
        $this->withoutVite();
        View::getFinder()->prependLocation(base_path('tests/Fixtures/supervisor-zone'));
        config(['modules.disabled.erp_supervisores' => true, 'logging.default' => 'null']);

        Schema::create('solicitud_altas', function (Blueprint $table): void {
            $table->id();
            foreach (['solicitante', 'nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento',
                'tipo_empleado', 'curp', 'nss', 'estado_civil', 'rfc', 'telefono', 'domicilio_calle',
                'domicilio_numero', 'domicilio_colonia', 'cp_fiscal', 'domicilio_ciudad', 'peso', 'estatura',
                'liga_rfc', 'domicilio_estado', 'infonavit', 'fonacot', 'domicilio_comprobante', 'rol', 'punto',
                'reingreso', 'empresa', 'fecha_ingreso', 'sueldo_mensual', 'email', 'tipo_periodo', 'banco',
                'cuenta_bancaria', 'status', 'observaciones', 'ultima_edicion', 'departamento', 'sol_docs_id'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        $this->migration()->up();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            foreach (['name', 'rol', 'email', 'password', 'punto', 'empresa', 'estatus', 'fecha_ingreso'] as $field) {
                $table->string($field)->nullable();
            }
            $table->unsignedBigInteger('sol_alta_id')->nullable();
            $table->unsignedBigInteger('sol_docs_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('documentacion_altas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('solicitud_id');
            $table->string('arch_curp')->nullable();
            $table->timestamps();
        });
        Schema::create('puntos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });
        Schema::create('subpuntos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('punto_id')->nullable();
            $table->string('nombre')->nullable();
            $table->string('zona')->nullable();
        });
        Schema::create('supervisorpuntos', function (Blueprint $table): void {
            $table->unsignedBigInteger('supervisor_id');
            $table->unsignedBigInteger('subpunto_id');
            $table->unique(['supervisor_id', 'subpunto_id']);
        });
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            foreach (['actor_id', 'module', 'action', 'subject_type', 'subject_id', 'old_values', 'new_values', 'metadata', 'ip_address', 'user_agent'] as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamp('created_at')->nullable();
        });
        foreach (['A', 'A', 'B', null, '', '   '] as $zone) {
            DB::table('subpuntos')->insert(['zona' => $zone]);
        }
    }

    protected function tearDown(): void
    {
        if ($this->isolated) {
            Schema::dropAllTables();
        }
        parent::tearDown();
    }

    public static function editors(): array
    {
        return [['admin'], ['AUXILIAR RH'], ['AUXILIAR RECURSOS HUMANOS'], ['JEFA RECURSOS HUMANOS']];
    }

    private function editor(string $role = 'admin'): void
    {
        $this->actingAs((new User)->forceFill(['id' => 900, 'name' => 'Editor', 'rol' => $role, 'empresa' => 'PSC'])->setRelation('solicitudAlta', null));
    }

    private function payload(array $overrides = []): array
    {
        return array_replace(['tipo' => 'oficina', 'name' => 'Persona ficticia', 'rol' => ' supervisor ',
            'zona_supervisor' => 'A', 'empresa' => 'PSC', 'punto' => 'Base', 'rfc' => 'TEST123'], $overrides);
    }

    private function employee(): array
    {
        $solicitud = SolicitudAlta::create(['nombre' => 'Original', 'rol' => 'SUPERVISOR', 'zona_supervisor' => 'B', 'tipo_empleado' => 'oficina']);
        $user = User::create(['name' => 'Original', 'rol' => 'SUPERVISOR', 'sol_alta_id' => $solicitud->id]);
        $user->subpuntosSupervisados()->sync([3]);

        return [$solicitud, $user];
    }

    #[DataProvider('editors')]
    public function test_creation_and_documentation_assign_selected_zone(string $role): void
    {
        $this->editor($role);
        $this->get(route('rh.formAlta'))->assertOk()->assertViewHas('zonasSupervisor', fn ($zones) => $zones->all() === ['A', 'B'])->assertSee('name="zona_supervisor"', false);
        $this->post(route('rh.guardarAlta'), $this->payload())->assertSessionHasNoErrors()->assertSessionMissing('error')->assertRedirect();
        $solicitud = SolicitudAlta::firstOrFail();
        $this->assertSame('SUPERVISOR', $solicitud->rol);
        $this->assertSame('A', $solicitud->zona_supervisor);
        $this->post(route('rh.guardarArchivosAlta', $solicitud->id))->assertSessionMissing('error')->assertRedirect(route('dashboard'));
        $user = User::where('sol_alta_id', $solicitud->id)->firstOrFail();
        $this->assertSame([1, 2], $user->subpuntosSupervisados()->orderBy('subpuntos.id')->pluck('subpuntos.id')->all());
    }

    #[DataProvider('editors')]
    public function test_edit_goes_to_documents_then_user_management(string $role): void
    {
        $this->editor($role);
        [$solicitud, $user] = $this->employee();
        $this->get(route('admin.editarUsuarioForm', $user->id))->assertOk()->assertSee('name="zona_supervisor"', false)->assertViewHas('zonasSupervisor');
        $response = $this->post(route('admin.actualizarUsuario', $solicitud->id), $this->payload());
        $response->assertOk()->assertViewIs('supervisor.editarArchivosForm')->assertViewHas('flujoAdministrativo', true)
            ->assertSee(route('admin.actualizarDocumentacionUsuario', $solicitud->id), false);
        $this->assertSame('A', $solicitud->fresh()->zona_supervisor);
        $this->assertSame([1, 2], $user->subpuntosSupervisados()->orderBy('subpuntos.id')->pluck('subpuntos.id')->all());
        $this->post(route('admin.actualizarDocumentacionUsuario', $solicitud->id))->assertRedirect(route('admin.verUsuarios'));
    }

    public function test_empty_or_omitted_zone_preserves_existing_assignments(): void
    {
        $this->editor();
        [$solicitud, $user] = $this->employee();
        foreach (['', null] as $zone) {
            $payload = $this->payload(['zona_supervisor' => $zone]);
            if ($zone === null) {
                unset($payload['zona_supervisor']);
            }
            $this->post(route('admin.actualizarUsuario', $solicitud->id), $payload)->assertOk();
            $this->assertSame('B', $solicitud->fresh()->zona_supervisor);
            $this->assertSame([3], $user->subpuntosSupervisados()->pluck('subpuntos.id')->all());
        }
    }

    public function test_invalid_zone_rejects_edit_without_partial_writes(): void
    {
        $this->editor();
        [$solicitud, $user] = $this->employee();
        $this->postJson(route('admin.actualizarUsuario', $solicitud->id), $this->payload(['zona_supervisor' => 'UNKNOWN']))->assertUnprocessable()->assertJsonValidationErrors('zona_supervisor');
        $this->assertSame('Original', $solicitud->fresh()->nombre);
        $this->assertSame('Original', $user->fresh()->name);
        $this->assertSame([3], $user->subpuntosSupervisados()->pluck('subpuntos.id')->all());
    }

    public function test_sync_failure_rolls_back_saved_user_and_request(): void
    {
        $this->editor();
        [$solicitud, $user] = $this->employee();
        $this->partialMock(SupervisorZoneService::class, function ($mock): void {
            $mock->shouldReceive('sync')->once()->andThrow(new \RuntimeException('Simulated sync failure'));
        });
        $this->post(route('admin.actualizarUsuario', $solicitud->id), $this->payload())->assertRedirect()->assertSessionHas('error');
        $this->assertSame('Original', $solicitud->fresh()->nombre);
        $this->assertSame('B', $solicitud->fresh()->zona_supervisor);
        $this->assertSame('Original', $user->fresh()->name);
        $this->assertSame([3], $user->subpuntosSupervisados()->pluck('subpuntos.id')->all());
    }

    public function test_supervisor_cannot_use_administrative_saves(): void
    {
        $this->editor('SUPERVISOR');
        foreach (['admin.actualizarUsuario', 'admin.actualizarDocumentacionUsuario'] as $route) {
            $this->post(route($route, 99), $this->payload())->assertForbidden();
        }
        $this->assertDatabaseCount('solicitud_altas', 0);
    }

    public function test_migration_accepts_existing_column_without_losing_data(): void
    {
        [$solicitud] = $this->employee();
        $this->migration()->up();
        $this->assertSame('B', $solicitud->fresh()->zona_supervisor);
    }

    public function test_new_supervisor_can_omit_zone(): void
    {
        $this->editor();
        $this->post(route('rh.guardarAlta'), $this->payload(['zona_supervisor' => '']))->assertSessionMissing('error')->assertRedirect();
        $solicitud = SolicitudAlta::firstOrFail();
        $this->assertNull($solicitud->zona_supervisor);
        $this->post(route('rh.guardarArchivosAlta', $solicitud->id))->assertSessionMissing('error')->assertRedirect(route('dashboard'));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('supervisorpuntos', 0);
    }

    public function test_other_roles_ignore_zone_without_changing_role_or_assignments(): void
    {
        $this->editor();
        [$solicitud, $user] = $this->employee();
        $this->post(route('admin.actualizarUsuario', $solicitud->id), $this->payload(['rol' => 'Guardia Operativo', 'zona_supervisor' => 'A']))->assertOk();
        $this->assertSame('Guardia Operativo', $user->fresh()->rol);
        $this->assertSame([3], $user->subpuntosSupervisados()->pluck('subpuntos.id')->all());
    }

    public function test_repeated_zone_assignment_does_not_duplicate_subpoints(): void
    {
        $this->editor();
        [$solicitud, $user] = $this->employee();
        for ($i = 0; $i < 2; $i++) {
            $this->post(route('admin.actualizarUsuario', $solicitud->id), $this->payload())->assertOk();
        }
        $this->assertDatabaseCount('supervisorpuntos', 2);
        $this->assertSame([1, 2], $user->subpuntosSupervisados()->orderBy('subpuntos.id')->pluck('subpuntos.id')->all());
    }

    public function test_creation_rejects_unknown_zone_without_creating_request(): void
    {
        $this->editor();
        $this->postJson(route('rh.guardarAlta'), $this->payload(['zona_supervisor' => 'UNKNOWN']))->assertUnprocessable()->assertJsonValidationErrors('zona_supervisor');
        $this->assertDatabaseCount('solicitud_altas', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_documentation_failure_rolls_back_user_document_record_and_assignments(): void
    {
        $this->editor();
        $this->post(route('rh.guardarAlta'), $this->payload())->assertRedirect();
        $solicitud = SolicitudAlta::firstOrFail();
        $this->partialMock(SupervisorZoneService::class, function ($mock): void {
            $mock->shouldReceive('sync')->once()->andThrow(new \RuntimeException('Simulated sync failure'));
        });
        $this->post(route('rh.guardarArchivosAlta', $solicitud->id))->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('documentacion_altas', 0);
        $this->assertDatabaseCount('supervisorpuntos', 0);
    }

    public function test_accepting_existing_request_assigns_zone(): void
    {
        $this->editor('AUXILIAR RH');
        $solicitud = SolicitudAlta::create(['nombre' => 'Persona', 'rol' => 'SUPERVISOR', 'zona_supervisor' => 'A', 'rfc' => 'TEST', 'status' => 'En Proceso']);
        DB::table('documentacion_altas')->insert(['solicitud_id' => $solicitud->id]);
        $this->post(route('rh.aceptarSolicitud', $solicitud->id))->assertRedirect(route('rh.solicitudesAltas'));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('supervisorpuntos', 2);
    }

    public function test_reversing_migration_never_silently_drops_existing_zone_data(): void
    {
        [$solicitud] = $this->employee();
        try {
            $this->migration()->down();
            $this->fail('Zone removal must require explicit review.');
        } catch (\RuntimeException $e) {
            $this->assertSame('B', $solicitud->fresh()->zona_supervisor);
        }
    }

    public function test_document_upload_keeps_administrative_redirect_and_zone(): void
    {
        $this->editor('JEFA RECURSOS HUMANOS');
        Storage::fake('public');
        [$solicitud, $user] = $this->employee();
        $this->post(route('admin.actualizarUsuario', $solicitud->id), $this->payload())->assertOk();
        $this->post(route('admin.actualizarDocumentacionUsuario', $solicitud->id), [
            'arch_curp' => UploadedFile::fake()->create('documento.pdf', 1, 'application/pdf'),
        ])->assertRedirect(route('admin.verUsuarios'));
        Storage::disk('public')->assertExists('solicitudesAltas/'.$solicitud->id.'/arch_curp.pdf');
        $this->assertDatabaseHas('documentacion_altas', [
            'solicitud_id' => $solicitud->id,
            'arch_curp' => 'storage/solicitudesAltas/'.$solicitud->id.'/arch_curp.pdf',
        ]);
        $this->assertSame('A', $solicitud->fresh()->zona_supervisor);
        $this->assertSame([1, 2], $user->subpuntosSupervisados()->orderBy('subpuntos.id')->pluck('subpuntos.id')->all());
    }

    private function migration(): \Illuminate\Database\Migrations\Migration
    {
        return require database_path('migrations/2026_09_02_000002_add_zona_supervisor_to_solicitud_altas_table.php');
    }
}
