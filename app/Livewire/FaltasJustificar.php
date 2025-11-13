<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Asistencia;
use App\Models\User;
use App\Models\FaltaJustificada;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FaltasJustificar extends Component
{
    use WithFileUploads;

    public $punto = '';
    public $fecha = '';
    public $faltas = [];
    public $usuariosFaltas = [];
    public $faltasJustificadas = [];
    public $usuariosAJustificar = [];
    public $motivos = [];
    public $archivos = [];

    public function render()
    {
        $subpuntosMap = $this->getSubpuntosPorPunto();
        $rol = Auth::user()?->rol;

        if ($rol === 'AUXILIAR OPERACIONES') {
            $subpuntosMap = [
                'MONTERREY' => $subpuntosMap['MONTERREY'] ?? []
            ];
        }

        return view('livewire.faltas-justificar', [
            'subpuntosMap' => $subpuntosMap,
        ]);
    }

    public function updatedPunto()
    {
        $this->cargarFaltas();
    }

    public function updatedFecha()
    {
        $this->cargarFaltas();
    }

    public function cargarFaltas()
    {
        if (!$this->punto || !$this->fecha) {
            $this->usuariosFaltas = collect();
            return;
        }

        $asistencia = Asistencia::where('punto', $this->punto)
            ->where('fecha', $this->fecha)
            ->first();

        $faltas = $asistencia ? json_decode($asistencia->faltas, true) : [];
        $this->usuariosFaltas = User::whereIn('id', $faltas)->get();

        // ✅ Asegurarse de que sea un array, no una colección
        $this->faltasJustificadas = FaltaJustificada::where('fecha', $this->fecha)
            ->pluck('user_id')
            ->toArray();
    }

    public function justificar()
    {
        Log::info('Iniciando justificar', [
            'usuariosAJustificar' => $this->usuariosAJustificar,
            'motivos' => $this->motivos,
            'archivos' => array_keys($this->archivos),
        ]);

        $this->validate([
            'usuariosAJustificar' => 'required|array|min:1',
            'usuariosAJustificar.*' => 'exists:users,id',
            'motivos' => 'required|array',
            'motivos.*' => 'required|string|max:500',
            'archivos' => 'nullable|array',
            'archivos.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        Log::info('Validación pasada');

        foreach ($this->usuariosAJustificar as $userId) {
            Log::info('Procesando usuario', ['user_id' => $userId]);

            $archivo = null;
            if (isset($this->archivos[$userId])) {
                $archivoSubido = $this->archivos[$userId];
                Log::info('Archivo encontrado para usuario', [
                    'user_id' => $userId,
                    'nombre' => $archivoSubido->getClientOriginalName(),
                    'ext' => $archivoSubido->getClientOriginalExtension(),
                    'size' => $archivoSubido->getSize(),
                    'valid' => $archivoSubido->isValid(),
                ]);

                if ($archivoSubido->isValid()) {
                    // Crear carpeta para el usuario si no existe
                    $carpeta = "faltas/{$userId}";
                    Storage::disk('public')->makeDirectory($carpeta);
                    Log::info('Carpeta creada', ['carpeta' => $carpeta]);

                    // Subir archivo con nombre único
                    $nombre = 'justificacion_' . time() . '.' . $archivoSubido->getClientOriginalExtension();
                    $ruta = $archivoSubido->storeAs($carpeta, $nombre, 'public');

                    $archivo = $ruta;
                    Log::info('Archivo subido', ['ruta' => $ruta]);
                } else {
                    Log::warning('Archivo no válido', ['user_id' => $userId]);
                }
            } else {
                Log::info('No hay archivo para usuario', ['user_id' => $userId]);
            }

            $asistencia = Asistencia::where('punto', $this->punto)
                ->where('fecha', $this->fecha)
                ->first();

            $registro = FaltaJustificada::create([
                'asistencia_id' => $asistencia?->id,
                'user_id' => $userId,
                'fecha' => $this->fecha,
                'tipo' => 'justificada',
                'motivo' => $this->motivos[$userId] ?? 'Sin motivo',
                'archivo_justificante' => $archivo,
                'registrado_por' => Auth::id(),
            ]);

            Log::info('Falta justificada creada', [
                'id' => $registro->id,
                'archivo' => $archivo,
                'user_id' => $userId,
            ]);

            // Limpiar el archivo temporal
            if (isset($this->archivos[$userId])) {
                unset($this->archivos[$userId]);
            }
        }

        session()->flash('message', 'Faltas justificadas registradas correctamente.');
        $this->cargarFaltas();
        $this->reset(['usuariosAJustificar', 'motivos', 'archivos']);
    }

    protected function getSubpuntosPorPunto()
    {
        $monterreyId = \App\Models\Punto::where('nombre', 'MONTERREY')->value('id');

        $codigos = [];
        if ($monterreyId) {
            $codigos = \App\Models\Subpunto::where('punto_id', $monterreyId)->pluck('codigo', 'nombre')->toArray();
        }

        $codigoMaryKay = $codigos['MARY KAY CORPORATIVO'] ?? $codigos['MARYKAY CORPORATIVO'] ?? $codigos['MAR KAY CORPORATIVO'] ?? null;

        $monterreySubpuntos = [
            ['nombre' => 'MONTERREY', 'codigo' => $codigos['MONTERREY'] ?? null],
            ['nombre' => 'CUSTODIO', 'codigo' => $codigos['CUSTODIO'] ?? null],
            ['nombre' => 'DALTILE', 'codigo' => $codigos['DALTILE'] ?? null],
            ['nombre' => 'TORRENOVO', 'codigo' => $codigos['TORRENOVO'] ?? null],
            ['nombre' => 'TRASLADOS', 'codigo' => $codigos['TRASLADOS'] ?? null],
            ['nombre' => 'BONETERA', 'codigo' => $codigos['BONETERA'] ?? null],
            ['nombre' => 'HOMEDEPOT', 'codigo' => $codigos['HOMEDEPOT'] ?? null],
            ['nombre' => 'AMERICAN AIRLINES', 'codigo' => $codigos['AMERICAN AIRLINES'] ?? null],
            ['nombre' => 'MARY KAY CORPORATIVO', 'codigo' => $codigoMaryKay],
            ['nombre' => 'KANSAS', 'codigo' => $codigos['KANSAS'] ?? null],
            ['nombre' => 'CIMARRON', 'codigo' => $codigos['CIMARRON'] ?? null],
            ['nombre' => 'OFICINA', 'codigo' => $codigos['OFICINA'] ?? null],
            ['nombre' => 'ASSET', 'codigo' => $codigos['ASSET'] ?? null],
            ['nombre' => 'TORRE DELTA', 'codigo' => $codigos['TORRE DELTA'] ?? null],
            ['nombre' => 'SACMI DE MEXICO', 'codigo' => $codigos['SACMI DE MEXICO'] ?? null],
            ['nombre' => 'THERMO ELÉCTRICA', 'codigo' => $codigos['THERMO ELÉCTRICA'] ?? null],
            ['nombre' => 'KINDER MORGAN', 'codigo' => $codigos['KINDER MORGAN'] ?? null],
            ['nombre' => 'GOBAR', 'codigo' => $codigos['GOBAR'] ?? null],
            ['nombre' => 'PEMCORP #2', 'codigo' => $codigos['PEMCORP #2'] ?? null],
            ['nombre' => 'ROCHE BOBOIS', 'codigo' => $codigos['ROCHE BOBOIS'] ?? null],
            ['nombre' => 'OFF ON GREEN', 'codigo' => $codigos['OFF ON GREEN'] ?? null],
            ['nombre' => 'COOPER LIGHT', 'codigo' => $codigos['COOPER LIGHT'] ?? null],
            ['nombre' => 'MONTE PALATINO', 'codigo' => $codigos['MONTE PALATINO'] ?? null],
            ['nombre' => 'OATEY', 'codigo' => $codigos['OATEY'] ?? null],
            ['nombre' => 'PLAZA DOMENA', 'codigo' => $codigos['PLAZA DOMENA'] ?? null],
        ];

        return [
            'MONTERREY' => $monterreySubpuntos,
            'GUANAJUATO' => [
                ['nombre' => 'SILAO', 'codigo' => null],
                ['nombre' => 'CELAYA', 'codigo' => null],
                ['nombre' => 'SALAMANCA', 'codigo' => null],
            ],
            'NUEVO LAREDO' => [
                ['nombre' => 'ZONA DE ABASTOS V', 'codigo' => null],
            ],
            'MEXICO' => [
                ['nombre' => 'VALLE DE MEXICO', 'codigo' => null],
            ],
            'SLP' => [
                ['nombre' => 'WATCO', 'codigo' => null],
                ['nombre' => 'BMW', 'codigo' => null],
                ['nombre' => 'ZONA DE ABASTOS I', 'codigo' => null],
                ['nombre' => 'INTERPUERTO Y TALLER', 'codigo' => null],
            ],
            'XALAPA' => [
                ['nombre' => 'XALAPA', 'codigo' => null],
            ],
            'MICHOACAN' => [
                ['nombre' => 'MICHOACÁN', 'codigo' => null],
            ],
            'PUEBLA' => [
                ['nombre' => 'PUEBLA', 'codigo' => null],
            ],
            'TOLUCA' => [
                ['nombre' => 'TOLUCA', 'codigo' => null],
            ],
            'QUERETARO' => [
                ['nombre' => 'QUERÉTARO', 'codigo' => null],
            ],
            'SALTILLO' => [
                ['nombre' => 'SALTILLO', 'codigo' => null],
            ],
            'DRONES' => [
                ['nombre' => 'DRONES', 'codigo' => null],
            ],
        ];
    }
}
