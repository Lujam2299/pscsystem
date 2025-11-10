<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Asistencia;
use App\Models\Punto;
use App\Models\Subpunto;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads; // ✅ Importar el trait
use Carbon\Carbon;

class AsistenciaDiaria extends Component
{
    use WithFileUploads; // ✅ Usar el trait

    public $punto = '';
    public $fecha = '';
    public $usuarios = [];
    public $asistenciaExiste = false;
    public $elementosEnlistados = [];
    public $faltas = [];
    public $descansos = [];
    public $puntosConAsistencia = [];
    public $fotosEvidencia = []; // ✅ Propiedad añadida

    protected $listeners = ['refresh' => '$refresh'];

    public function mount()
    {
        $this->fecha = now()->toDateString();
        $this->actualizarPuntosConAsistencia();
    }

    public function render()
    {
        $subpuntosMap = $this->getSubpuntosPorPunto();

        if ($this->punto) {
            $this->cargarUsuarios();
            $this->verificarAsistenciaExistente();
        }

        return view('livewire.asistencia-diaria', [
            'subpuntosMap' => $subpuntosMap,
        ]);
    }

    public function updatedPunto()
    {
        $this->reset(['usuarios', 'asistenciaExiste', 'elementosEnlistados', 'faltas', 'descansos', 'fotosEvidencia']);
        $this->cargarUsuarios();
        $this->verificarAsistenciaExistente();
    }

    public function updatedFecha()
    {
        $this->reset(['fotosEvidencia']);
        $this->verificarAsistenciaExistente();
        $this->actualizarPuntosConAsistencia();
    }

    private function actualizarPuntosConAsistencia()
    {
        if (!$this->fecha) {
            $this->puntosConAsistencia = [];
            return;
        }

        $asistencias = Asistencia::where('fecha', $this->fecha)
            ->pluck('punto')
            ->toArray();

        $this->puntosConAsistencia = $asistencias;
    }

    private function cargarUsuarios()
    {
        $this->usuarios = User::where('estatus', 'Activo')
            ->where('rol', 'GUARDIA')
            ->where('punto', $this->punto)
            ->orderBy('name')
            ->get();
    }

    private function verificarAsistenciaExistente()
    {
        if (!$this->punto || !$this->fecha) {
            $this->asistenciaExiste = false;
            return;
        }

        $asistencia = Asistencia::where('punto', $this->punto)
            ->where('fecha', $this->fecha)
            ->first();

        if ($asistencia) {
            $this->asistenciaExiste = true;
            $this->elementosEnlistados = json_decode($asistencia->elementos_enlistados, true) ?? [];
            $this->faltas = json_decode($asistencia->faltas, true) ?? [];
            $this->descansos = json_decode($asistencia->descansos, true) ?? [];
        } else {
            $this->asistenciaExiste = false;
            $this->elementosEnlistados = [];
            $this->faltas = [];
            $this->descansos = [];
        }
    }

    public function updatedFotosEvidencia($value, $key)
{
    if ($value && is_string($key)) {
        // Esto mantiene el archivo en memoria aunque el checkbox se desmarque accidentalmente
        $this->fotosEvidencia[$key] = $value;
    }
}

    public function guardarAsistencia()
{
    \Log::info('guardarAsistencia: Función llamada');

    $this->validate([
        'punto' => 'required|string',
        'fecha' => 'required|date',
        'fotosEvidencia.*' => 'nullable|image|max:2048', // valida imágenes hasta 2MB
    ]);

    \Log::info('guardarAsistencia: Validación pasada');

    if (empty($this->elementosEnlistados) && empty($this->faltas) && empty($this->descansos)) {
        \Log::warning('guardarAsistencia: No hay usuarios marcados');
        session()->flash('error', 'Debes marcar al menos un usuario como asistió, faltó o descansó.');
        return;
    }

    \Log::info('guardarAsistencia: Hay usuarios marcados', [
        'asistencias' => $this->elementosEnlistados,
        'faltas' => $this->faltas,
        'descansos' => $this->descansos
    ]);

    \Log::info('guardarAsistencia: Contenido de fotosEvidencia', ['fotos' => $this->fotosEvidencia]);

    DB::beginTransaction();
    try {
        $user = Auth::user();

        $todosUsuarios = User::where('estatus', 'Activo')
            ->where('rol', 'GUARDIA')
            ->where('punto', $this->punto)
            ->pluck('id')
            ->toArray();

        \Log::info('guardarAsistencia: Usuarios del punto obtenidos', ['usuarios' => $todosUsuarios]);

        $faltasFinales = array_values(array_diff($todosUsuarios, array_merge($this->elementosEnlistados, $this->descansos)));

        \Log::info('guardarAsistencia: Faltas calculadas', ['faltasFinales' => $faltasFinales]);

        $rutaBase = "asistencias/" . Str::slug($user->name) . "/" . $this->fecha;
        Storage::disk('public')->makeDirectory($rutaBase, 0755, true);

        $fotosAsistentes = [];
        $this->resetErrorBag('fotosEvidencia');

        foreach ($this->fotosEvidencia as $elementoId => $foto) {
            \Log::info("guardarAsistencia: Procesando foto para usuario {$elementoId}", [
                'valid' => $foto && method_exists($foto, 'isValid') ? $foto->isValid() : false,
                'extension' => $foto ? $foto->extension() : null,
                'size' => $foto ? $foto->getSize() : null
            ]);

            if ($foto && method_exists($foto, 'isValid') && $foto->isValid()) {
                $extension = $foto->extension();
                $nombreArchivo = "{$elementoId}_" . time() . ".{$extension}";
                $rutaCompleta = Storage::disk('public')->putFileAs($rutaBase, $foto, $nombreArchivo);
                $fotosAsistentes[$elementoId] = $rutaCompleta;
            }
        }

        \Log::info('guardarAsistencia: Fotos subidas', ['fotos' => $fotosAsistentes]);

        Asistencia::create([
            'user_id' => $user->id,
            'fecha' => $this->fecha,
            'hora_asistencia' => now('America/Mexico_City')->toTimeString(),
            'elementos_enlistados' => json_encode(array_values($this->elementosEnlistados)),
            'faltas' => json_encode($faltasFinales),
            'descansos' => json_encode(array_values($this->descansos)),
            'coberturas' => json_encode([]),
            'observaciones' => '',
            'punto' => $this->punto,
            'empresa' => $user->empresa ?? 'PSC',
            'fotos_asistentes' => json_encode($fotosAsistentes),
        ]);

        DB::commit();

        session()->flash('success', 'Asistencia registrada correctamente.');
        $this->verificarAsistenciaExistente();
        \Log::info('guardarAsistencia: Todo OK');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error al guardar asistencia: ' . $e->getMessage());
        session()->flash('error', 'Error al guardar la asistencia: ' . $e->getMessage());
    }
}


    protected function getSubpuntosPorPunto()
    {
        // Lógica para obtener subpuntos por punto (igual que antes)
        $monterreyId = Punto::where('nombre', 'MONTERREY')->value('id');

        $codigos = [];
        if ($monterreyId) {
            $codigos = Subpunto::where('punto_id', $monterreyId)->pluck('codigo', 'nombre')->toArray();
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
            'KANSAS' => [
                ['nombre' => 'KANSAS', 'codigo' => null],
            ],
        ];
    }
}
