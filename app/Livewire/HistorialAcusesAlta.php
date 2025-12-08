<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class HistorialAcusesAlta extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFechaDesde() { $this->resetPage(); }
    public function updatingFechaHasta() { $this->resetPage(); }

    public function render()
    {
        $query = User::with('documentacionAltas')
            ->withTrashed()
            ->whereHas('documentacionAltas', function ($q) {
                $q->whereNotNull('arch_acuse_imss'); // Solo con acuse subido
            });

        // Filtro por nombre
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // ✅ Filtro por rango de fechas (usando created_at de users)
        if ($this->fecha_desde) {
            $query->whereDate('created_at', '>=', $this->fecha_desde);
        }
        if ($this->fecha_hasta) {
            $query->whereDate('created_at', '<=', $this->fecha_hasta);
        }

        $usuarios = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.historial-acuses-alta', [
            'usuarios' => $usuarios,
        ]);
    }
}
