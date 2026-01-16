<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Misiones;
use App\Models\Geofence;
use App\Models\User;
use App\Models\RealtimePosition;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class MapaGeocercas extends Component
{
    public $misionesRecientes = [];
    public $geocercasMisionSeleccionada = [];
    public $misionSeleccionadaId = null;

    public function mount()
    {
        $this->cargarMisionesRecientes();
        $this->cargarUsuariosEscolta();
    }

    public function cargarMisionesRecientes()
    {
        $this->misionesRecientes = Misiones::with('geofences')->orderBy('created_at', 'desc')->limit(10)->get();
    }

    public function cargarUsuariosEscolta()
    {
        Log::info('MapaGeocercas@cargarUsuariosEscolta: Iniciando carga de usuarios escolta con ubicaciones recientes');

        $periodo = Carbon::now()->subHours(24);
        $escortUsersAll = User::all();//where('rol', 'like', '%escolta%')->get();

        $usersWithRecentLocation = [];
        foreach ($escortUsersAll as $user) {
            $lastLocation = RealtimePosition::where('user_id', $user->id)
                ->where('created_at', '>', $periodo)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastLocation) {
                $usersWithRecentLocation[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'latitude' => $lastLocation->latitude,
                    'longitude' => $lastLocation->longitude,
                    'recorded_at' => $lastLocation->recorded_at->toISOString(),
                    'user_data' => $user->only(['rol', 'email'])
                ];
            }
        }

        Log::info('MapaGeocercas@cargarUsuariosEscolta: Usuarios encontrados', ['count' => count($usersWithRecentLocation)]);
        $this->dispatch('escortUsersLoaded', users: $usersWithRecentLocation);
    }

    public function seleccionarMision($misionId)
    {
        $this->misionSeleccionadaId = $misionId;
        $this->cargarGeocercasMision($misionId);
    }

    public function cargarGeocercasMision($misionId)
    {
        $this->geocercasMisionSeleccionada = Geofence::where('mision_id', $misionId)->get();
        Log::info('Geocercas cargadas para misión ' . $misionId . ': ' . $this->geocercasMisionSeleccionada->count());
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
