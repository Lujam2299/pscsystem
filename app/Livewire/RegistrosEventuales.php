<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Eventuales;
use App\Models\Subpunto;

class RegistrosEventuales extends Component
{
    use WithPagination;

    public $search = '';
    public $tipo_pago = 'todos';
    public $subpunto_id = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingTipoPago() { $this->resetPage(); }
    public function updatingSubpuntoId() { $this->resetPage(); }
    public function updatingFechaDesde() { $this->resetPage(); } // ✅ Nuevo
    public function updatingFechaHasta() { $this->resetPage(); } // ✅ Nuevo

    public function render()
    {
        $query = Eventuales::with(['user', 'subpunto']); // ✅ Modelo singular

        // Filtro por nombre de usuario
        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro por tipo de pago
        if ($this->tipo_pago !== 'todos') {
            $query->where('tipo_pago', $this->tipo_pago);
        }

        // Filtro por subpunto
        if ($this->subpunto_id) {
            $query->where('subpunto_id', $this->subpunto_id);
        }

        // Filtro por rango de fechas
        if ($this->fecha_desde) {
            $query->whereDate('fecha', '>=', $this->fecha_desde);
        }
        if ($this->fecha_hasta) {
            $query->whereDate('fecha', '<=', $this->fecha_hasta);
        }

        $registros = $query->orderBy('fecha', 'desc')->paginate(10);
        $subpuntos = Subpunto::orderBy('nombre')->get();

        return view('livewire.registros-eventuales', [
            'registros' => $registros,
            'subpuntos' => $subpuntos,
        ]);
    }
}
