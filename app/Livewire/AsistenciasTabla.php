<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Punto;
use App\Models\Subpunto;
use App\Models\Asistencia;
use App\Models\TiemposExtra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AsistenciasTabla extends Component
{
    public $punto = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    protected $puntosAsignadosMap = [];

    protected $queryString = ['punto', 'fecha_inicio', 'fecha_fin'];

    public function render()
    {
        $datos = $this->obtenerDatos();

        // Filtrar subpuntos según el rol del usuario autenticado
        $subpuntosMap = $this->getSubpuntosPorPunto();
        $rol = Auth::user()?->rol; // Obtenemos el rol del usuario autenticado

        if ($rol === 'AUXILIAR OPERACIONES') {
            // Si es AUXILIAR OPERACIONES, solo mostramos Monterrey y sus subpuntos
            $subpuntosMap = [
                'MONTERREY' => $subpuntosMap['MONTERREY'] ?? []
            ];
        }
        // Si no, se devuelve el mapa completo (comportamiento por defecto)

        return view('livewire.asistencias-tabla', [
            'usuarios' => $datos['usuarios'],
            'fechas' => $datos['fechas'],
            'vacacionesPorUsuario' => $datos['vacacionesPorUsuario'],
            'asistenciasIndexadas' => $datos['asistenciasIndexadas'],
            'horasExtrasPorUsuario' => $datos['horasExtrasPorUsuario'],
            'subpuntosMap' => $subpuntosMap, // <-- Pasamos el mapa filtrado
        ]);
    }

    public function updated($property)
    {
        // Opcional: vuelve a renderizar al cambiar un filtro
        // $this->render();
    }

public function obtenerDatos()
{
    if (!$this->fecha_inicio || !$this->fecha_fin) {
        return [
            'usuarios' => collect(),
            'fechas' => [],
            'vacacionesPorUsuario' => [],
            'asistenciasIndexadas' => collect(),
            'horasExtrasPorUsuario' => [],
        ];
    }

    $filtro = strtoupper($this->punto);
    if (in_array($filtro, ['MARYKAY CORPORATIVO', 'MAR KAY CORPORATIVO'])) {
        $filtro = 'MARY KAY CORPORATIVO';
    }

    // Determinar si es un subpunto o un punto general
    $puntoGeneral = null;
    $subpuntos = [];

    foreach ($this->getSubpuntosPorPunto() as $p => $subs) {
        if ($filtro === $p) {
            $puntoGeneral = $p;
            $subpuntos = $subs;
            break;
        } elseif (collect($subs)->pluck('nombre')->map('strtoupper')->contains($filtro)) {
            $puntoGeneral = $p;
            $subpuntos = [collect($subs)->firstWhere('nombre', 'LIKE', $filtro)];
            break;
        } elseif (collect($subs)->pluck('codigo')->map('strval')->contains($filtro)) {
            $puntoGeneral = $p;
            $subpuntos = [collect($subs)->firstWhere('codigo', $filtro)];
            break;
        }
    }

    if (!$puntoGeneral && in_array($filtro, ['MARYKAY CORPORATIVO', 'MAR KAY CORPORATIVO'])) {
        $puntoGeneral = 'MONTERREY';
        $subpuntos = [
            collect($this->getSubpuntosPorPunto()['MONTERREY'])->firstWhere('nombre', 'LIKE', $filtro)
        ];
    }

    if (!$puntoGeneral) {
        $puntoGeneral = $filtro;
        $subpuntos = [['nombre' => $filtro, 'codigo' => null]];
    }

    $rol = Auth::user()?->rol;
    if ($rol === 'AUXILIAR OPERACIONES') {
        $puntoGeneral = 'MONTERREY';
        $subpuntos = $this->getSubpuntosPorPunto()['MONTERREY'];
    }

    $puntosAsistencias = [$puntoGeneral];
    if ($puntoGeneral === 'MONTERREY') {
        $puntosAsistencias = ['MONTERREY', 'KANSAS', 'MTY'];
    }

    $asistenciasIndexadas = Asistencia::with('puntosAsignados', 'usuario')
        ->whereIn('punto', $puntosAsistencias)
        ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
        ->get()
        ->keyBy(fn($a) => Carbon::parse($a->fecha)->format('Y-m-d'));

    $puntosAsignadosMap = [];
    foreach ($asistenciasIndexadas as $fecha => $asistencia) {
        if (in_array($asistencia->usuario->punto, ['KANSAS', 'MTY'])) {
            $puntosAsignadosMap[$fecha] = $asistencia->puntosAsignados->pluck('punto', 'user_id')->toArray();
        }
    }

    $this->puntosAsignadosMap = $puntosAsignadosMap;

    $usuarios = User::where('estatus', 'Activo')
        ->where(function ($query) use ($subpuntos, $puntoGeneral) {
            foreach ($subpuntos as $subpunto) {
                $nombre = $subpunto['nombre'] ?? null;
                $codigo = $subpunto['codigo'] ?? null;

                $query->orWhere(function ($q) use ($nombre, $codigo, $puntoGeneral) {
                    if ($nombre) {
                        $q->whereRaw('LOWER(punto) LIKE ?', ['%' . strtolower($nombre) . '%']);
                    }
                    if ($nombre === 'MARY KAY CORPORATIVO') {
                        $q->orWhereRaw('LOWER(punto) LIKE ?', ['%marykay corporativo%'])
                          ->orWhereRaw('LOWER(punto) LIKE ?', ['%mar kay corporativo%']);
                    }
                    if ($codigo && $puntoGeneral === 'MONTERREY') {
                        $q->orWhere('punto', $codigo);
                    }
                });
            }
        });

    if ($puntoGeneral === 'MONTERREY') {
        $usuarios->orWhere(function ($q) {
            $q->where('punto', 'KANSAS')
              ->orWhere('punto', 'MTY');
        });
    }

    $usuarios = $usuarios->get()
        ->filter(function ($user) {
            $rol = $this->normalize($user->rol);
            return in_array($rol, ['patrullero', 'guardia']);
        })
        ->sortBy([
            ['punto', 'asc'],
            ['name', 'asc']
        ]);

    $startDate = Carbon::parse($this->fecha_inicio);
    $endDate = Carbon::parse($this->fecha_fin);
    $fechas = [];
    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
        $fechas[] = $date->format('Y-m-d');
    }

    $vacacionesPorUsuario = [];
    foreach ($usuarios as $user) {
        $vacaciones = DB::table('solicitud_vacaciones')
            ->where('user_id', $user->id)
            ->where('estatus', 'Aceptada')
            ->where(function ($query) {
                $query->whereBetween('fecha_inicio', [$this->fecha_inicio, $this->fecha_fin])
                    ->orWhereBetween('fecha_fin', [$this->fecha_inicio, $this->fecha_fin])
                    ->orWhere(function ($q) {
                        $q->where('fecha_inicio', '<', $this->fecha_inicio)
                            ->where('fecha_fin', '>', $this->fecha_fin);
                    });
            })
            ->get();

        $dias = collect();
        foreach ($vacaciones as $vac) {
            $inicio = Carbon::parse($vac->fecha_inicio);
            $fin = Carbon::parse($vac->fecha_fin);
            for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
                $dias->push($d->format('Y-m-d'));
            }
        }
        $vacacionesPorUsuario[$user->id] = $dias->toArray();
    }

    $horasExtrasPorUsuario = [];
    foreach ($usuarios as $user) {
        $registros = TiemposExtra::where('user_id', $user->id)
            ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->get();

        $porDia = [];
        foreach ($registros as $r) {
            $dia = Carbon::parse($r->fecha)->format('Y-m-d');
            $horas = (int) Carbon::parse($r->total_horas)->format('H');
            $porDia[$dia] = ($porDia[$dia] ?? 0) + $horas;
        }
        $horasExtrasPorUsuario[$user->id] = $porDia;
    }

    return [
        'usuarios' => $usuarios,
        'fechas' => $fechas,
        'vacacionesPorUsuario' => $vacacionesPorUsuario,
        'asistenciasIndexadas' => $asistenciasIndexadas,
        'horasExtrasPorUsuario' => $horasExtrasPorUsuario,
    ];
}

protected function getSubpuntosPorPunto()
{
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
        //'KANSAS' => [
            //['nombre' => 'KANSAS', 'codigo' => null],
        //],
    ];
}

    protected function normalize($string)
    {
        return strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $string));
    }
}
