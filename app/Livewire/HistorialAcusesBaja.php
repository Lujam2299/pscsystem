<?php

namespace App\Livewire;

use App\Models\SolicitudBajas;
use Livewire\Component;
use Livewire\WithPagination;

class HistorialAcusesBaja extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $acuses = SolicitudBajas::whereHas('acuse')
            ->with(['acuse', 'user'])
            ->orderBy('fecha_baja', 'desc')
            ->paginate(10);

        return view('livewire.historial-acuses-baja', [
            'acuses' => $acuses
        ]);
    }
}
