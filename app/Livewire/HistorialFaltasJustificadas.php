<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FaltaJustificada;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HistorialFaltasJustificadas extends Component
{
    use WithPagination;

    public $busquedaUsuario = '';
    public $punto = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';

    protected $queryString = [
        'busquedaUsuario' => ['except' => ''],
        'punto' => ['except' => ''],
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
    ];

    public function render()
    {
        $query = FaltaJustificada::with(['usuario', 'registradoPor', 'asistencia'])
            ->where('tipo', 'justificada'); // Solo faltas justificadas

        // Filtrar por nombre de usuario
        if ($this->busquedaUsuario) {
            $query->whereHas('usuario', function ($q) {
                $q->where('name', 'LIKE', '%' . $this->busquedaUsuario . '%');
            });
        }

        // Filtrar por punto
        if ($this->punto) {
            $query->whereHas('asistencia', function ($q) {
                $q->where('punto', $this->punto);
            });
        }

        // Filtrar por rango de fechas
        if ($this->fecha_inicio) {
            $query->whereDate('fecha', '>=', $this->fecha_inicio);
        }

        if ($this->fecha_fin) {
            $query->whereDate('fecha', '<=', $this->fecha_fin);
        }

        $faltas = $query->orderBy('fecha', 'desc')->paginate(10);

        // Obtener puntos para el filtro
        $puntos = $this->getPuntos();

        return view('livewire.historial-faltas-justificadas', [
            'faltas' => $faltas,
            'puntos' => $puntos,
        ]);
    }

    public function updated($property)
    {
        if (in_array($property, ['busquedaUsuario', 'punto', 'fecha_inicio', 'fecha_fin'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset(['busquedaUsuario', 'punto', 'fecha_inicio', 'fecha_fin']);
    }

    protected function getPuntos()
    {
        return FaltaJustificada::where('tipo', 'justificada')
            ->whereHas('asistencia')
            ->join('asistencias', 'falta_justificadas.asistencia_id', '=', 'asistencias.id')
            ->select('asistencias.punto')
            ->distinct()
            ->pluck('punto');
    }
}
