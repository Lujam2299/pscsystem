<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TraccarMonitoringControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.traccar.api_url' => 'https://traccar.example.test/api',
            'services.traccar.websocket_url' => 'wss://traccar.example.test/api/socket',
            'services.traccar.token' => 'permanent-secret',
            'services.traccar.timeout' => 2,
            'services.traccar.socket_token_ttl' => 1,
        ]);

        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('titulo')->nullable();
            $table->text('mensaje')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('alertas');

        parent::tearDown();
    }

    public function test_guest_cannot_open_the_gps_monitoring_page(): void
    {
        $this->get(route('monitoreo.unidades-gps.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unrelated_role_cannot_access_traccar_data(): void
    {
        Http::fake();

        $this->actingAs($this->userWithRole('GUARDIA'))
            ->getJson(route('monitoreo.unidades-gps.data'))
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_monitoring_role_can_open_the_gps_monitoring_page(): void
    {
        $this->actingAs($this->userWithRole('MONITORISTA'))
            ->get(route('monitoreo.unidades-gps.index'))
            ->assertOk()
            ->assertSee('Unidades GPS')
            ->assertSee('traccar-map', false);
    }

    public function test_monitoring_role_can_fetch_devices_and_positions(): void
    {
        Http::fake([
            'https://traccar.example.test/api/devices' => Http::response([
                ['id' => 10, 'name' => 'Unidad 10', 'status' => 'online'],
            ]),
            'https://traccar.example.test/api/positions' => Http::response([
                ['id' => 20, 'deviceId' => 10, 'latitude' => 25.68, 'longitude' => -100.31],
            ]),
        ]);

        $this->actingAs($this->userWithRole('AUXILIAR MONITORISTA'))
            ->getJson(route('monitoreo.unidades-gps.data'))
            ->assertOk()
            ->assertJsonPath('devices.0.id', 10)
            ->assertJsonPath('positions.0.deviceId', 10)
            ->assertJsonMissing(['token' => 'permanent-secret']);

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer permanent-secret'));
    }

    public function test_admin_can_request_a_short_lived_socket_token(): void
    {
        Http::fake([
            'https://traccar.example.test/api/session/token' => Http::response('temporary-token'),
        ]);

        $this->actingAs($this->userWithRole('admin'))
            ->postJson(route('monitoreo.unidades-gps.socket-token'))
            ->assertOk()
            ->assertJsonPath('token', 'temporary-token')
            ->assertJsonStructure(['token', 'expires_at'])
            ->assertJsonMissing(['token' => 'permanent-secret']);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://traccar.example.test/api/session/token'
                && $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Bearer permanent-secret')
                && filled($request['expiration']);
        });
    }

    public function test_upstream_error_is_returned_as_a_safe_gateway_error(): void
    {
        Http::fake([
            'https://traccar.example.test/api/devices' => Http::response([], 500),
        ]);

        $this->actingAs($this->userWithRole('MONITOREO'))
            ->getJson(route('monitoreo.unidades-gps.data'))
            ->assertStatus(502)
            ->assertJson([
                'message' => 'No fue posible consultar las unidades GPS en este momento.',
            ])
            ->assertJsonMissing(['token' => 'permanent-secret']);
    }

    private function userWithRole(string $role): User
    {
        return (new User)->forceFill([
            'id' => random_int(1, 10000),
            'name' => 'Usuario de prueba',
            'email' => fake()->unique()->safeEmail(),
            'rol' => $role,
        ]);
    }
}
