<?php

namespace App\Livewire;

use App\Models\Reingreso;
use Livewire\Component;
use Livewire\WithPagination;

class ReingresoTable extends Component
{
    use WithPagination;

    // Propiedades públicas para los filtros
    public $search = '';
    public $startDate = '';
    public $endDate = '';

    // Config paginación
    protected $paginationTheme = 'tailwind';
    protected $perPage = 10;

    // Opcional: Actualizar filtros sin esperar Enter
    protected $queryString = ['search', 'startDate', 'endDate'];

    public function render()
    {
        $query = Reingreso::with(['user', 'user.documentacionAltas'])  // Carga relacional para evitar N+1 queries
            // Ordenar primero por nombre de usuario (ASC) y luego por número de reingreso (ASC)
            ->join('users', 'reingresos.user_id', '=', 'users.id') // Join para ordenar por nombre de usuario
            ->orderBy('users.name', 'asc') // Primero ordena alfabéticamente por nombre
            ->orderBy('reingresos.numero_reingreso', 'asc') // Luego por número de reingreso
            ->select('reingresos.*');

        // Aplicar filtro de búsqueda por nombre de usuario
        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%') // Ajusta 'name' si es otro campo
                  ->orWhere('email', 'like', '%' . $this->search . '%'); // Opcional: buscar también por email
            });
        }

        // Aplicar filtro de rango de fechas
        if ($this->startDate) {
            $query->whereDate('fecha', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('fecha', '<=', $this->endDate);
        }

        $reingresos = $query->paginate($this->perPage);

        return view('livewire.reingreso-table', [
            'reingresos' => $reingresos,
        ]);
    }

    // Método para resetear filtros
    public function resetFilters()
    {
        $this->reset(['search', 'startDate', 'endDate']);
        // Opcional: resetear la página a 1 al limpiar filtros
        $this->resetPage();
    }
}
