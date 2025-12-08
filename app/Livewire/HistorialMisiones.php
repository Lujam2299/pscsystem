<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Misiones;

class HistorialMisiones extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_inicio_desde = '';
    public $fecha_inicio_hasta = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFechaInicioDesde() { $this->resetPage(); }
    public function updatingFechaInicioHasta() { $this->resetPage(); }

    public function render()
    {
        $query = Misiones::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('tipo_servicio', 'like', '%' . $this->search . '%')
                  ->orWhere('nombre_clave', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->fecha_inicio_desde) {
            $query->whereDate('fecha_inicio', '>=', $this->fecha_inicio_desde);
        }
        if ($this->fecha_inicio_hasta) {
            $query->whereDate('fecha_inicio', '<=', $this->fecha_inicio_hasta);
        }

        $misiones = $query->orderBy('fecha_inicio', 'desc')->paginate(10);

        return view('livewire.historial-misiones', [
            'misiones' => $misiones
        ]);
    }
}
