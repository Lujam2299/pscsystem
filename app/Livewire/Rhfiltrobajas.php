<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads; // ← Añadir
use App\Models\User;
use App\Models\SolicitudBajas;
use Illuminate\Support\Facades\Storage; // ← Añadir

class Rhfiltrobajas extends Component
{
    use WithPagination, WithFileUploads; // ← Añadir WithFileUploads

    public $search = '';
    public $fecha = null;
    public $solicitudId; // ← Añadir
    public $archivoRenuncia; // ← Añadir

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        $usuario = auth()->user()->name;
        $solicitudes = SolicitudBajas::whereHas('user', function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })
        ->when($this->fecha, function ($query) {
            $query->whereDate('fecha_baja', $this->fecha);
        })
        ->with('user')
        ->orderBy('fecha_baja', 'desc')
        ->paginate(10);

        return view('livewire.rhfiltrobajas', [
            'solicitudes' => $solicitudes
        ]);
    }

    // Nuevo método para abrir modal
    public function abrirModalSubirRenuncia($id)
    {
        $this->solicitudId = $id;
        $this->archivoRenuncia = null; // Limpiar archivo anterior
    }

    // Nuevo método para subir archivo
public function subirRenuncia()
{
    // Validar solo si hay archivo
    if ($this->archivoRenuncia) {
        $this->validate([
            'archivoRenuncia' => 'file|mimes:pdf,jpg,jpeg,png|max:10240', // Quitar 'required'
        ]);
    } else {
        $this->dispatch('archivoSubido', [
            'type' => 'error',
            'message' => 'Por favor selecciona un archivo.'
        ]);
        return;
    }

    $solicitud = SolicitudBajas::find($this->solicitudId);

    if (!$solicitud) {
        $this->dispatch('archivoSubido', [
            'type' => 'error',
            'message' => 'Solicitud no encontrada.'
        ]);
        return;
    }

    // Crear carpeta si no existe
    $carpeta = 'solicitudesBajas/' . $solicitud->id;
    \Storage::disk('public')->makeDirectory($carpeta);

    // Generar nombre del archivo
    $fechaHoy = now()->format('Y-m-d');
    $extension = $this->archivoRenuncia->getClientOriginalExtension();
    $nombreArchivo = "arch_renuncia_{$fechaHoy}.{$extension}";

    // Ruta: solicitudesBajas/{id_solicitud}/arch_renuncia_fecha.extension
    $ruta = $this->archivoRenuncia->storeAs($carpeta, $nombreArchivo, 'public');

    // Actualizar campo en la base de datos
    $solicitud->update([
        'arch_renuncia' => $ruta,
    ]);

    // Limpiar variables
    $this->archivoRenuncia = null;
    $this->solicitudId = null;

    $this->dispatch('archivoSubido', [
        'type' => 'success',
        'message' => 'Archivo de renuncia subido exitosamente.'
    ]);

    // Recargar datos
    $this->resetPage();
}
}
