<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Asistencia;
use App\Models\TiemposExtra;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AsistenciasTabla extends Component
{
    public $punto = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';

    protected $queryString = ['punto', 'fecha_inicio', 'fecha_fin'];

    public function render()
    {
        $datos = $this->obtenerDatos();

        return view('livewire.asistencias-tabla', [
            'usuarios' => $datos['usuarios'],
            'fechas' => $datos['fechas'],
            'vacacionesPorUsuario' => $datos['vacacionesPorUsuario'],
            'asistenciasIndexadas' => $datos['asistenciasIndexadas'],
            'horasExtrasPorUsuario' => $datos['horasExtrasPorUsuario'],
            'subpuntosMap' => $this->getSubpuntosPorPunto(), // <-- Agregamos esta línea
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

        // Determinar si es un subpunto o un punto general
        $puntoGeneral = null;
        $subpuntos = [];

        foreach ($this->getSubpuntosPorPunto() as $p => $subs) {
            if ($filtro === $p) {
                $puntoGeneral = $p;
                $subpuntos = $subs;
                break;
            } elseif (in_array($filtro, $subs)) {
                $puntoGeneral = $p;
                $subpuntos = [$filtro]; // Filtrar solo por ese subpunto
                break;
            }
        }

        // Si no coincide con ningún punto/subpunto, usar el filtro directamente como punto
        if (!$puntoGeneral) {
            $puntoGeneral = $filtro;
            $subpuntos = [$filtro];
        }

        $asistenciasIndexadas = Asistencia::where('punto', $puntoGeneral)
            ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->fecha)->format('Y-m-d'));

        $usuarios = User::where('estatus', 'Activo')
            ->where(function ($query) use ($subpuntos) {
                foreach ($subpuntos as $subpunto) {
                    $query->orWhereRaw('LOWER(punto) LIKE ?', ['%' . strtolower($subpunto) . '%']);
                }
            })
            ->get()
            ->filter(function ($user) {
                $rol = $this->normalize($user->rol);
                return in_array($rol, ['patrullero', 'guardia']);
            })
            // Añadimos el ordenamiento aquí
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
        return [
            'MONTERREY' => [
                'MONTERREY','CUSTODIO', 'DAL TILE', 'TORRE NOVO', 'TRASLADOS',
                'BONETERA', 'HOME DEPO', 'AMERICAN AIRLINES',
                'MARY KAY CORPORATIVO', 'KANSAS', 'CIMARRON', 'OFICINA',
                'ASSET', 'TORRE DELTA', 'SACMI DE MEXICO',
                'THERMO ELÉCTRICA', 'KINDEER MORGAN', 'GOBAR',
                'PEMCORP #2', 'ROCHE BOBOIS', 'OFF ON GREEN',
                'COOPER LIGHT', 'MONTE PALATINO', 'OATEY', 'PLAZA DOMENA'
            ],
            'GUANAJUATO' => ['SILAO', 'CELAYA', 'SALAMANCA'],
            'NUEVO LAREDO' => ['ZONA DE ABASTOS V'],
            'MEXICO' => ['VALLE DE MEXICO'],
            'SLP' => ['WATCO', 'BMW', 'ZONA DE ABASTOS I', 'INTERPUERTO Y TALLER'],
            'XALAPA' => ['XALAPA'],
            'MICHOACAN' => ['MICHOACÁN'],
            'PUEBLA' => ['PUEBLA'],
            'TOLUCA' => ['TOLUCA'],
            'QUERETARO' => ['QUERÉTARO'],
            'SALTILLO' => ['SALTILLO'],
            'DRONES' => ['DRONES'],
            'KANSAS' => ['KANSAS'],
        ];
    }

    protected function normalize($string)
    {
        return strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $string));
    }
}
