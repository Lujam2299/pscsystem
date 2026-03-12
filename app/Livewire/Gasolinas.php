<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Punto;
use App\Models\Unidades;
use App\Models\Turno;
use Carbon\Carbon; // Asegúrate de tener esta línea

class Gasolinas extends Component
{
    public $subpunto_id = null;
    public $placa = '';
    public $zona_seleccionada = '';

    // Cambiamos dias_atras por filtros de rango
    public $fecha_desde;
    public $fecha_hasta;

    public $registros = [];

    public function mount()
    {
        $this->registros = [];
        // Configurar valores por defecto: últimos 10 días
        $this->fecha_hasta = now()->format('Y-m-d');
        $this->fecha_desde = now()->subDays(10)->format('Y-m-d');
    }

    // Método que se ejecuta cuando cambian las fechas
    public function updatedFechaDesde()
    {
        if ($this->placa) {
            $this->loadRegistros();
        }
    }

    public function updatedFechaHasta()
    {
        if ($this->placa) {
            $this->loadRegistros();
        }
    }

    public function render()
    {
        $puntos = Punto::all();

        // Filtrar registros activos (solo los que tienen placa o son nuevos)
        $registrosFiltrados = collect($this->registros)->filter(function ($r) {
            return !empty($r['placas']);
        });

        // KM inicial: primer registro (ordenado por fecha ASC)
        $kmInicial = $registrosFiltrados->first() ? $registrosFiltrados->first()['km_inicio'] : 0;

        // KM final: último registro con km_carga > 0 (es decir, con carga registrada)
        $ultimoConCarga = $registrosFiltrados->filter(fn($r) => $r['km_carga'] > 0)->last();
        $kmFinal = $ultimoConCarga ? $ultimoConCarga['km_carga'] : $kmInicial;

        $diferenciaKm = $kmFinal - $kmInicial;

        $totalDinero = $registrosFiltrados->sum('monto');
        $totalLitros = $registrosFiltrados->sum('litros');

        $rendimiento = $totalLitros > 0 ? round($diferenciaKm / $totalLitros, 2) : 0;

        return view('livewire.gasolinas', [
            'puntos' => $puntos,
            'total_km' => $diferenciaKm,
            'total_litros' => $totalLitros,
            'total_dinero' => $totalDinero,
            'rendimiento' => $rendimiento,
        ]);
    }

    public function updatedPlaca($value)
    {
        if ($value) {
            $unidad = Unidades::where('placas', $value)->first();
            $this->zona_seleccionada = $unidad ? strtoupper($unidad->zona) : '';
            $this->loadRegistros();
        } else {
            $this->zona_seleccionada = '';
            $this->registros = [];
        }
    }

