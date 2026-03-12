<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Punto;
use App\Models\Unidades;
use App\Models\Turno;

class Gasolinas extends Component
{
    public $subpunto_id = null;
    public $placa = '';
    public $zona_seleccionada = '';
    public $dias_atras = 10;

    public $registros = [];

    public function mount()
    {
        $this->registros = [];
    }

    public function render()
    {
        $puntos = Punto::all();

        return view('livewire.gasolinas', [
            'puntos' => $puntos,
            'total_km' => collect($this->registros)->sum('km_inicio'),
            'total_litros' => 0,
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
        $query = Turno::query()
            ->when($this->subpunto_id, fn($q) => $q->where('subpunto_id', $this->subpunto_id))
            ->when($this->placa, fn($q) => $q->where('Placas_unidad', 'like', "%{$this->placa}%"))
            ->whereDate('created_at', '>=', now()->subDays($this->dias_atras)->toDateString())
            ->orderBy('created_at', 'asc');

        $turnos = $query->get();

        // Cargar gastos asociados a los turnos
        $gastos = \App\Models\Gastos::whereIn('Turno_id', $turnos->pluck('id'))->get()->keyBy('Turno_id');

        $this->registros = $turnos->map(function ($t) use ($gastos) {
            $gasto = $gastos[$t->id] ?? null;

            return [
                'id' => $t->id,
                'fecha' => $t->created_at->format('Y-m-d'),
                'user_id' => $t->User_id,
                'nombre_elemento' => $t->Nombre_elemento,
                'tipo' => $t->Tipo,
                'hora_inicio' => $t->Hora_inicio ? $t->Hora_inicio->format('H:i') : '',
                'km_inicio' => $t->Km_inicio,
                'rayas_inicio' => $t->Rayas_gasolina_inicio,
                'placas' => $t->Placas_unidad,
                'subpunto_id' => $t->subpunto_id,
                'punto' => $t->Punto ?? '',

                // Campos de carga (desde gastos)
                'hora_carga' => $gasto ? $gasto->Hora : '',
                'gasolina_antes_carga' => $gasto ? $gasto->Gasolina_antes_carga : 0,
                'km_carga' => $gasto ? $gasto->Km : 0,
                'kmr_entre_cargas' => 0,
                'monto' => $gasto ? $gasto->Monto : 0,
                'litros' => $gasto ? $gasto->Litros : 0,
                'gasolina_despues_carga' => $gasto ? $gasto->Gasolina_despues_carga : 0,
            ];
        })->values()->toArray();
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
                'created_at' => $dato['fecha'],
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
