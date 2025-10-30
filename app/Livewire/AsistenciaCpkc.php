<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Asistencia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AsistenciaCpkc extends Component
{
    public $puntos = [
        'GUARDIA RECEPCION 1',
        'PLUMA 100',
        'ACCESO DEPORTIVO',
        'CAPACITACION',
        'VIAS PUBLICAS ENTRADA',
        'VIAS PUBLICAS SALIDA',
        'ACCESO AL TAM',
        'TAM CASETA TOYOTA',
        'CASETA INTERMEDIA KIA',
        'ENTRADA PATIOS FERROVIARIOS',
        'ENTRADA KIA',
        'GUARDIA RECEPCION 2',
        'PLUMA 200',
        'GUARDIA ARMADO TALLER',
        'GUARDIA ARMADO HOLLAND',
        'RONDINERO',
        'GUARDIA PORTON',
        'RECEPCIONISTA 1',
        'RECEPCIONISTA 2',
        'PROTECCION'
    ];

    public $searches = [];
    public $usersByPoint = [];
    public $users = [];

    public function mount()
    {
        $this->users = User::whereIn('punto', ['KANSAS', 'MTY', 'Kansas'])
            ->where('estatus', 'Activo')
            ->get();

        foreach ($this->puntos as $punto) {
            $this->searches[$punto] = '';
            $this->usersByPoint[$punto] = [];
        }
    }

    public function addUser($punto, $userId)
{
    $user = $this->users->firstWhere('id', $userId);

    if ($user && !isset($this->usersByPoint[$punto][$userId])) {
        $this->usersByPoint[$punto][$userId] = [
            'user' => $user,
            'turno_dia' => null,
            'turno_noche' => null
        ];

        $this->searches[$punto] = '';
    }
}

    public function removeUser($punto, $userId)
    {
        unset($this->usersByPoint[$punto][$userId]);
    }

    public function setTurno($punto, $userId, $turno, $valor)
    {
        if (isset($this->usersByPoint[$punto][$userId])) {
            $this->usersByPoint[$punto][$userId][$turno] = $valor === '' ? null : $valor;
        }
    }

    public function save()
{
    $user = Auth::user();
    $now = now('America/Mexico_City');

    $asistencias = [];
    $descansos = [];
    $faltasExplicitas = [];

    // Recorrer todos los usuarios asignados en la tabla
    foreach ($this->usersByPoint as $punto => $usuarios) {
        foreach ($usuarios as $userId => $data) {
            $dia = $data['turno_dia'];
            $noche = $data['turno_noche'];

            // Si tiene A en algún turno → asistencia (prioridad máxima)
            if ($dia === 'A' || $noche === 'A') {
                $asistencias[] = (int) $userId;
            }
            // Si tiene D en algún turno → descanso
            elseif ($dia === 'D' || $noche === 'D') {
                $descansos[] = (int) $userId;
            }
            // Si tiene F en algún turno → falta explícita
            elseif ($dia === 'F' || $noche === 'F') {
                $faltasExplicitas[] = (int) $userId;
            }
            // Si no tiene nada → también es falta, pero la manejamos después
        }
    }

    // Eliminar duplicados
    $asistencias = array_unique($asistencias);
    $descansos = array_unique($descansos);
    $faltasExplicitas = array_unique($faltasExplicitas);

    // Obtener todos los usuarios activos de KANSAS/MTY (como en tu flujo original)
    $todosUsuarios = User::whereIn('punto', ['KANSAS', 'MTY'])
        ->where('estatus', 'Activo')
        ->where('rol', '!=', 'Supervisor')
        ->pluck('id')
        ->toArray();

    // Usuarios que SÍ están en la tabla (asignados a algún punto)
    $usuariosAsignados = [];
    foreach ($this->usersByPoint as $usuarios) {
        foreach ($usuarios as $userId => $data) {
            $usuariosAsignados[] = (int) $userId;
        }
    }
    $usuariosAsignados = array_unique($usuariosAsignados);

    // Faltas implícitas: usuarios que NO están en la tabla
    $faltasImplicitas = array_diff($todosUsuarios, $usuariosAsignados);

    // Faltas totales = explícitas + implícitas
    $faltasTotales = array_values(array_unique(array_merge($faltasExplicitas, $faltasImplicitas)));

    // Eliminar de faltasTotales a quienes están en asistencias o descansos (por si acaso)
    $faltasTotales = array_values(array_diff($faltasTotales, array_merge($asistencias, $descansos)));

    // Guardar
    DB::beginTransaction();
try {
    $asistencia = Asistencia::create([
        'user_id' => $user->id,
        'fecha' => $now->toDateString(),
        'hora_asistencia' => $now->toTimeString(),
        'elementos_enlistados' => json_encode(array_values($asistencias)),
        'faltas' => json_encode(array_values($faltasTotales)),
        'descansos' => json_encode(array_values($descansos)),
        'coberturas' => json_encode([]), // sigue vacío
        'observaciones' => 'Registro masivo CPKC',
        'punto' => $user->punto,
        'empresa' => $user->empresa,
        'fotos_asistentes' => json_encode([]),
    ]);

    // ✅ NUEVO: Guardar asignaciones de punto (solo para KANSAS/MTY)
    $asignaciones = [];
    foreach ($this->usersByPoint as $punto => $usuarios) {
        foreach ($usuarios as $userId => $data) {
            $dia = $data['turno_dia'];
            $noche = $data['turno_noche'];

            // Si tiene turno día, insertar un registro
            if ($dia !== null) {
                $asignaciones[] = [
                    'asistencia_id' => $asistencia->id,
                    'user_id' => $userId,
                    'punto' => $punto,
                    'turno' => 'dia',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Si tiene turno noche, insertar otro registro
            if ($noche !== null) {
                $asignaciones[] = [
                    'asistencia_id' => $asistencia->id,
                    'user_id' => $userId,
                    'punto' => $punto,
                    'turno' => 'noche',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
    }

    if (!empty($asignaciones)) {
        \App\Models\AsistenciaPunto::insert($asignaciones);
    }

    DB::commit();
    session()->flash('success', 'Asistencia registrada correctamente.');
    return redirect()->route('dashboard');

} catch (\Exception $e) {
    DB::rollBack();
    Log::error('Error al guardar asistencia CPKC: ' . $e->getMessage());
    $this->dispatch('alert', type: 'error', message: 'Error al guardar: ' . $e->getMessage());
}
}

    public function render()
{
    $users = User::whereIn('punto', ['KANSAS', 'MTY'])
        ->where('estatus', 'Activo')
        ->where('rol', '!=', 'Supervisor')
        ->get();

    return view('livewire.asistencia-cpkc', compact('users'));
}
}
