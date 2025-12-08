<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SolicitudBajas;

class HistorialAcusesBaja extends Component
{
    use WithPagination;

    public $search = '';           // Filtro por nombre de usuario
    public $fecha_desde = '';      // Filtro desde
    public $fecha_hasta = '';      // Filtro hasta

    protected $paginationTheme = 'tailwind';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFechaDesde() { $this->resetPage(); }
    public function updatingFechaHasta() { $this->resetPage(); }

    public function render()
{
    $query = SolicitudBajas::with(['acuse', 'user']); // Cargar acuse y user, aunque no existan

    // Filtro por nombre de usuario
    if ($this->search) {
        $query->whereHas('user', function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%');
        });
    }

    // Filtro por rango de fechas
    if ($this->fecha_desde) {
        $query->whereDate('fecha_baja', '>=', $this->fecha_desde);
    }
    if ($this->fecha_hasta) {
        $query->whereDate('fecha_baja', '<=', $this->fecha_hasta);
    }

    $query->orderBy('fecha_baja', 'desc');

    $acuses = $query->paginate(10);

    return view('livewire.historial-acuses-baja', [
        'acuses' => $acuses
    ]);
}
}
