<?php

namespace App\Livewire;

use App\Models\SolicitudBajas;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class Historialfiniquitos extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_baja = '';

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $query = SolicitudBajas::with('user')
            ->where('por', 'Renuncia')
            ->where(function ($q) {
                $q->where('observaciones', 'Cheque subido correctamente.')
                  ->orWhere('observaciones', 'Cheque cancelado.');
            });

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->fecha_baja) {
            $query->whereDate('fecha_baja', $this->fecha_baja);
        }

        $renuncias = $query->orderBy('fecha_baja', 'desc')->paginate(10);

        return view('livewire.historialfiniquitos', compact('renuncias'));
    }
}
