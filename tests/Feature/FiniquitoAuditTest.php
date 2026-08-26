<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiniquitoAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_mass_finiquito_process_generates_a_summary_even_without_eligible_requests(): void
    {
        $administrator = User::factory()->create(['rol' => 'admin', 'estatus' => 'Activo']);

        $this->actingAs($administrator)
            ->postJson(route('registrarFiniquitos'))
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $log = AuditLog::query()
            ->where('action', 'Generación masiva de finiquitos ejecutada')
            ->firstOrFail();

        $this->assertSame($administrator->id, $log->actor_id);
        $this->assertSame(0, $log->metadata['solicitudes_revisadas']);
        $this->assertSame(0, $log->metadata['finiquitos_generados']);
        $this->assertSame(0.0, (float) $log->metadata['total_generado']);
    }
}
