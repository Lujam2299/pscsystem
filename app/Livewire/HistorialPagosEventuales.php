<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Eventuales;

class HistorialPagosEventuales extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';
    public $tipo_servicio = 'todos';
    public $motivo = 'todos';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFechaDesde() { $this->resetPage(); }
    public function updatingFechaHasta() { $this->resetPage(); }
    public function updatingTipoServicio() { $this->resetPage(); }
    public function updatingMotivo() { $this->resetPage(); }

    public function render()
    {
        $query = Eventuales::with(['user'])
            ->whereNotNull('arch_pago');

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->fecha_desde) {
            $query->whereDate('fecha', '>=', $this->fecha_desde);
        }
        if ($this->fecha_hasta) {
            $query->whereDate('fecha', '<=', $this->fecha_hasta);
        }

        if ($this->tipo_servicio !== 'todos') {
            $query->where('tipo_servicio', $this->tipo_servicio);
        }

        if ($this->motivo !== 'todos') {
            $query->where('motivo', $this->motivo);
        }

        $registros = $query->orderBy('fecha', 'desc')->paginate(10);

        return view('livewire.historial-pagos-eventuales', [
            'registros' => $registros,
        ]);
    }
}