    public function loadRegistros()
{
    $fechaDesde = $this->fecha_desde ? Carbon::parse($this->fecha_desde) : now()->subDays(10);
    $fechaHasta = $this->fecha_hasta ? Carbon::parse($this->fecha_hasta) : now();

    // 1. Obtener último km_carga ANTES del rango (para referencia)
    $ultimo_km_antes = 0;
    if ($this->placa) {
        $ultimoAntes = \App\Models\Gastos::join('turno', 'gastos.Turno_id', '=', 'turno.id')
            ->where('turno.Placas_unidad', 'like', "%{$this->placa}%")
            ->where('turno.Fecha', '<', $fechaDesde->toDateString())
            ->where('gastos.Km', '>', 0)
            ->orderBy('turno.Fecha', 'desc')
            ->limit(1)
            ->value('gastos.Km');

        $ultimo_km_antes = $ultimoAntes ?? 0;
    }

    // 2. Cargar turnos dentro del rango
    $query = Turno::query()
        ->when($this->subpunto_id, fn($q) => $q->where('subpunto_id', $this->subpunto_id))
        ->when($this->placa, fn($q) => $q->where('Placas_unidad', 'like', "%{$this->placa}%"))
        ->whereBetween('Fecha', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
        ->orderBy('Fecha', 'asc');

    $turnos = $query->get();
    $gastos = \App\Models\Gastos::whereIn('Turno_id', $turnos->pluck('id'))->get()->keyBy('Turno_id');

    // 3. Construir registros
    $registros = $turnos->map(function ($t) use ($gastos, $ultimo_km_antes) {
        $gasto = $gastos[$t->id] ?? null;

        return [
            'id' => $t->id,
            'fecha' => $t->Fecha ? $t->Fecha->format('Y-m-d') : $t->created_at->format('Y-m-d'),
            'user_id' => $t->User_id,
            'nombre_elemento' => $t->Nombre_elemento,
            'tipo' => $t->Tipo,
            'hora_inicio' => $t->Hora_inicio ? $t->Hora_inicio->format('H:i') : '',
            'km_inicio' => $t->Km_inicio,
            'rayas_inicio' => $t->Rayas_gasolina_inicio,
            'placas' => $t->Placas_unidad,
            'subpunto_id' => $t->subpunto_id,
            'punto' => $t->Punto ?? '',

            'hora_carga' => $gasto ? $gasto->Hora : '',
            'gasolina_antes_carga' => $gasto ? $gasto->Gasolina_antes_carga : 0,
            'km_carga' => $gasto ? $gasto->Km : 0,
            'kmr_entre_cargas' => 0,
            'monto' => $gasto ? $gasto->Monto : 0,
            'litros' => $gasto ? $gasto->Litros : 0,
            'gasolina_despues_carga' => $gasto ? $gasto->Gasolina_despues_carga : 0,
        ];
    })->values()->toArray();

    // 4. Calcular KMR para cada registro (backend)
    $lastKm = $ultimo_km_antes; // Iniciar con el último km fuera del rango
    foreach ($registros as &$r) {
        if ($r['km_carga'] > 0) {
            if ($lastKm > 0) {
                $r['kmr_entre_cargas'] = $r['km_carga'] - $lastKm;
            }
            $lastKm = $r['km_carga']; // Actualizar para el siguiente
        }
    }

    $this->registros = $registros;
}

    public function addRow()
    {
        $this->registros[] = [
            'id' => null,
            'fecha' => now()->format('Y-m-d'),
            'user_id' => null,
            'nombre_elemento' => '',
            'tipo' => 'Entrada',
            'hora_inicio' => now()->format('H:i'),
            'km_inicio' => 0,
            'rayas_inicio' => 0,
            'placas' => $this->placa,
            'subpunto_id' => $this->subpunto_id,
            'punto' => $this->zona_seleccionada,

            // Carga (vacía por defecto)
            'hora_carga' => '',
            'gasolina_antes_carga' => 0,
            'km_carga' => 0,
            'kmr_entre_cargas' => 0,
            'monto' => 0,
            'litros' => 0,
            'gasolina_despues_carga' => 0,
        ];
    }

    public function guardarTodos()
    {
        foreach ($this->registros as $dato) {
            // Guardar turno
            if (empty(trim($dato['nombre_elemento']))) continue;

            if (!$dato['user_id']) {
                session()->flash('error', 'Debe seleccionar un usuario válido.');
                return;
            }

            $turno = $dato['id'] ? Turno::find($dato['id']) : new Turno;

            $turno->fill([
                'Fecha' => $dato['fecha'],
                'User_id' => $dato['user_id'],
                'Nombre_elemento' => $dato['nombre_elemento'],
                'Tipo' => $dato['tipo'],
                'Hora_inicio' => $dato['hora_inicio'],
                'Km_inicio' => $dato['km_inicio'],
                'Rayas_gasolina_inicio' => $dato['rayas_inicio'],
                'Placas_unidad' => $dato['placas'],
                'subpunto_id' => $dato['subpunto_id'],
                'Punto' => $dato['punto'],
            ])->save();

            // Guardar gasto si hay datos de carga
            $tiene_carga = (
                $dato['km_carga'] > 0 ||
                $dato['monto'] > 0 ||
                $dato['gasolina_antes_carga'] > 0 ||
                $dato['gasolina_despues_carga'] > 0
            );

            if ($tiene_carga) {
                $gasto = \App\Models\Gastos::firstOrNew(['Turno_id' => $turno->id]);
                $gasto->Turno_id = $turno->id;
                $gasto->user_name = $turno->Nombre_elemento;

                $gasto->fill([
                    'user_id' => $dato['user_id'],
                    'Fecha' => $dato['fecha'],
                    'Tipo' => 'Gasolina',
                    'Hora' => $dato['hora_carga'],
                    'Km' => $dato['km_carga'],
                    'Gasolina_antes_carga' => $dato['gasolina_antes_carga'],
                    'Monto' => $dato['monto'],
                    'Litros' => $dato['litros'],
                    'Gasolina_despues_carga' => $dato['gasolina_despues_carga'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->save();
            }
        }

        session()->flash('message', 'Datos guardados exitosamente.');
        $this->loadRegistros();
    }
}
