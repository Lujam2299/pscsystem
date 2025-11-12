<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PermisoEspecial;

class PermisosEspeciales extends Component
{
    use WithPagination;

    public $busqueda = '';
    public $tipo = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $con_goce = '';

    protected $queryString = [
        'busqueda' => ['except' => ''],
        'tipo' => ['except' => ''],
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
        'con_goce' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function render()
    {
        $permisos = PermisoEspecial::with(['usuario'])
            ->whereHas('usuario', function ($q) {
                $q->where('empresa', 'PSC');
            })
            ->when($this->busqueda, function ($query) {
                $query->whereHas('usuario', function ($q) {
                    $q->where('name', 'LIKE', '%' . $this->busqueda . '%');
                });
            })
            ->when($this->tipo, function ($query) {
                $query->where('tipo', $this->tipo);
            })
            ->when($this->fecha_inicio, function ($query) {
                $query->whereDate('fecha_inicio', '>=', $this->fecha_inicio);
            })
            ->when($this->fecha_fin, function ($query) {
                $query->whereDate('fecha_fin', '<=', $this->fecha_fin);
            })
            ->when($this->con_goce !== '', function ($query) {
                $query->where('con_goce', $this->con_goce === 'si' ? true : false);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Fijo en 10

        return view('livewire.permisos-especiales', [
            'permisos' => $permisos,
        ]);
    }

    public function updated($property)
    {
        if (in_array($property, ['busqueda', 'tipo', 'fecha_inicio', 'fecha_fin', 'con_goce'])) {
            $this->resetPage();
        }
    }
}
