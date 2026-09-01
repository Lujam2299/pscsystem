<?php

namespace Tests\Feature;

use App\Livewire\InspeccionRecepcionBandeja;
use App\Livewire\InspeccionReporteSemanal;
use App\Livewire\InspeccionRevisionDetalle;
use App\Models\InspeccionMensaje;
use App\Models\InspeccionRevisionCaso;
use App\Models\InspeccionUnidad;
use App\Models\Unidades;
use App\Models\User;
use App\Services\InspeccionMensajeAgrupador;
use App\Services\InspeccionReporteSemanalService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InspeccionRecepcionTest extends TestCase
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
        Schema::create('inspeccion_revision_casos', function (Blueprint $table): void {
            $table->id();
            $table->string('estado')->default('pendiente');
            $table->unsignedBigInteger('unidad_sugerida_id')->nullable();
            $table->unsignedBigInteger('unidad_confirmada_id')->nullable();
            $table->unsignedBigInteger('inspeccion_id')->nullable();
            $table->json('placas_candidatas')->nullable();
            $table->unsignedTinyInteger('confianza')->default(0);
            $table->text('notas_revision')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('inspeccion_mensajes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('caso_id');
            $table->string('origen')->default('manual');
            $table->string('external_id')->nullable();
            $table->string('conversacion')->nullable();
            $table->string('remitente')->nullable();
            $table->dateTime('fecha_mensaje');
            $table->string('tipo');
            $table->text('texto')->nullable();
            $table->boolean('incluido')->default(true);
            $table->string('estado')->default('pendiente');
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
        Schema::create('inspeccion_mensaje_archivos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('mensaje_id');
            $table->string('disk');
            $table->string('path');
            $table->string('nombre_original');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->char('sha256', 64);
            $table->unsignedSmallInteger('orden');
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
        foreach (['audit_logs', 'inspeccion_mensaje_archivos', 'inspeccion_mensajes', 'inspeccion_revision_casos', 'inspeccion_evidencias', 'inspecciones_unidades', 'unidades', 'users'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_plate_sent_after_images_suggests_the_unit(): void
    {
        $unidad = Unidades::create(['placas' => 'PM7547C', 'marca' => 'Nissan']);
        $caso = InspeccionRevisionCaso::create(['estado' => 'pendiente']);
        InspeccionMensaje::create([
            'caso_id' => $caso->id,
            'fecha_mensaje' => '2026-08-31 10:00:00',
            'tipo' => 'imagenes',
            'texto' => null,
        ]);
        InspeccionMensaje::create([
            'caso_id' => $caso->id,
            'fecha_mensaje' => '2026-08-31 10:01:00',
            'tipo' => 'texto',
            'texto' => 'Corresponde a la unidad PM-7547-C',
        ]);

        app(InspeccionMensajeAgrupador::class)->analizar($caso);

        $this->assertSame('placa_sugerida', $caso->fresh()->estado);
        $this->assertSame($unidad->id, $caso->fresh()->unidad_sugerida_id);
        $this->assertSame(80, $caso->fresh()->confianza);
    }

    public function test_manual_import_stores_private_images_and_detects_a_later_plate(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['rol' => 'Monitorista']);
        $unidad = Unidades::create(['placas' => 'PM7547C', 'marca' => 'Nissan']);
        $this->actingAs($user);

        Livewire::test(InspeccionRecepcionBandeja::class)
            ->set('remitente', 'Supervisor')
            ->set('fechaBase', '2026-08-31T10:00')
            ->set('textoAnterior', 'Envío evidencia de la unidad')
            ->set('textoPosterior', 'La placa es PM-7547-C')
            ->set('imagenes', [UploadedFile::fake()->image('unidad.jpg', 900, 1200)])
            ->call('importar')
            ->assertHasNoErrors()
            ->assertRedirect();

        $caso = InspeccionRevisionCaso::with('mensajes.archivos')->firstOrFail();
        $this->assertSame('placa_sugerida', $caso->estado);
        $this->assertSame($unidad->id, $caso->unidad_sugerida_id);
        $this->assertCount(3, $caso->mensajes);
        Storage::disk('local')->assertExists($caso->mensajes->pluck('archivos')->flatten()->first()->path);
    }

    public function test_two_detected_plates_leave_the_case_ambiguous(): void
    {
        Unidades::create(['placas' => 'PM7547C']);
        Unidades::create(['placas' => '28S555']);
        $caso = InspeccionRevisionCaso::create(['estado' => 'pendiente']);
        InspeccionMensaje::create([
            'caso_id' => $caso->id,
            'fecha_mensaje' => now(),
            'tipo' => 'texto',
            'texto' => 'Fotos de PM7547C y 28S555',
        ]);

        app(InspeccionMensajeAgrupador::class)->analizar($caso);

        $this->assertSame('ambiguo', $caso->fresh()->estado);
        $this->assertNull($caso->fresh()->unidad_sugerida_id);
        $this->assertCount(2, $caso->fresh()->placas_candidatas);
    }

    public function test_human_confirmation_creates_one_inspection_and_consolidates_files(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('monitoreo/inspecciones-temporales/caso-1/test.jpg', 'image-content');

        $user = User::factory()->create(['rol' => 'Monitorista']);
        $unidad = Unidades::create(['placas' => 'PM7547C', 'marca' => 'Nissan']);
        $caso = InspeccionRevisionCaso::create(['estado' => 'placa_sugerida', 'unidad_sugerida_id' => $unidad->id]);
        $mensaje = InspeccionMensaje::create([
            'caso_id' => $caso->id,
            'fecha_mensaje' => '2026-08-31 10:00:00',
            'tipo' => 'imagenes',
            'texto' => 'PM7547C sin novedad',
            'remitente' => 'Supervisor',
        ]);
        $archivo = $mensaje->archivos()->create([
            'disk' => 'local',
            'path' => 'monitoreo/inspecciones-temporales/caso-1/test.jpg',
            'nombre_original' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 13,
            'sha256' => hash('sha256', 'image-content'),
            'orden' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(InspeccionRevisionDetalle::class, ['caso' => $caso])
            ->set('unidadId', (string) $unidad->id)
            ->set('resultado', 'sin_novedad')
            ->call('confirmar')
            ->assertHasNoErrors()
            ->assertRedirect();

        $caso->refresh();
        $archivo->refresh();
        $this->assertSame('confirmado', $caso->estado);
        $this->assertNotNull($caso->inspeccion_id);
        $this->assertDatabaseCount('inspecciones_unidades', 1);
        $this->assertDatabaseCount('inspeccion_evidencias', 1);
        Storage::disk('local')->assertMissing('monitoreo/inspecciones-temporales/caso-1/test.jpg');
        Storage::disk('local')->assertExists($archivo->path);
    }

    public function test_weekly_report_only_includes_confirmed_cases_by_first_evidence_date(): void
    {
        $user = User::factory()->create(['rol' => 'Monitorista']);
        $unidad = Unidades::create(['placas' => '28S555', 'marca' => 'Toyota', 'modelo' => '2023']);
        $inspeccion = InspeccionUnidad::create([
            'unidad_id' => $unidad->id,
            'fecha_inspeccion' => '2026-08-30 23:59:00',
            'tipo' => 'revision',
            'resultado' => 'sin_novedad',
            'origen' => 'bandeja_revision',
            'estado' => 'confirmada',
        ]);
        $inspeccion->evidencias()->create([
            'disk' => 'local',
            'path' => 'monitoreo/inspecciones/1/evidencia.jpg',
            'nombre_original' => 'evidencia.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 100,
            'sha256' => str_repeat('a', 64),
            'orden' => 1,
            'clasificacion' => 'general',
        ]);

        $confirmado = InspeccionRevisionCaso::create([
            'estado' => 'confirmado',
            'unidad_confirmada_id' => $unidad->id,
            'inspeccion_id' => $inspeccion->id,
            'reviewed_by' => $user->id,
            'confirmed_at' => '2026-09-01 08:00:00',
        ]);
        InspeccionMensaje::create([
            'caso_id' => $confirmado->id,
            'fecha_mensaje' => '2026-08-30 23:59:00',
            'tipo' => 'imagenes',
            'incluido' => true,
        ]);
        InspeccionMensaje::create([
            'caso_id' => $confirmado->id,
            'fecha_mensaje' => '2026-09-01 08:00:00',
            'tipo' => 'texto',
            'texto' => 'Placa 28S555',
            'incluido' => true,
        ]);

        $pendiente = InspeccionRevisionCaso::create(['estado' => 'pendiente']);
        InspeccionMensaje::create([
            'caso_id' => $pendiente->id,
            'fecha_mensaje' => '2026-08-29 10:00:00',
            'tipo' => 'imagenes',
            'incluido' => true,
        ]);

        $reporte = app(InspeccionReporteSemanalService::class)->generar('2026-08-26');

        $this->assertSame('2026-08-24', $reporte['inicio']->toDateString());
        $this->assertSame('2026-08-30', $reporte['fin']->toDateString());
        $this->assertSame(1, $reporte['total_casos']);
        $this->assertSame(1, $reporte['total_evidencias']);
        $this->assertSame(1, $reporte['total_unidades']);
        $this->assertSame($confirmado->id, $reporte['casos']->sole()['caso_id']);
        $this->assertSame('28S555', $reporte['casos']->sole()['placa']);

        $this->actingAs($user);
        Livewire::test(InspeccionReporteSemanal::class)
            ->set('semana', '2026-08-24')
            ->assertSee('28S555')
            ->assertSee('1')
            ->assertDontSee('Pendiente');

        $this->get(route('inspecciones.reportes.semanal.xlsx', ['semana' => '2026-08-24']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
