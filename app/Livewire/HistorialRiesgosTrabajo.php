<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\RiesgoTrabajo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HistorialRiesgosTrabajo extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $mostrarModalEdicion = false;
    public $riesgoEditando;
    public $tipo_riesgo;
    public $descripcion_observaciones;
    public $fecha;
    public $folio;
    public $archivo_pdf;
    public $arch_alta;
    public $ruta_archivo_pdf_actual;
    public $arch_alta_actual;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $riesgos = RiesgoTrabajo::with(['user' => function($query) {
                $query->withTrashed(); // Cargar usuarios incluso si están soft deleted
            }])
            ->where(function($query) {
                // Buscar por tipo_riesgo o por el nombre del usuario asociado
                $query->where('tipo_riesgo', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($q) {
                          $q->withTrashed() // Buscar también en usuarios soft deleted
                            ->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.historial-riesgos-trabajo', [
            'riesgos' => $riesgos,
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function abrirModalEdicion($id)
{
    $riesgo = RiesgoTrabajo::findOrFail($id);

    $this->dispatch('abrir-modal-riesgo', [
        'id' => $riesgo->id,
        'tipo_riesgo' => $riesgo->tipo_riesgo,
        'descripcion_observaciones' => $riesgo->descripcion_observaciones,
        'fecha' => $riesgo->fecha,
        'folio' => $riesgo->folio,
        'ruta_archivo_pdf' => $riesgo->ruta_archivo_pdf,
        'arch_alta' => $riesgo->arch_alta,
    ]);
}
}
