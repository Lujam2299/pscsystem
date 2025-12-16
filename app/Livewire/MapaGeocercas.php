<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Misiones; // Asegúrate de tener el modelo Misiones
use App\Models\Geofence; // Asegúrate de tener el modelo Geofence
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class MapaGeocercas extends Component
{
    public $misionesRecientes = [];
    public $geocercasMisionSeleccionada = [];
    public $misionSeleccionadaId = null;

    public function mount()
    {
        $this->cargarMisionesRecientes();
    }

    public function cargarMisionesRecientes()
    {
        // Cargar las misiones más recientes con eager loading para geofences
        $this->misionesRecientes = Misiones::with('geofences')->orderBy('created_at', 'desc')->limit(10)->get();
    }

    public function seleccionarMision($misionId)
    {
        $this->misionSeleccionadaId = $misionId;
        $this->cargarGeocercasMision($misionId);
    }

    public function cargarGeocercasMision($misionId)
    {
        // Cargar las geocercas asociadas a la misión seleccionada
        $this->geocercasMisionSeleccionada = Geofence::where('mision_id', $misionId)->get();
        Log::info('Geocercas cargadas para misión ' . $misionId . ': ' . $this->geocercasMisionSeleccionada->count());
        // Opcional: Enviar datos al mapa
        $this->dispatch('geocercasActualizadas', geocercas: $this->geocercasMisionSeleccionada->toArray());
    }

    public function render()
    {
        return view('livewire.mapa-geocercas', [
            'misionesRecientes' => $this->misionesRecientes,
            'geocercasMisionSeleccionada' => $this->geocercasMisionSeleccionada,
            'misionSeleccionadaId' => $this->misionSeleccionadaId,
        ]);
    }
}
