<?php

namespace App\Livewire;

use App\Models\InspeccionUnidad;
use App\Models\Servicio;
use App\Services\AuditLogger;
use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class InspeccionDetalle extends Component
{
    public InspeccionUnidad $inspeccion;

    public function mount(InspeccionUnidad $inspeccion): void
    {
        Gate::authorize(Permission::INSPECTIONS_VIEW);
        $this->inspeccion = $inspeccion->load(['unidad', 'evidencias', 'servicio', 'creador', 'revisor']);
    }

    public function generarReparacion(): mixed
    {
        Gate::authorize(Permission::INSPECTIONS_MANAGE);

        if ($this->inspeccion->servicio_id) {
            return redirect()->route('servicio.detalle', $this->inspeccion->servicio_id);
        }

        $servicio = Servicio::create([
            'unidad_id' => $this->inspeccion->unidad_id,
            'fecha' => $this->inspeccion->fecha_inspeccion->toDateString(),
            'descripcion' => $this->inspeccion->observaciones ?: 'Revisión derivada de la inspección #'.$this->inspeccion->id,
            'costo' => 0,
            'responsable' => null,
            'tipo' => 'Correctivo',
            'observaciones' => 'Registro generado desde la inspección #'.$this->inspeccion->id.'. Requiere completar diagnóstico, responsable y costo.',
        ]);

        $this->inspeccion->update([
            'servicio_id' => $servicio->id,
            'estado' => 'requiere_seguimiento',
        ]);

        app(AuditLogger::class)->record(
            'Inspecciones de unidades',
            'Reparación generada desde inspección',
            $this->inspeccion,
            [],
            ['servicio_id' => $servicio->id],
        );

        session()->flash('success', 'Se creó el servicio correctivo. Completa su diagnóstico y costo.');

        return redirect()->route('servicios.index', ['editar' => $servicio->id, 'return' => 'detalle']);
    }

    public function render()
    {
        return view('livewire.inspeccion-detalle')->layout('layouts.app');
    }
}
