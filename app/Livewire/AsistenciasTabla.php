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
    public $tipoFiltro = '';
    public $usuariosConAlerta = [];

    protected $queryString = ['punto', 'fecha_inicio', 'fecha_fin'];

    private function calcularAlertas($usuarios)
    {
        $this->usuariosConAlerta = [];

        foreach ($usuarios as $usuario) {
            // Obtener las últimas 2 asistencias del usuario (ordenadas por fecha descendente)
            $ultimasAsistencias = Asistencia::whereJsonContains('faltas', $usuario->id)
                ->where('fecha', '<=', $this->fecha_fin)
                ->orderBy('fecha', 'desc')
                ->limit(2)
                ->get();

            // Si tiene exactamente 2 asistencias y ambas contienen al usuario en faltas
            if ($ultimasAsistencias->count() === 2) {
                $primera = $ultimasAsistencias[0];
                $segunda = $ultimasAsistencias[1];

                $faltasPrimera = json_decode($primera->faltas, true) ?? [];
                $faltasSegunda = json_decode($segunda->faltas, true) ?? [];

                if (in_array($usuario->id, $faltasPrimera) && in_array($usuario->id, $faltasSegunda)) {
                    $this->usuariosConAlerta[] = $usuario->id;
                }
            }
        }
    }

    public function render()
    {
        $datos = $this->obtenerDatos();

        // Filtrar subpuntos según el rol del usuario autenticado
        $subpuntosMap = $this->getSubpuntosPorPunto();
        $rol = Auth::user()?->rol;

        if ($rol === 'AUXILIAR OPERACIONES') {
            // Si es AUXILIAR OPERACIONES, solo mostramos Monterrey y sus subpuntos
            $subpuntosMap = [
                'MONTERREY' => $subpuntosMap['MONTERREY'] ?? []
            ];
        }

        return view('livewire.asistencias-tabla', [
            'usuarios' => $datos['usuarios'],
            'fechas' => $datos['fechas'],
            'vacacionesPorUsuario' => $datos['vacacionesPorUsuario'],
            'asistenciasIndexadas' => $datos['asistenciasIndexadas'],
            'horasExtrasPorUsuario' => $datos['horasExtrasPorUsuario'],
            'subpuntosMap' => $subpuntosMap,
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

        if (!$puntoGeneral && in_array($filtro, ['MARYKAY CORPORATIVO', 'MARY KAY CORPORATIVO'])) {
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

        if ($filtro === 'MONTERREY') {
            // Obtener todos los subpuntos de Monterrey
            $monterreySubpuntos = collect($this->getSubpuntosPorPunto()['MONTERREY'])->pluck('nombre')->toArray();
            $puntosAsistencias = array_merge(['MONTERREY'], $monterreySubpuntos, ['KANSAS', 'MTY']);
        } else {
            // Si no, solo el punto seleccionado
            $puntosAsistencias = [$filtro];
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

        if ($filtro === 'MONTERREY') {
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

        // Filtrar usuarios según el tipo seleccionado
        if ($this->tipoFiltro === 'asistencias') {
            // Mostrar solo usuarios que asistieron al menos un día
            $usuarios = $usuarios->filter(function ($user) use ($asistenciasIndexadas) {
                foreach ($asistenciasIndexadas as $asistencia) {
                    $enlistados = json_decode($asistencia->elementos_enlistados, true) ?? [];
                    if (in_array($user->id, $enlistados)) {
                        return true;
                    }
                }
                return false;
            });
        } elseif ($this->tipoFiltro === 'faltas') {
            // Mostrar solo usuarios que faltaron al menos un día
            $usuarios = $usuarios->filter(function ($user) use ($asistenciasIndexadas) {
                foreach ($asistenciasIndexadas as $asistencia) {
                    $faltantes = json_decode($asistencia->faltas, true) ?? [];
                    if (in_array($user->id, $faltantes)) {
                        return true;
                    }
                }
                return false;
            });
        } elseif ($this->tipoFiltro === 'descansos') {
            $usuarios = $usuarios->filter(function ($user) use ($asistenciasIndexadas) {
                foreach ($asistenciasIndexadas as $asistencia) {
                    $descansantes = json_decode($asistencia->descansos, true) ?? [];
                    if (in_array($user->id, $descansantes)) {
                        return true;
                    }
                }
                return false;
            });
        }

        $this->calcularAlertas($usuarios);

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
        ];
    }

    protected function normalize($string)
    {
        return strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $string));
    }
}
