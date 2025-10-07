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
    public $fecha = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingTipoPago() { $this->resetPage(); }
    public function updatingSubpuntoId() { $this->resetPage(); }
    public function updatingFecha() { $this->resetPage(); }

    public function render()
    {
        $query = Eventuales::with(['user', 'subpunto']);

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->tipo_pago !== 'todos') {
            $query->where('tipo_pago', $this->tipo_pago);
        }

        if ($this->subpunto_id) {
            $query->where('subpunto_id', $this->subpunto_id);
        }

        if ($this->fecha) {
            $query->whereDate('fecha', $this->fecha);
        }

        $registros = $query->orderBy('fecha', 'desc')->paginate(10);
        $subpuntos = Subpunto::orderBy('nombre')->get();

        return view('livewire.registros-eventuales', [
            'registros' => $registros,
            'subpuntos' => $subpuntos,
        ]);
    }
}
