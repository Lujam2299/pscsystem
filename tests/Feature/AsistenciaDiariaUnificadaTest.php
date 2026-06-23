<?php

namespace Tests\Feature;

use App\Livewire\AsistenciaDiaria;
use App\Models\Asistencia;
use App\Models\Punto;
use App\Models\Subpunto;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AsistenciaDiariaUnificadaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEsquemaMinimo();
    }

    protected function tearDown(): void
    {
        Schema::dropAllTables();
        parent::tearDown();
    }

    public function test_crea_recarga_y_edita_el_mismo_registro_sin_duplicarlo(): void
    {
        $punto = Punto::create(['nombre' => 'MONTERREY']);
        Subpunto::create([
            'punto_id' => $punto->id,
            'nombre' => 'PLANTA UNO',
            'codigo' => 1,
            'roles' => ['GUARDIA'],
        ]);

        $operaciones = User::create([
            'name' => 'Usuario Operaciones',
            'email' => 'operaciones@example.test',
            'password' => 'secret',
            'punto' => 'OFICINA',
            'rol' => 'OPERACIONES',
            'estatus' => 'Activo',
            'empresa' => 'PSC',
        ]);
        $guardia = User::create([
            'name' => 'Elemento Uno',
            'email' => 'guardia@example.test',
            'password' => 'secret',
            'punto' => '001',
            'rol' => 'GUARDIA',
            'estatus' => 'Activo',
            'empresa' => 'PSC',
        ]);
        $coordinador = User::create([
            'name' => 'Coordinador Uno',
            'email' => 'coordinador@example.test',
            'password' => 'secret',
            'punto' => '001',
            'rol' => 'coordinador',
            'estatus' => 'Activo',
            'empresa' => 'PSC',
        ]);

        Livewire::actingAs($operaciones)
            ->test(AsistenciaDiaria::class)
            ->set('punto', '001')
            ->assertSet("estatusPorUsuario.{$guardia->id}", 'descanso')
            ->assertSet("estatusPorUsuario.{$coordinador->id}", 'descanso')
            ->assertSet("turnosPorUsuario.{$guardia->id}", [])
            ->set("estatusPorUsuario.{$guardia->id}", 'asistio')
            ->set("turnosPorUsuario.{$guardia->id}", ['dia'])
            ->assertSet("turnosPorUsuario.{$guardia->id}", ['dia'])
            ->set("turnosPorUsuario.{$guardia->id}", ['dia', 'noche'])
            ->assertSet("turnosPorUsuario.{$guardia->id}", ['dia', 'noche'])
            ->set("minutosRetardo.{$guardia->id}", 10)
            ->set("tiempoExtraHoras.{$guardia->id}", 2.5)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('asistencias', 1);
        $registro = Asistencia::firstOrFail();
        $this->assertSame([$guardia->id], json_decode($registro->elementos_enlistados, true));
        $this->assertSame(['dia', 'noche'], json_decode($registro->turnos, true)[$guardia->id]);
        $this->assertDatabaseHas('retardos', [
            'asistencia_id' => $registro->id,
            'user_id' => $guardia->id,
            'minutos_retardo' => 10,
        ]);
        $this->assertDatabaseHas('tiempos_extras', [
            'asistencia_id' => $registro->id,
            'user_id' => $guardia->id,
            'total_horas' => '02:30:00',
        ]);

        Livewire::actingAs($operaciones)
            ->test(AsistenciaDiaria::class)
            ->set('punto', '001')
            ->assertSet('modoEdicion', true)
            ->assertSet("estatusPorUsuario.{$guardia->id}", 'asistio')
            ->set("estatusPorUsuario.{$guardia->id}", 'falto')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('asistencias', 1);
        $registro->refresh();
        $this->assertSame([], json_decode($registro->elementos_enlistados, true));
        $this->assertSame([$guardia->id], json_decode($registro->faltas, true));
        $this->assertDatabaseCount('retardos', 0);
        $this->assertDatabaseCount('tiempos_extras', 0);
    }

    public function test_ordena_monterrey_primero_y_sus_subpuntos_por_codigo(): void
    {
        Punto::create(['nombre' => 'AGUASCALIENTES']);
        $monterrey = Punto::create(['nombre' => 'MONTERREY']);

        Subpunto::create(['punto_id' => $monterrey->id, 'nombre' => 'SIN CODIGO', 'codigo' => null]);
        Subpunto::create(['punto_id' => $monterrey->id, 'nombre' => 'CODIGO VEINTE', 'codigo' => 20]);
        Subpunto::create(['punto_id' => $monterrey->id, 'nombre' => 'CODIGO TRES', 'codigo' => 3]);

        $mapa = Livewire::test(AsistenciaDiaria::class)->get('subpuntosMap');

        $this->assertSame('MONTERREY', array_key_first($mapa));
        $this->assertSame(
            ['MONTERREY', 'CODIGO TRES', 'CODIGO VEINTE', 'SIN CODIGO'],
            array_column($mapa['MONTERREY'], 'nombre')
        );
    }

    private function crearEsquemaMinimo(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('sol_alta_id')->nullable();
            $table->string('punto')->nullable();
            $table->string('rol')->nullable();
            $table->string('estatus')->nullable();
            $table->string('empresa')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('puntos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('solicitud_altas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('documentacion_altas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id');
            $table->string('arch_foto')->nullable();
            $table->timestamps();
        });

        Schema::create('subpuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('punto_id');
            $table->string('nombre');
            $table->integer('codigo')->nullable();
            $table->json('roles')->nullable();
            $table->timestamps();
        });

        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('fecha');
            $table->time('hora_asistencia');
            $table->json('elementos_enlistados');
            $table->json('turnos')->nullable();
            $table->json('faltas')->nullable();
            $table->json('descansos')->nullable();
            $table->json('coberturas')->nullable();
            $table->string('observaciones');
            $table->string('punto');
            $table->string('empresa')->nullable();
            $table->json('fotos_asistentes')->nullable();
            $table->timestamps();
        });

        Schema::create('retardos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('asistencia_id')->nullable();
            $table->date('fecha');
            $table->integer('minutos_retardo');
            $table->unsignedBigInteger('registrado_por');
            $table->timestamps();
        });

        Schema::create('tiempos_extras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asistencia_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->date('fecha');
            $table->string('hora_inicio')->nullable();
            $table->string('hora_fin')->nullable();
            $table->time('total_horas');
            $table->string('autorizado_por');
            $table->string('observaciones');
            $table->timestamps();
        });
    }
}
