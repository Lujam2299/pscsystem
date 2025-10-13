<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ValesComida;

class HistorialValesComida extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';
    public $monto_desde = '';
    public $monto_hasta = '';
    public $estatus = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFechaDesde() { $this->resetPage(); }
    public function updatingFechaHasta() { $this->resetPage(); }
    public function updatingMontoDesde() { $this->resetPage(); }
    public function updatingMontoHasta() { $this->resetPage(); }
    public function updatingEstatus() { $this->resetPage(); }

    public function render()
    {
        $query = ValesComida::with(['user', 'comprobantes'])
            ->orderBy('fecha', 'desc');

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

        if ($this->monto_desde !== '') {
            $query->where('monto', '>=', $this->monto_desde);
        }
        if ($this->monto_hasta !== '') {
            $query->where('monto', '<=', $this->monto_hasta);
        }

        if ($this->estatus) {
            $query->where('estatus', $this->estatus);
        }

        $vales = $query->paginate(10);

        $estatusOptions = [
            'En Proceso',
            'Aceptada',
            'Rechazada',
            'Comprobación Pendiente',
            'Comprobación En Revisión',
            'Comprobación Aprobada',
            'Comprobación Rechazada'
        ];

        return view('livewire.historial-vales-comida', [
            'vales' => $vales,
            'estatusOptions' => $estatusOptions,
        ]);
    }

    public function exportarConFiltros()
    {
        $params = [
            'search' => $this->search,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'monto_desde' => $this->monto_desde,
            'monto_hasta' => $this->monto_hasta,
            'estatus' => $this->estatus
        ];

        $queryString = http_build_query($params);
        $url = route('vales.comida.exportar') . '?' . $queryString;

        // Devolver la URL para que el frontend la use
        return $url;
    }
}
