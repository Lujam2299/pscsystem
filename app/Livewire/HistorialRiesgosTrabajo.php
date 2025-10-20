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

    // ✅ Propiedades para el formulario de edición
    public $mostrarModalEdicion = false;
    public $riesgoEditandoId;
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

    // ✅ Escuchar el evento de actualización
    protected $listeners = ['riesgo-actualizado' => '$refresh'];

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

    // ✅ Método para abrir el modal de edición
    public function abrirModalEdicion($id)
    {
        $riesgo = RiesgoTrabajo::findOrFail($id);

        $this->riesgoEditandoId = $riesgo->id;
        $this->tipo_riesgo = $riesgo->tipo_riesgo;
        $this->descripcion_observaciones = $riesgo->descripcion_observaciones;
        $this->fecha = $riesgo->fecha ? $riesgo->fecha->format('Y-m-d') : null;
        $this->folio = $riesgo->folio;
        $this->ruta_archivo_pdf_actual = $riesgo->ruta_archivo_pdf;
        $this->arch_alta_actual = $riesgo->arch_alta;

        $this->mostrarModalEdicion = true;
    }

    // ✅ Método para guardar los cambios
    public function guardarEdicion()
    {
        $this->validate([
            'tipo_riesgo' => 'required|in:En el trabajo,En trayecto',
            'descripcion_observaciones' => 'nullable|string',
            'fecha' => 'nullable|date',
            'folio' => 'nullable|string|max:100',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:2048',
            'arch_alta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $riesgo = RiesgoTrabajo::findOrFail($this->riesgoEditandoId);
        $userId = $riesgo->user_id;
        $baseDir = "RiesgosTrabajo/{$userId}";

        $nuevaRutaPdf = $riesgo->ruta_archivo_pdf;
        if ($this->archivo_pdf) {
            // Eliminar archivo anterior si existe
            if ($riesgo->ruta_archivo_pdf) {
                $rutaAnterior = str_replace('storage/', '', $riesgo->ruta_archivo_pdf);
                if (Storage::disk('public')->exists($rutaAnterior)) {
                    Storage::disk('public')->delete($rutaAnterior);
                }
            }
            // Guardar nuevo archivo
            $originalName = $this->archivo_pdf->getClientOriginalName();
            $extension = $this->archivo_pdf->getClientOriginalExtension();
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $nuevaRutaPdf = 'storage/' . $this->archivo_pdf->storeAs($baseDir, $fileName, 'public');
        }

        $nuevaRutaAlta = $riesgo->arch_alta;
        if ($this->arch_alta) {
            // Eliminar archivo anterior si existe
            if ($riesgo->arch_alta) {
                $rutaAnterior = str_replace('storage/', '', $riesgo->arch_alta);
                if (Storage::disk('public')->exists($rutaAnterior)) {
                    Storage::disk('public')->delete($rutaAnterior);
                }
            }
            // Guardar nuevo archivo
            $originalName = $this->arch_alta->getClientOriginalName();
            $extension = $this->arch_alta->getClientOriginalExtension();
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $nuevaRutaAlta = 'storage/' . $this->arch_alta->storeAs($baseDir, $fileName, 'public');
        }

        $riesgo->update([
            'tipo_riesgo' => $this->tipo_riesgo,
            'descripcion_observaciones' => $this->descripcion_observaciones,
            'fecha' => $this->fecha,
            'folio' => $this->folio,
            'ruta_archivo_pdf' => $nuevaRutaPdf,
            'arch_alta' => $nuevaRutaAlta,
        ]);

        session()->flash('message', 'Riesgo actualizado correctamente.');

        $this->cerrarModalEdicion();
    }

    // ✅ Método para cerrar el modal
    public function cerrarModalEdicion()
    {
        $this->mostrarModalEdicion = false;
        $this->reset([
            'riesgoEditandoId',
            'tipo_riesgo',
            'descripcion_observaciones',
            'fecha',
            'folio',
            'archivo_pdf',
            'arch_alta',
            'ruta_archivo_pdf_actual',
            'arch_alta_actual',
        ]);
    }
}
