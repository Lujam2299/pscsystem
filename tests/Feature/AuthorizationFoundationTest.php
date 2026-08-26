<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthorizationFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
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

        Route::middleware(['web', 'auth', 'permission:payroll.access'])
            ->get('/_authorization/payroll', fn () => response('ok'));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Usuario externo',
            'email' => 'externo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'externo@example.com']);
    }

    public function test_regular_user_cannot_open_a_payroll_route(): void
    {
        $user = User::factory()->create(['rol' => 'Guardia']);

        $this->actingAs($user)
            ->get('/_authorization/payroll')
            ->assertForbidden();
    }

    public function test_payroll_user_can_open_a_payroll_route(): void
    {
        $user = User::factory()->create(['rol' => 'Auxiliar Nóminas']);

        $this->actingAs($user)
            ->get('/_authorization/payroll')
            ->assertOk();
    }

    public function test_auxiliary_administrator_cannot_manage_api_tokens(): void
    {
        $user = User::factory()->create(['rol' => 'Auxiliar Administrativo']);

        $this->actingAs($user)
            ->post('/generate-api-token', [
                'token_name' => 'prueba',
                'current_password' => 'password',
            ])
            ->assertForbidden();
    }
}
