<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GpsAlert;
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

        Schema::create('gps_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('traccar_event_id')->unique();
            $table->unsignedBigInteger('device_id')->index();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedBigInteger('geofence_id')->nullable();
            $table->string('type');
            $table->string('priority')->default('info');
            $table->timestampTz('event_time');
            $table->json('attributes')->nullable();
            $table->timestamps();
        });

        Schema::create('gps_alert_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gps_alert_id');
            $table->unsignedBigInteger('user_id');
            $table->timestampTz('read_at');
            $table->unique(['gps_alert_id', 'user_id']);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('gps_alert_reads');
        Schema::dropIfExists('gps_alerts');
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
            ->assertSee('traccar-map', false)
            ->assertSee('En movimiento')
            ->assertSee('Detenida con ignición')
            ->assertSee('Estacionada')
            ->assertSee('Seguir unidad')
            ->assertSee('gps-marker__vehicle', false)
            ->assertSee('Noreste')
            ->assertSee('gps-detail', false);
    }

    public function test_monitoring_role_can_fetch_devices_and_positions(): void
    {
        Http::fake([
            'https://traccar.example.test/api/devices' => Http::response([
                ['id' => 10, 'name' => 'Unidad 10', 'status' => 'online'],
            ]),
            'https://traccar.example.test/api/positions' => Http::response([
                [
                    'id' => 20,
                    'deviceId' => 10,
                    'latitude' => 25.68,
                    'longitude' => -100.31,
                    'speed' => 20,
                    'course' => 180,
                    'attributes' => ['ignition' => true, 'motion' => true],
                ],
            ]),
        ]);

        $this->actingAs($this->userWithRole('AUXILIAR MONITORISTA'))
            ->getJson(route('monitoreo.unidades-gps.data'))
            ->assertOk()
            ->assertJsonPath('devices.0.id', 10)
            ->assertJsonPath('positions.0.deviceId', 10)
            ->assertJsonPath('positions.0.attributes.ignition', true)
            ->assertJsonPath('positions.0.course', 180)
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

    public function test_monitoring_role_can_fetch_geofences(): void
    {
        Http::fake([
            'https://traccar.example.test/api/geofences' => Http::response([
                ['id' => 7, 'name' => 'Base principal', 'area' => 'CIRCLE (25.68 -100.31, 500)'],
            ]),
        ]);

        $this->actingAs($this->userWithRole('MONITORISTA'))
            ->getJson(route('monitoreo.unidades-gps.geofences'))
            ->assertOk()
            ->assertJsonPath('geofences.0.id', 7)
            ->assertJsonPath('geofences.0.name', 'Base principal');
    }

    public function test_recent_gps_events_are_deduplicated_and_classified(): void
    {
        Http::fake([
            'https://traccar.example.test/api/devices' => Http::response([
                ['id' => 10, 'name' => 'Unidad 10', 'status' => 'online'],
            ]),
            'https://traccar.example.test/api/reports/events*' => Http::response([
                [
                    'id' => 501,
                    'deviceId' => 10,
                    'positionId' => 20,
                    'type' => 'alarm',
                    'eventTime' => now()->utc()->toIso8601String(),
                    'attributes' => ['alarm' => 'sos'],
                ],
            ]),
        ]);

        $user = $this->userWithRole('MONITORISTA');
        $this->actingAs($user)
            ->getJson(route('monitoreo.unidades-gps.alerts'))
            ->assertOk()
            ->assertJsonPath('alerts.0.traccar_event_id', 501)
            ->assertJsonPath('alerts.0.priority', 'critical')
            ->assertJsonPath('unread_count', 1);

        $this->actingAs($user)->getJson(route('monitoreo.unidades-gps.alerts'))->assertOk();

        $this->assertSame(1, GpsAlert::where('traccar_event_id', 501)->count());
    }

    public function test_monitoring_user_can_mark_a_gps_alert_as_read(): void
    {
        $alert = GpsAlert::create([
            'traccar_event_id' => 900,
            'device_id' => 10,
            'type' => 'overspeed',
            'priority' => 'high',
            'event_time' => now(),
            'attributes' => [],
        ]);
        $user = $this->userWithRole('MONITORISTA');

        $this->actingAs($user)
            ->postJson(route('monitoreo.unidades-gps.alerts.read'), ['ids' => [$alert->id]])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('gps_alert_reads', [
            'gps_alert_id' => $alert->id,
            'user_id' => $user->id,
        ]);
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
