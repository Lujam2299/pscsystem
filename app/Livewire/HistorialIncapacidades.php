<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Incapacidad;

class HistorialIncapacidades extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $tipo_incapacidad = '';

    protected $paginationTheme = 'tailwind';
    protected $queryString = ['search', 'fecha_inicio', 'fecha_fin', 'tipo_incapacidad'];

    protected $rules = [
        'fecha_inicio' => 'nullable|date',
        'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
    ];

    public function updated($property)
    {
        if (in_array($property, ['fecha_inicio', 'fecha_fin', 'tipo_incapacidad'])) {
            $this->resetPage();
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Incapacidad::query()
            ->with(['user' => function($query) {
                $query->withTrashed();
            }]);

        // Aplicar filtro de búsqueda
        $query->when($this->search, function ($q) {
            $q->where('motivo', 'like', '%' . $this->search . '%')
              ->orWhere('tipo_incapacidad', 'like', '%' . $this->search . '%')
              ->orWhere('folio', 'like', '%' . $this->search . '%')
              ->orWhereHas('user', function ($q2) {
                  $q2->withTrashed()
                    ->where('name', 'like', '%' . $this->search . '%');
              });
        });

        // Filtrar por rango de fechas (usando fecha_inicio de la incapacidad)
        $query->when($this->fecha_inicio, function ($q) {
            $q->whereDate('fecha_inicio', '>=', $this->fecha_inicio);
        });

        $query->when($this->fecha_fin, function ($q) {
            $q->whereDate('fecha_inicio', '<=', $this->fecha_fin);
        });

        // Filtrar por tipo de incapacidad
        $query->when($this->tipo_incapacidad, function ($q) {
            $q->where('tipo_incapacidad', $this->tipo_incapacidad);
        });

        $incapacidades = $query->orderBy('created_at', 'desc')
                               ->paginate(10);

        // Tipos de incapacidades disponibles
        $tiposDisponibles = [
            'Enfermedad General',
            'Accidente de Trabajo',
            'Accidente de Trayecto',
            'Enfermedad de Riesgo de Trabajo',
            'Maternidad',
            'Otro'
        ];

        return view('livewire.historial-incapacidades', [
            'incapacidades' => $incapacidades,
            'tiposDisponibles' => $tiposDisponibles,
        ]);
    }

    public function clearFilters()
    {
        $this->reset(['fecha_inicio', 'fecha_fin', 'tipo_incapacidad']);
    }
}
