<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Punto;
use App\Models\Subpunto;
use App\Models\Asistencia;
use App\Services\CalculoDestajoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DestajosTabla extends Component
{
    public $punto = '';
    public $empresa = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';

    protected $puntosAsignadosMap = [];
    private CalculoDestajoService $destajoService;
    protected $queryString = ['punto', 'empresa', 'fecha_inicio', 'fecha_fin'];

    public function __construct()
    {
        $this->destajoService = app(CalculoDestajoService::class);
    }

    public function render()
    {
        $datos = $this->obtenerDatos();
        $subpuntosMap = $this->getSubpuntosPorPunto();

        $rol = Auth::user()?->rol;
        if ($rol === 'AUXILIAR OPERACIONES') {
            $subpuntosMap = ['MONTERREY' => $subpuntosMap['MONTERREY'] ?? []];
        }

        return view('livewire.destajos-tabla', [
            'usuarios' => $datos['usuarios'],
            'fechas' => $datos['fechas'],
            'destajosPorUsuario' => $datos['destajosPorUsuario'],
            'totalesGenerales' => $datos['totalesGenerales'],
            'subpuntosMap' => $subpuntosMap,
        ]);
    }

    public function obtenerDatos()
    {
        // === VALIDACIÓN DE FILTROS ===
        // Fechas obligatorias
        if (!$this->fecha_inicio || !$this->fecha_fin) {
            return [
                'usuarios' => collect(),
                'fechas' => [],
                'destajosPorUsuario' => [],
                'totalesGenerales' => ['dias_trabajados' => 0, 'total_monto' => 0]
            ];
        }

        // Al menos uno de: punto o empresa
        if (empty($this->punto) && empty($this->empresa)) {
            return [
                'usuarios' => collect(),
                'fechas' => [],
                'destajosPorUsuario' => [],
                'totalesGenerales' => ['dias_trabajados' => 0, 'total_monto' => 0]
            ];
        }

        // === FILTRADO DE PUNTOS ===
        $filtro = strtoupper($this->punto);
        if (in_array($filtro, ['MARYKAY CORPORATIVO', 'MAR KAY CORPORATIVO'])) {
            $filtro = 'MARY KAY CORPORATIVO';
        }

        $puntoGeneral = null;
        $subpuntos = [];
        $mapaSubpuntos = $this->getSubpuntosPorPunto();

        foreach ($mapaSubpuntos as $p => $subs) {
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
            $subpuntos = [collect($mapaSubpuntos['MONTERREY'])->firstWhere('nombre', 'LIKE', $filtro)];
        }

        if (!$puntoGeneral && !empty($this->punto)) {
            $puntoGeneral = $filtro;
            $subpuntos = [['nombre' => $filtro, 'codigo' => null]];
        }

        $rolAuth = Auth::user()?->rol;
        if ($rolAuth === 'AUXILIAR OPERACIONES') {
            $puntoGeneral = 'MONTERREY';
            $subpuntos = $mapaSubpuntos['MONTERREY'];
        }

        // Puntos para buscar asistencias
        $puntosAsistencias = [];
        if (!empty($this->punto)) {
            if ($filtro === 'MONTERREY' || $rolAuth === 'AUXILIAR OPERACIONES') {
                $monterreySubpuntos = collect($mapaSubpuntos['MONTERREY'])->pluck('nombre')->toArray();
                $puntosAsistencias = array_merge(['MONTERREY'], $monterreySubpuntos, ['KANSAS', 'MTY']);
            } else {
                $puntosAsistencias = [$filtro];
            }
        }

        // === OBTENER ASISTENCIAS ===
        $asistenciasQuery = Asistencia::whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin]);

        if (!empty($puntosAsistencias)) {
            $asistenciasQuery->whereIn('punto', $puntosAsistencias);
        }

        $asistenciasIndexadas = $asistenciasQuery->get()->keyBy(fn($a) => Carbon::parse($a->fecha)->format('Y-m-d'));

        // === OBTENER USUARIOS ===
        $usuariosQuery = User::with('solicitudAlta')->where('estatus', 'Activo');

        // Filtro por empresa
        if (!empty($this->empresa)) {
            $usuariosQuery->where('empresa', $this->empresa);
        }

        // Filtro por punto
        if (!empty($this->punto)) {
            $usuariosQuery->where(function ($query) use ($subpuntos, $puntoGeneral) {
                foreach ($subpuntos as $subpunto) {
                    $nombre = $subpunto['nombre'] ?? null;
                    $codigo = $subpunto['codigo'] ?? null;

                    $query->orWhere(function ($q) use ($nombre, $codigo, $puntoGeneral) {
                        if ($nombre) {
                            $q->whereRaw('LOWER(punto) LIKE ?', ['%' . strtolower($nombre) . '%']);
                            if ($nombre === 'MARY KAY CORPORATIVO') {
                                $q->orWhereRaw('LOWER(punto) LIKE ?', ['%marykay corporativo%'])
                                  ->orWhereRaw('LOWER(punto) LIKE ?', ['%mar kay corporativo%']);
                            }
                        }
                        if ($codigo && $puntoGeneral === 'MONTERREY') {
                            $q->orWhere('punto', $codigo);
                        }
                    });
                }
            });

            if ($filtro === 'MONTERREY' || $rolAuth === 'AUXILIAR OPERACIONES') {
                $usuariosQuery->orWhere(function ($q) {
                    $q->where('punto', 'KANSAS')->orWhere('punto', 'MTY');
                });
            }
        }

        $usuarios = $usuariosQuery->get()->sortBy(function ($user) {
            if ($user->solicitudAlta) {
                $s = $user->solicitudAlta;
                return strtolower(trim(($s->apellido_paterno ?? '') . ' ' . ($s->apellido_materno ?? '') . ' ' . ($s->nombre ?? '')));
            }
            return strtolower($user->name ?? '');
        })->values();

        // === FECHAS Y DATOS AUXILIARES ===
        $fechas = [];
        $startDate = Carbon::parse($this->fecha_inicio);
        $endDate = Carbon::parse($this->fecha_fin);
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
                        ->orWhereBetween('fecha_fin', [$this->fecha_inicio, $this->fecha_fin]);
                })->get();
            $dias = collect();
            foreach ($vacaciones as $vac) {
                $inicio = Carbon::parse($vac->fecha_inicio);
                $fin = Carbon::parse($vac->fecha_fin);
                for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) $dias->push($d->format('Y-m-d'));
            }
            $vacacionesPorUsuario[$user->id] = $dias->toArray();
        }

        $permisosPorUsuario = [];
        $permisos = \App\Models\PermisoEspecial::where(function($q) {
            $q->whereBetween('fecha_inicio', [$this->fecha_inicio, $this->fecha_fin])
              ->orWhereBetween('fecha_fin', [$this->fecha_inicio, $this->fecha_fin]);
        })->get();

        foreach ($permisos as $permiso) {
            $inicio = Carbon::parse($permiso->fecha_inicio);
            $fin = Carbon::parse($permiso->fecha_fin);
            for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
                $permisosPorUsuario[$permiso->user_id][$d->format('Y-m-d')] = ['con_goce' => (int) $permiso->con_goce === 1];
            }
        }

        $incapacidadesPorUsuario = [];
        $incapacidades = \App\Models\Incapacidad::where(function ($q) {
            $q->whereBetween('fecha_inicio', [$this->fecha_inicio, $this->fecha_fin]);
        })->orWhere(function ($q) {
            $q->whereDate(\DB::raw('DATE_ADD(fecha_inicio, INTERVAL dias_incapacidad - 1 DAY)'), '>=', $this->fecha_inicio)
              ->where('fecha_inicio', '<=', $this->fecha_fin);
        })->get();

        foreach ($incapacidades as $inc) {
            $inicio = Carbon::parse($inc->fecha_inicio);
            $fin = $inicio->copy()->addDays($inc->dias_incapacidad - 1);
            for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
                $fecha = $d->format('Y-m-d');
                if (Carbon::parse($fecha)->between(Carbon::parse($this->fecha_inicio), Carbon::parse($this->fecha_fin))) {
                    $incapacidadesPorUsuario[$inc->user_id][] = $fecha;
                }
            }
        }

        // === CALCULAR DESTAJOS ===
        $destajosPorUsuario = [];
        $totalesGenerales = ['dias_trabajados' => 0, 'total_monto' => 0];

        foreach ($usuarios as $user) {
            try {
                $resultado = $this->destajoService->calcularDestajo(
                    $user,
                    $this->fecha_inicio,
                    $this->fecha_fin,
                    [
                        'vacacionesPorUsuario' => $vacacionesPorUsuario,
                        'asistenciasIndexadas' => $asistenciasIndexadas,
                        'permisosPorUsuario' => $permisosPorUsuario,
                        'incapacidadesPorUsuario' => $incapacidadesPorUsuario,
                    ]
                );
                if ($resultado['success']) {
                    $destajosPorUsuario[$user->id] = $resultado;
                    $totalesGenerales['dias_trabajados'] += $resultado['dias_trabajados'];
                    $totalesGenerales['total_monto'] += $resultado['total_monto'];
                }
            } catch (\Exception $e) {}
        }

        return [
            'usuarios' => $usuarios,
            'fechas' => $fechas,
            'destajosPorUsuario' => $destajosPorUsuario,
            'totalesGenerales' => $totalesGenerales,
        ];
    }

    protected function getSubpuntosPorPunto()
    {
        $monterreyId = Punto::where('nombre', 'MONTERREY')->value('id');
        $codigos = $monterreyId ? Subpunto::where('punto_id', $monterreyId)->pluck('codigo', 'nombre')->toArray() : [];
        $codigoMaryKay = $codigos['MARY KAY CORPORATIVO'] ?? $codigos['MARYKAY CORPORATIVO'] ?? $codigos['MAR KAY CORPORATIVO'] ?? null;
        $monterreySubpuntos = [
            ['nombre' => 'MONTERREY', 'codigo' => $codigos['MONTERREY'] ?? null], ['nombre' => 'CUSTODIO', 'codigo' => $codigos['CUSTODIO'] ?? null],
            ['nombre' => 'DALTILE', 'codigo' => $codigos['DALTILE'] ?? null], ['nombre' => 'TORRENOVO', 'codigo' => $codigos['TORRENOVO'] ?? null],
            ['nombre' => 'TRASLADOS', 'codigo' => $codigos['TRASLADOS'] ?? null], ['nombre' => 'BONETERA', 'codigo' => $codigos['BONETERA'] ?? null],
            ['nombre' => 'HOMEDEPOT', 'codigo' => $codigos['HOMEDEPOT'] ?? null], ['nombre' => 'AMERICAN AIRLINES', 'codigo' => $codigos['AMERICAN AIRLINES'] ?? null],
            ['nombre' => 'MARY KAY CORPORATIVO', 'codigo' => $codigoMaryKay], ['nombre' => 'KANSAS', 'codigo' => $codigos['KANSAS'] ?? null],
            ['nombre' => 'CIMARRON', 'codigo' => $codigos['CIMARRON'] ?? null], ['nombre' => 'OFICINA', 'codigo' => $codigos['OFICINA'] ?? null],
            ['nombre' => 'ASSET', 'codigo' => $codigos['ASSET'] ?? null], ['nombre' => 'TORRE DELTA', 'codigo' => $codigos['TORRE DELTA'] ?? null],
            ['nombre' => 'SACMI DE MEXICO', 'codigo' => $codigos['SACMI DE MEXICO'] ?? null], ['nombre' => 'THERMO ELÉCTRICA', 'codigo' => $codigos['THERMO ELÉCTRICA'] ?? null],
            ['nombre' => 'KINDER MORGAN', 'codigo' => $codigos['KINDER MORGAN'] ?? null], ['nombre' => 'GOBAR', 'codigo' => $codigos['GOBAR'] ?? null],
            ['nombre' => 'PEMCORP #2', 'codigo' => $codigos['PEMCORP #2'] ?? null], ['nombre' => 'ROCHE BOBOIS', 'codigo' => $codigos['ROCHE BOBOIS'] ?? null],
            ['nombre' => 'OFF ON GREEN', 'codigo' => $codigos['OFF ON GREEN'] ?? null], ['nombre' => 'COOPER LIGHT', 'codigo' => $codigos['COOPER LIGHT'] ?? null],
            ['nombre' => 'MONTE PALATINO', 'codigo' => $codigos['MONTE PALATINO'] ?? null], ['nombre' => 'OATEY', 'codigo' => $codigos['OATEY'] ?? null],
            ['nombre' => 'PLAZA DOMENA', 'codigo' => $codigos['PLAZA DOMENA'] ?? null],
        ];
        return [
            'MONTERREY' => $monterreySubpuntos,
            'GUANAJUATO' => [['nombre' => 'SILAO', 'codigo' => null], ['nombre' => 'CELAYA', 'codigo' => null], ['nombre' => 'SALAMANCA', 'codigo' => null]],
            'NUEVO LAREDO' => [['nombre' => 'ZONA DE ABASTOS V', 'codigo' => null]],
            'MEXICO' => [['nombre' => 'VALLE DE MEXICO', 'codigo' => null]],
            'SLP' => [['nombre' => 'WATCO', 'codigo' => null], ['nombre' => 'BMW', 'codigo' => null], ['nombre' => 'ZONA DE ABASTOS I', 'codigo' => null], ['nombre' => 'INTERPUERTO Y TALLER', 'codigo' => null]],
            'XALAPA' => [['nombre' => 'XALAPA', 'codigo' => null]],
            'MICHOACAN' => [['nombre' => 'MICHOACÁN', 'codigo' => null]],
            'PUEBLA' => [['nombre' => 'PUEBLA', 'codigo' => null]],
            'TOLUCA' => [['nombre' => 'TOLUCA', 'codigo' => null]],
            'QUERETARO' => [['nombre' => 'QUERÉTARO', 'codigo' => null]],
            'SALTILLO' => [['nombre' => 'SALTILLO', 'codigo' => null]],
            'DRONES' => [['nombre' => 'DRONES', 'codigo' => null]],
        ];
    }
}
