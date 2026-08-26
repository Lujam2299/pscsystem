<?php

namespace Tests\Feature\Livewire;

use App\Exports\GastosMisionSpreadsheetExport;
use App\Livewire\GastosCrud;
use App\Models\Gastos;
use App\Models\Misiones;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GastosCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-02 12:00:00');
        $this->crearEsquemaMinimo();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Schema::dropAllTables();

        parent::tearDown();
    }

    public function test_normaliza_agentes_aunque_el_json_este_codificado_dos_veces(): void
    {
        $mision = Misiones::create([
            'agentes_id' => json_encode(['3', '3963']),
            'fecha_inicio' => '2026-06-20',
            'fecha_fin' => '2026-07-25',
        ]);

        $this->assertSame([3, 3963], $mision->fresh()->agentesIdsNormalizados());
    }

    public function test_muestra_activas_por_defecto_y_agrupa_los_gastos_del_periodo(): void
    {
        $agente = $this->crearUsuario('Agente Activo');

        $activa = Misiones::create([
            'agentes_id' => json_encode([(string) $agente->id]),
            'cliente' => 'Cliente Activo',
            'fecha_inicio' => '2026-06-20',
            'fecha_fin' => '2026-07-25',
        ]);

        Misiones::create([
            'agentes_id' => [(string) $agente->id],
            'cliente' => 'Cliente Terminado',
            'fecha_inicio' => '2026-05-01',
            'fecha_fin' => '2026-05-10',
        ]);

        $this->crearGasto($agente, '2026-06-24', 250);
        $this->crearGasto($agente, '2026-05-05', 100);

        $datos = app(GastosCrud::class)->render()->getData();

        $this->assertSame([$activa->id], $datos['misiones']->pluck('id')->all());
        $this->assertSame(1, $datos['resumenMisiones'][$activa->id]['cantidad_gastos']);
        $this->assertSame(250.0, $datos['resumenMisiones'][$activa->id]['total']);
        $this->assertSame('Agente Activo', $datos['resumenMisiones'][$activa->id]['agentes'][0]['nombre']);
    }

    public function test_filtra_misiones_terminadas_y_limita_gastos_por_rango(): void
    {
        $agente = $this->crearUsuario('Agente Histórico');

        $terminada = Misiones::create([
            'agentes_id' => [(string) $agente->id],
            'cliente' => 'Cliente Histórico',
            'fecha_inicio' => '2026-05-01',
            'fecha_fin' => '2026-05-31',
        ]);

        $this->crearGasto($agente, '2026-05-05', 100);
        $this->crearGasto($agente, '2026-05-20', 300);

        $componente = app(GastosCrud::class);
        $componente->filtro_estatus = 'terminadas';
        $componente->filtro_fecha_inicio = '2026-05-15';
        $componente->filtro_busqueda = 'Histórico';

        $datos = $componente->render()->getData();

        $this->assertSame([$terminada->id], $datos['misiones']->pluck('id')->all());
        $this->assertSame(1, $datos['resumenMisiones'][$terminada->id]['cantidad_gastos']);
        $this->assertSame(300.0, $datos['resumenMisiones'][$terminada->id]['total']);
        $this->assertSame('Terminada', $datos['resumenMisiones'][$terminada->id]['estatus']);

        $componente->filtro_fecha_fin = '2026-05-01';

        $this->assertCount(0, $componente->render()->getData()['misiones']);
    }

    public function test_genera_una_hoja_por_agente_y_clasifica_los_importes(): void
    {
        $agente = $this->crearUsuario('Agente Reporte');
        $mision = Misiones::create([
            'agentes_id' => [(string) $agente->id],
            'cliente' => 'Cliente Reporte',
            'nombre_clave' => 'Operación Centro',
            'fecha_inicio' => '2026-06-20',
            'fecha_fin' => '2026-07-25',
        ]);

        $this->crearGastoClasificado($agente, '2026-06-21', 100, 'Viaticos', 'alimentos');
        $this->crearGastoClasificado($agente, '2026-06-22', 500, 'Gasolina', 'gasolina', 'tag');
        $this->crearGastoClasificado(
            $agente,
            '2026-06-23',
            75,
            'Viaticos',
            'estacionamiento',
            'efectivo',
            'Parking aeropuerto'
        );

        $spreadsheet = app(GastosMisionSpreadsheetExport::class)->createSpreadsheet($mision);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('Agente '.$agente->id, $sheet->getTitle());
        $this->assertSame(100.0, $sheet->getCell('G7')->getValue());
        $this->assertSame(500.0, $sheet->getCell('K8')->getValue());
        $this->assertSame(75.0, $sheet->getCell('D9')->getValue());
        $this->assertSame('Parking aeropuerto', $sheet->getCell('C9')->getValue());
        $this->assertSame('=SUM(A7,D7:K7)', $sheet->getCell('L7')->getValue());
        $this->assertSame('=SUM(L7:L37)', $sheet->getCell('L38')->getValue());

        $spreadsheet->disconnectWorksheets();
    }

    private function crearUsuario(string $nombre): User
    {
        return User::create([
            'name' => $nombre,
            'email' => str($nombre)->slug().'@example.test',
            'password' => 'secret',
            'rol' => 'ESCOLTA',
        ]);
    }

    private function crearGasto(User $agente, string $fecha, float $monto): Gastos
    {
        return Gastos::create([
            'user_id' => $agente->id,
            'user_name' => $agente->name,
            'Monto' => $monto,
            'Fecha' => $fecha,
            'Hora' => '07:00:00',
            'Evidencia' => null,
            'Tipo' => 'Viaticos',
        ]);
    }

    private function crearGastoClasificado(
        User $agente,
        string $fecha,
        float $monto,
        string $tipo,
        string $categoria,
        ?string $metodoPago = null,
        ?string $descripcion = null
    ): Gastos {
        return Gastos::create([
            'user_id' => $agente->id,
            'user_name' => $agente->name,
            'Monto' => $monto,
            'Fecha' => $fecha,
            'Hora' => '07:00:00',
            'Evidencia' => null,
            'Tipo' => $tipo,
            'Categoria' => $categoria,
            'Metodo_pago' => $metodoPago,
            'Descripcion' => $descripcion,
        ]);
    }

    private function crearEsquemaMinimo(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('rol')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('misiones', function (Blueprint $table): void {
            $table->id();
            $table->json('agentes_id')->nullable();
            $table->string('tipo_servicio')->nullable();
            $table->string('nombre_clave')->nullable();
            $table->string('cliente')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->timestamps();
        });

        Schema::create('gastos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('mision_id')->nullable();
            $table->decimal('Monto', 10, 2)->nullable();
            $table->date('Fecha')->nullable();
            $table->time('Hora')->nullable();
            $table->string('Evidencia')->nullable();
            $table->string('Tipo')->nullable();
            $table->string('Categoria')->nullable();
            $table->string('Metodo_pago')->nullable();
            $table->string('Descripcion')->nullable();
            $table->string('user_name')->nullable();
            $table->decimal('Litros', 10, 2)->nullable();
            $table->decimal('Km', 10, 2)->nullable();
            $table->decimal('Gasolina_antes_carga', 10, 2)->nullable();
            $table->decimal('Gasolina_despues_carga', 10, 2)->nullable();
            $table->timestamps();
        });
    }
}
