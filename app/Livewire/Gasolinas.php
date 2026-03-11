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
        // Al iniciar, no cargamos registros
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
            $this->loadRegistros(); // ← Carga solo cuando hay placa
        } else {
            $this->zona_seleccionada = '';
            $this->registros = []; // ← Vacía si no hay placa
        }
    }

    public function loadRegistros()
    {
        $query = Turno::query()
            ->when($this->subpunto_id, fn($q) => $q->where('subpunto_id', $this->subpunto_id))
            ->when($this->placa, fn($q) => $q->where('Placas_unidad', 'like', "%{$this->placa}%"))
            ->whereDate('created_at', '>=', now()->subDays($this->dias_atras)->toDateString())
            ->orderBy('created_at', 'asc');

        $this->registros = $query->get()->map(function ($t) {
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
        ];
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
        }

        session()->flash('message', 'Turnos guardados exitosamente.');
        $this->loadRegistros();
    }
}
