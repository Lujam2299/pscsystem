<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Eventuales;

class HistorialPagosEventuales extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFecha() { $this->resetPage(); }

    public function render()
    {
        $query = Eventuales::with(['user'])
            ->whereNotNull('arch_pago');

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->fecha) {
            $query->whereDate('fecha', $this->fecha);
        }

        $registros = $query->orderBy('fecha', 'desc')->paginate(10);

        return view('livewire.historial-pagos-eventuales', [
            'registros' => $registros,
        ]);
    }
}
