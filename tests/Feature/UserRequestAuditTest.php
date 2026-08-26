<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRequestAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_creating_a_user_generates_an_audit_record_without_password(): void
    {
        $administrator = User::factory()->create(['rol' => 'admin', 'estatus' => 'Activo']);

        $this->actingAs($administrator)
            ->post(route('registrarUsuario'), [
                'name' => 'Usuario auditado',
                'email' => 'auditado@example.test',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'rol' => 'Recursos Humanos',
                'punto' => 'Matriz',
                'empresa' => 'PSC',
            ])
            ->assertRedirect(route('admin.verUsuarios'));

        $user = User::query()->where('email', 'auditado@example.test')->firstOrFail();
        $log = AuditLog::query()->where('action', 'Usuario creado')->firstOrFail();

        $this->assertSame($administrator->id, $log->actor_id);
        $this->assertSame($user->id, $log->subject_id);
        $this->assertSame('Recursos Humanos', $log->new_values['rol']);
        $this->assertArrayNotHasKey('password', $log->new_values);
        $this->assertStringNotContainsString('Password123!', json_encode($log->toArray()));
    }
}
