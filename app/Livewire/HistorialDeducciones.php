<?php

namespace App\Livewire;

use App\Models\Deducciones;
use Livewire\Component;
use Livewire\WithPagination;

class HistorialDeducciones extends Component
{
    use WithPagination;

    public $buscarUsuario = '';
    public $fechaInicio = '';
    public $fechaFin = '';
    public $estado = '';

    protected $queryString = [
        'buscarUsuario' => ['except' => ''],
        'fechaInicio' => ['except' => ''],
        'fechaFin' => ['except' => ''],
        'estado' => ['except' => ''],
    ];

    public function updatingBuscarUsuario()
    {
        $this->resetPage();
    }

    public function updatingFechaInicio()
    {
        $this->resetPage();
    }

    public function updatingFechaFin()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    public function render()
    {
        $deducciones = Deducciones::with('user')
            ->buscarUsuario($this->buscarUsuario)
            ->entreFechas($this->fechaInicio, $this->fechaFin)
            ->porStatus($this->estado)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.historial-deducciones', [
            'deducciones' => $deducciones,
        ]);
    }

    public function limpiarFiltros()
    {
        $this->reset(['buscarUsuario', 'fechaInicio', 'fechaFin', 'estado']);
        $this->resetPage();
    }
}
