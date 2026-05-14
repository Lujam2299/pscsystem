<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\SolicitudBajas;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Rhfiltrobajas extends Component
{
    use WithPagination, WithFileUploads;

    // Filtros
    public $search = '';
    public $fecha = null;

    // --- Modal Subir Renuncia (Existente) ---
    public $solicitudId;
    public $archivoRenuncia;

    // --- Modal Editar Solicitud (Nuevo) ---
    public $editSolicitudId = null;
    public $solicitudActual = null; // Instancia del modelo cargada

    public $editMotivo = '';
    public $editPor = '';
    public $editFechaBaja = '';
    public $editUltimaAsistencia = ''; // NUEVO
    public $editDescuento = '';       // NUEVO

    // Archivos temporales para edición (si se seleccionan nuevos, reemplazan a los viejos)
    public $newArchBaja;
    public $newArchEquipo;
    public $newArchCheque;
    public $newArchRenuncia;

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFecha()
    {
        $this->resetPage();
    }

    public function render()
    {
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

    // ------------------------------------------
    // Lógica Modal: Subir Renuncia (Existente)
    // ------------------------------------------
    public function abrirModalSubirRenuncia($id)
    {
        $this->solicitudId = $id;
        $this->archivoRenuncia = null;
    }

    public function subirRenuncia()
    {
        if (!$this->archivoRenuncia) {
            session()->flash('error', 'Por favor selecciona un archivo.');
            return;
        }

        $this->validate([
            'archivoRenuncia' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $solicitud = SolicitudBajas::find($this->solicitudId);
        if (!$solicitud) {
            session()->flash('error', 'Solicitud no encontrada.');
            return;
        }

        $carpeta = 'solicitudesBajas/' . $solicitud->id;
        Storage::disk('public')->makeDirectory($carpeta);

        $fechaHoy = now()->format('Y-m-d');
        $extension = $this->archivoRenuncia->getClientOriginalExtension();
        $nombreArchivo = "arch_renuncia_{$fechaHoy}.{$extension}";

        $ruta = $this->archivoRenuncia->storeAs($carpeta, $nombreArchivo, 'public');

        $solicitud->update(['arch_renuncia' => $ruta]);

        $this->archivoRenuncia = null;
        $this->solicitudId = null;
        session()->flash('message', 'Archivo de renuncia subido exitosamente.');
        $this->resetPage();
    }

    // ------------------------------------------
    // Lógica Modal: Editar Solicitud (Nueva)
    // ------------------------------------------

    public function abrirModalEditar($id)
    {
        // Cargamos la solicitud y la guardamos en la propiedad pública
        $this->solicitudActual = SolicitudBajas::findOrFail($id);

        $this->editSolicitudId = $this->solicitudActual->id;
        $this->editMotivo = $this->solicitudActual->motivo;
        $this->editPor = $this->solicitudActual->por;
        // Formatear fechas para input type="date" (Y-m-d)
        $this->editFechaBaja = Carbon::parse($this->solicitudActual->fecha_baja)->format('Y-m-d');
        $this->editUltimaAsistencia = $this->solicitudActual->ultima_asistencia ? Carbon::parse($this->solicitudActual->ultima_asistencia)->format('Y-m-d') : null; // NUEVO
        $this->editDescuento = $this->solicitudActual->descuento; // NUEVO

        // Resetear archivos temporales de edición
        $this->newArchBaja = null;
        $this->newArchEquipo = null;
        $this->newArchCheque = null;
        $this->newArchRenuncia = null;
    }

    public function guardarEdicion()
    {
        // Usamos la propiedad cargada o buscamos de nuevo por seguridad
        $solicitud = $this->solicitudActual ? $this->solicitudActual : SolicitudBajas::findOrFail($this->editSolicitudId);

        $this->validate([
            'editMotivo' => 'required|string|max:1000',
            'editPor' => 'nullable|in:Renuncia,Ausentismo,Separación Voluntaria,Otro',
            'editFechaBaja' => 'required|date',
            'editUltimaAsistencia' => 'nullable|date', // NUEVO
            'editDescuento' => 'nullable|numeric',    // NUEVO
            'newArchBaja' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'newArchEquipo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'newArchCheque' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'newArchRenuncia' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $carpeta = 'solicitudesBajas/' . $solicitud->id;
        Storage::disk('public')->makeDirectory($carpeta);
        // Usamos la fecha del formulario para nombrar los archivos si se actualizan
        $fechaRef = Carbon::parse($this->editFechaBaja)->format('Y-m-d');

        // Closure auxiliar para manejar la subida y limpieza - CON LOGGING
        $procesarArchivo = function($nuevoArchivo, $rutaAntigua, $prefijoNombre) use ($carpeta, $fechaRef, $solicitud) {
            \Log::info("Procesando archivo {$prefijoNombre}", [
                'nuevoArchivo' => $nuevoArchivo ? 'SI' : 'NO',
                'rutaAntigua' => $rutaAntigua,
                'existeAnterior' => $rutaAntigua && Storage::disk('public')->exists($rutaAntigua),
                'solicitud_id' => $solicitud->id
            ]);

            if ($nuevoArchivo) {
                \Log::info("  -> Subiendo nuevo archivo para {$prefijoNombre}");
                // Borrar archivo anterior si existe para no llenar el disco
                if ($rutaAntigua && Storage::disk('public')->exists($rutaAntigua)) {
                    try {
                        $deleted = Storage::disk('public')->delete($rutaAntigua);
                        \Log::info("  -> Borrado archivo anterior {$rutaAntigua}: " . ($deleted ? 'OK' : 'ERROR'));
                    } catch (\Exception $e) {
                        \Log::error("Error borrando archivo anterior {$rutaAntigua}: " . $e->getMessage());
                    }
                }
                $extension = $nuevoArchivo->getClientOriginalExtension();
                $nombreArchivo = "{$prefijoNombre}_{$fechaRef}.{$extension}";
                $rutaNueva = $nuevoArchivo->storeAs($carpeta, $nombreArchivo, 'public');
                \Log::info("  -> Archivo subido como: {$rutaNueva}");
                return $rutaNueva;
            }
            // Si no hay nuevo archivo, retornar la ruta antigua (sin cambios)
            \Log::info("  -> No hay nuevo archivo, manteniendo: {$rutaAntigua}");
            return $rutaAntigua;
        };

        $solicitud->update([
            'motivo' => $this->editMotivo,
            'por' => $this->editPor,
            'fecha_baja' => $this->editFechaBaja,
            'ultima_asistencia' => $this->editUltimaAsistencia, // NUEVO
            'descuento' => $this->editDescuento,               // NUEVO
            'estatus' => 'Aceptada',                           // ESTABLECIDO COMO FIJO

            'archivo_baja' => $procesarArchivo($this->newArchBaja, $solicitud->archivo_baja, 'archivo_baja'),
            'arch_equipo_entregado' => $procesarArchivo($this->newArchEquipo, $solicitud->arch_equipo_entregado, 'arch_equipo'),
            'arch_cheque' => $procesarArchivo($this->newArchCheque, $solicitud->arch_cheque, 'arch_cheque'),
            'arch_renuncia' => $procesarArchivo($this->newArchRenuncia, $solicitud->arch_renuncia, 'arch_renuncia'),
        ]);

        // Cerrar modal y limpiar
        $this->editSolicitudId = null;
        $this->solicitudActual = null;
        session()->flash('message', 'Solicitud actualizada correctamente.');
        $this->resetPage();
    }
}
