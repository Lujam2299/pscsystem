<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Incapacidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IncapacidadAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_a_disability_preserves_the_operation_and_generates_audit(): void
    {
        Storage::fake('public');
        $administrator = User::factory()->create(['rol' => 'admin', 'estatus' => 'Activo']);
        $employee = User::factory()->create(['estatus' => 'Activo']);

        $this->actingAs($administrator)
            ->post(route('aux.guardarIncapacidad'), [
                'user_id' => $employee->id,
                'motivo' => 'Recuperación médica',
                'tipo_incapacidad' => 'Temporal',
                'ramo_seguro' => 'Enfermedad general',
                'dias_incapacidad' => 3,
                'fecha_inicio' => '2026-08-26',
                'folio' => 'TEST-INC-001',
                'archivo_pdf' => UploadedFile::fake()->create('incapacidad.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('aux.incapacidadesList'));

        $incapacidad = Incapacidad::query()->where('folio', 'TEST-INC-001')->firstOrFail();
        $log = AuditLog::query()->where('action', 'Incapacidad registrada')->firstOrFail();

        $this->assertSame($administrator->id, $log->actor_id);
        $this->assertSame($incapacidad->id, $log->subject_id);
        $this->assertSame($employee->id, $log->new_values['user_id']);
        Storage::disk('public')->assertExists($incapacidad->ruta_archivo_pdf);
    }
}
