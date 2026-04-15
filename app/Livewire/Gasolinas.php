<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Punto;
use App\Models\Unidades;
use App\Models\Turno;
use Carbon\Carbon;

class Gasolinas extends Component
{
    public $subpunto_id = null;
    public $placa = '';
    public $zona_seleccionada = '';
    public $fecha_desde;
    public $fecha_hasta;

    public $registros = [];

    // Array temporal para almacenar sugerencias
    public $tempSuggestions = [];

    public function mount()
    {
        $this->registros = [];
        $this->tempSuggestions = [];
        $this->fecha_hasta = now()->format('Y-m-d');
        $this->fecha_desde = now()->subDays(10)->format('Y-m-d');
    }

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

    public function render()
    {
        $puntos = Punto::all();

        $registrosFiltrados = collect($this->registros)->filter(function ($r) {
            return !empty($r['placas']);
        });

        $kmInicial = $registrosFiltrados->first() ? $registrosFiltrados->first()['km_inicio'] : 0;
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

    public function loadRegistros()
    {
        $fechaDesde = Carbon::parse($this->fecha_desde);
        $fechaHasta = Carbon::parse($this->fecha_hasta);

        $query = Turno::query()
            ->when($this->subpunto_id, fn($q) => $q->where('subpunto_id', $this->subpunto_id))
            ->when($this->placa, fn($q) => $q->where('Placas_unidad', 'like', "%{$this->placa}%"))
            ->where(function ($q) use ($fechaDesde, $fechaHasta) {
                $q->where(function ($sub) use ($fechaDesde, $fechaHasta) {
                    $sub->whereNotNull('Fecha')
                         ->whereDate('Fecha', '>=', $fechaDesde->toDateString())
                         ->whereDate('Fecha', '<=', $fechaHasta->toDateString());
                })->orWhere(function ($sub) use ($fechaDesde, $fechaHasta) {
                    $sub->whereNull('Fecha')
                         ->whereDate('created_at', '>=', $fechaDesde->toDateString())
                         ->whereDate('created_at', '<=', $fechaHasta->toDateString());
                });
            })
            ->orderBy('Fecha', 'asc')
            ->orderBy('created_at', 'asc');

        $turnos = $query->get()->unique('id');
        $gastos = \App\Models\Gastos::whereIn('Turno_id', $turnos->pluck('id'))->get()->keyBy('Turno_id');

        $registros = [];
        $lastKm = 0;

        foreach ($turnos as $t) {
            $gasto = $gastos[$t->id] ?? null;

            $kmCarga = $gasto ? $gasto->Km : 0;
            $kmr = 0;
            if ($kmCarga > 0) {
                if ($lastKm > 0) {
                    $kmr = $kmCarga - $lastKm;
                }
                $lastKm = $kmCarga;
            }

            $registros[] = [
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
                'km_carga' => $kmCarga,
                'kmr_entre_cargas' => $kmr,
                'monto' => $gasto ? $gasto->Monto : 0,
                'litros' => $gasto ? $gasto->Litros : 0,
                'gasolina_despues_carga' => $gasto ? $gasto->Gasolina_despues_carga : 0,
            ];
        }

        $this->registros = $registros;
    }

    public function updatedRegistrosNombreElemento($value, $fullPath)
    {
        preg_match('/registros\.(\d+)\.nombre_elemento/', $fullPath, $matches);
        if (!isset($matches[1])) return;

        $index = (int)$matches[1];

        if (strlen($value) < 2) {
            $this->tempSuggestions = [];
            return;
        }

        $users = \App\Models\User::where('estatus', 'Activo')
            ->where('name', 'like', "%{$value}%")
            ->limit(5)
            ->select('id', 'name')
            ->get()
            ->toArray();

        $this->tempSuggestions = $users;
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

            'hora_carga' => '',
            'gasolina_antes_carga' => 0,
            'km_carga' => 0,
            'kmr_entre_cargas' => 0,
            'monto' => 0,
            'litros' => 0,
            'gasolina_despues_carga' => 0,
        ];
    }

    public function selectUser($userId, $userName, $rowIndex)
    {
        $this->registros[$rowIndex]['nombre_elemento'] = $userName;
        $this->registros[$rowIndex]['user_id'] = $userId;
        $this->tempSuggestions = []; // Limpiar sugerencias
    }

    public function selectUserFromInput($rowIndex)
    {
        $nombre = $this->registros[$rowIndex]['nombre_elemento'];
        if (empty($nombre)) return;

        $user = \App\Models\User::where('estatus', 'Activo')
            ->where('name', $nombre)
            ->first();

        if ($user) {
            $this->registros[$rowIndex]['user_id'] = $user->id;
        }
    }

    public function guardarTodos()
    {
        foreach ($this->registros as $dato) {
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

            $tiene_carga = (
                $dato['km_carga'] > 0 ||
                $dato['monto'] > 0 ||
                $dato['gasolina_antes_carga'] > 0 ||
                $dato['gasolina_despues_carga'] > 0
            );

            if ($tiene_carga) {
                $gasto = \App\Models\Gastos::firstOrNew(['Turno_id' => $turno->id]);
                $gasto->Turno_id = $turno->id;
                $gasto->user_name = $dato['nombre_elemento'];

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
