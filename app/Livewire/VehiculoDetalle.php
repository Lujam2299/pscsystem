<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Unidades;

class VehiculoDetalle extends Component
{
  public $unidadId;
  public $unidad;

  public function mount($id)
  {
    $this->unidadId = $id;
    $this->unidad   = Unidades::with(['inspecciones' => function ($query) {
      $query->withCount('evidencias')->latest('fecha_inspeccion')->limit(10);
    }])->findOrFail($id);
  }

  public function render()
  {
    // Solo lectura: no hay recarga ni cambios de estado
    // Se consulta el historial de placas una sola vez
    if (!isset($this->unidad->placas_historial)) {
      $placas_historial               = \App\Models\Placa::where('unidad_id', $this->unidad->id)
        ->orderByDesc('created_at')
        ->get();
      $this->unidad->placas_historial = $placas_historial;
    }
    return view('livewire.vehiculo-detalle', [
      'unidad' => $this->unidad,
    ]);
  }
}
