<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('rol')->nullable();
            $table->string('estatus')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable();
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

    protected function tearDown(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    public function test_example_action_records_actor_changes_and_hides_secrets(): void
    {
        $admin = User::factory()->create(['rol' => 'Administrador']);
        $employee = User::factory()->create(['rol' => 'Guardia', 'estatus' => 'Activo']);
        $this->actingAs($admin);

        app(AuditLogger::class)->record(
            'Bajas',
            'Prueba: usuario dado de baja',
            $employee,
            ['estatus' => 'Activo', 'password' => 'secreto'],
            ['estatus' => 'Inactivo', 'token' => 'secreto'],
            ['motivo' => 'Dato temporal de prueba'],
        );

        $log = AuditLog::firstOrFail();
        $this->assertSame($admin->id, $log->actor_id);
        $this->assertSame('Activo', $log->old_values['estatus']);
        $this->assertSame('Inactivo', $log->new_values['estatus']);
        $this->assertArrayNotHasKey('password', $log->old_values);
        $this->assertArrayNotHasKey('token', $log->new_values);
        $this->assertSame($employee->id, $log->subject_id);
    }

    public function test_only_administrator_can_open_audit_view(): void
    {
        $admin = User::factory()->create(['rol' => 'Administrador']);
        $guard = User::factory()->create(['rol' => 'Guardia']);

        $this->actingAs($admin)->get(route('admin.audit.index'))->assertOk();
        $this->actingAs($guard)->get(route('admin.audit.index'))->assertForbidden();
    }

    public function test_sensitive_state_change_routes_only_accept_mutating_http_methods(): void
    {
        foreach ([
            'admin.darDeBajaUsuario', 'admin.reingreso',
            'rh.aceptarSolicitud', 'rh.rechazarSolicitud',
            'rh.aceptarBaja', 'rh.rechazarBaja',
            'sup.aceptarSolicitudVacaciones', 'sup.rechazarSolicitudVacaciones',
            'admin.vacaciones.aceptar', 'admin.vacaciones.rechazar',
        ] as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "No existe la ruta $routeName.");
            $this->assertContains('POST', $route->methods(), "$routeName no acepta POST.");
            $this->assertNotContains('GET', $route->methods(), "$routeName todavía acepta GET.");
        }
    }
}
