<?php

namespace Tests\Feature;

use App\Models\Archivonomina;
use App\Models\AuditLog;
use App\Models\Deducciones;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PayrollAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_deduction_preserves_values_and_generates_audit(): void
    {
        $administrator = User::factory()->create(['rol' => 'admin', 'estatus' => 'Activo']);
        $employee = User::factory()->create(['estatus' => 'Activo']);

        $this->actingAs($administrator)
            ->post(route('guardarDeduccion'), [
                'user_id' => $employee->id,
                'concepto' => 'Préstamo interno',
                'fecha_inicio' => '2026-09-01',
                'monto' => 1200.50,
                'num_quincenas' => 4,
            ])
            ->assertRedirect(route('nominas.deducciones'));

        $deduction = Deducciones::query()->firstOrFail();
        $log = AuditLog::query()->where('action', 'Deducción creada')->firstOrFail();

        $this->assertSame(1200.50, (float) $deduction->monto);
        $this->assertSame(1200.50, (float) $deduction->monto_pendiente);
        $this->assertSame($administrator->id, $log->actor_id);
        $this->assertSame($deduction->id, $log->subject_id);
        $this->assertSame(4, $log->new_values['num_quincenas']);
    }

    public function test_assigning_an_employee_number_records_before_and_after_values(): void
    {
        $administrator = User::factory()->create(['rol' => 'admin', 'estatus' => 'Activo']);
        $employee = User::factory()->create(['estatus' => 'Activo', 'num_empleado' => null]);

        $this->actingAs($administrator)
            ->postJson(route('nominas.asignarNumeroEmpleado'), [
                'user_id' => $employee->id,
                'num_empleado' => 4321,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $log = AuditLog::query()->where('action', 'Número de empleado asignado')->firstOrFail();

        $this->assertSame('4321', $employee->fresh()->num_empleado);
        $this->assertNull($log->old_values['num_empleado']);
        $this->assertSame(4321, $log->new_values['num_empleado']);
    }

    public function test_editing_a_payroll_record_from_livewire_generates_audit(): void
    {
        $administrator = User::factory()->create(['rol' => 'admin', 'estatus' => 'Activo']);
        $record = Archivonomina::query()->create([
            'periodo' => '1° Agosto 2026',
            'subtotal' => 1000,
            'total_destajos' => 100,
        ]);

        $this->actingAs($administrator);

        Livewire::test(\App\Livewire\NominasRegistrosTable::class)
            ->call('abrirModalEdicion', $record->id)
            ->set('periodo', '2° Agosto 2026')
            ->call('guardarEdicion')
            ->assertHasNoErrors();

        $log = AuditLog::query()->where('action', 'Registro de nómina actualizado')->firstOrFail();

        $this->assertSame('1° Agosto 2026', $log->old_values['periodo']);
        $this->assertSame('2° Agosto 2026', $log->new_values['periodo']);
        $this->assertSame([], $log->metadata['archivos_reemplazados']);
    }
}
