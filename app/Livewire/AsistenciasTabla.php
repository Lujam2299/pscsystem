<?php

namespace App\Livewire;

use App\Models\Asistencia;
use App\Models\Punto;
use App\Models\Retardo;
use App\Models\Subpunto;
use App\Models\TiemposExtra;
use App\Models\User;
use App\Services\CalculoNominaService;
use App\Support\Authorization\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class AsistenciasTabla extends Component
{
    public function boot(): void
    {
        Gate::authorize(Permission::ATTENDANCE_VIEW);
    }

    public $punto = '';

    public $fecha_inicio = '';

    public $fecha_fin = '';

    protected $puntosAsignadosMap = [];

    public $tipoFiltro = '';

    public $usuariosConAlerta = [];

    public $showModal = false;

    public $detalleNomina = null;

    public $userIdModal = null;

    private CalculoNominaService $calculoService;

    protected $queryString = ['punto', 'fecha_inicio', 'fecha_fin'];

    public function __construct()
    {
        $this->calculoService = app(CalculoNominaService::class);
    }

    public function mostrarDetalleNomina(int $userId)
    {
        $user = User::find($userId);
        if (! $user) {
            session()->flash('error', 'Usuario no encontrado');

            return;
        }

        $datosAsistencias = $this->obtenerDatos();
        $resultado = $this->calculoService->calcularPercepciones(
            $user,
            $this->fecha_inicio,
            $this->fecha_fin,
            [
                'vacacionesPorUsuario' => $datosAsistencias['vacacionesPorUsuario'],
                'asistenciasIndexadas' => $datosAsistencias['asistenciasIndexadas'],
                'horasExtrasPorUsuario' => $datosAsistencias['horasExtrasPorUsuario'],
                'permisosPorUsuario' => $datosAsistencias['permisosPorUsuario'],
                'faltasJustificadas' => $datosAsistencias['faltasJustificadas'],
                'retardosPorUsuario' => $datosAsistencias['retardosPorUsuario'],
                'incapacidadesPorUsuario' => $datosAsistencias['incapacidadesPorUsuario'],
            ]
        );

        $this->detalleNomina = $resultado;
        $this->userIdModal = $userId;
        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->detalleNomina = null;
        $this->userIdModal = null;
    }

    private function calcularAlertas($usuarios)
    {
        $this->usuariosConAlerta = [];

        foreach ($usuarios as $usuario) {
            // Obtener las últimas 2 asistencias con falta (ordenadas por fecha descendente)
            $ultimasAsistencias = Asistencia::whereJsonContains('faltas', $usuario->id)
                ->where('fecha', '<=', $this->fecha_fin)
                ->orderBy('fecha', 'desc')
                ->limit(2)
                ->get();

            if ($ultimasAsistencias->count() === 2) {
                $primera = $ultimasAsistencias[0];
                $segunda = $ultimasAsistencias[1];

                $faltasPrimera = json_decode($primera->faltas, true) ?? [];
                $faltasSegunda = json_decode($segunda->faltas, true) ?? [];

                // Verificar si el usuario está incapacitado o tiene permiso sin goce en esas fechas
                $fecha1 = Carbon::parse($primera->fecha)->format('Y-m-d');
                $fecha2 = Carbon::parse($segunda->fecha)->format('Y-m-d');

                $todasIncapacidades = $this->obtenerTodasIncapacidadesUsuario($usuario->id, $this->fecha_inicio, $this->fecha_fin);
                $todasPermisosSinGoce = $this->obtenerTodasPermisosSinGoceUsuario($usuario->id, $this->fecha_inicio, $this->fecha_fin);

                // Si alguna fecha tiene incapacidad o permiso sin goce → NO alerta
                if (
                    in_array($fecha1, $todasIncapacidades) ||
                    in_array($fecha2, $todasIncapacidades) ||
                    in_array($fecha1, $todasPermisosSinGoce) ||
                    in_array($fecha2, $todasPermisosSinGoce)
                ) {
                    continue;
                }

                // Solo si ambas fechas son faltas reales (sin I ni PE-SG), activar alerta
                if (in_array($usuario->id, $faltasPrimera) && in_array($usuario->id, $faltasSegunda)) {
                    $this->usuariosConAlerta[] = $usuario->id;
                }
            }
        }
    }

    /**
     * Obtiene todas las fechas de incapacidad del usuario en un rango
     */
    private function obtenerTodasIncapacidadesUsuario(int $userId, string $inicio, string $fin): array
    {
        $incapacidades = \App\Models\Incapacidad::where('user_id', $userId)
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereBetween('fecha_inicio', [$inicio, $fin])
                    ->orWhereDate(\DB::raw('DATE_ADD(fecha_inicio, INTERVAL dias_incapacidad - 1 DAY)'), '>=', $inicio)
                    ->where('fecha_inicio', '<=', $fin)
                    ->orWhere(function ($subq) use ($inicio, $fin) {
                        $subq->where('fecha_inicio', '<', $inicio)
                            ->whereDate(\DB::raw('DATE_ADD(fecha_inicio, INTERVAL dias_incapacidad - 1 DAY)'), '>', $fin);
                    });
            })
            ->get();

        $dias = [];
        foreach ($incapacidades as $incap) {
            $inicioInc = Carbon::parse($incap->fecha_inicio);
            $finInc = $inicioInc->copy()->addDays($incap->dias_incapacidad - 1);

            for ($d = $inicioInc->copy(); $d->lte($finInc); $d->addDay()) {
                $fecha = $d->format('Y-m-d');
                if (Carbon::parse($fecha)->between(Carbon::parse($inicio), Carbon::parse($fin))) {
                    $dias[] = $fecha;
                }
            }
        }

        return array_unique($dias);
    }

    /**
     * Obtiene todas las fechas de permisos sin goce del usuario en un rango
     */
    private function obtenerTodasPermisosSinGoceUsuario(int $userId, string $inicio, string $fin): array
    {
        $permisos = \App\Models\PermisoEspecial::where('user_id', $userId)
            ->where('con_goce', 0) // ✅ Filtrar directamente por 0 (tinyint)
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereBetween('fecha_inicio', [$inicio, $fin])
                    ->orWhereBetween('fecha_fin', [$inicio, $fin])
                    ->orWhere(function ($subq) use ($inicio, $fin) {
                        $subq->where('fecha_inicio', '<', $inicio)
                            ->where('fecha_fin', '>', $fin);
                    });
            })
            ->get();

        $dias = [];
        foreach ($permisos as $permiso) {
            $inicioInc = Carbon::parse($permiso->fecha_inicio);
            $finInc = Carbon::parse($permiso->fecha_fin);

            for ($d = $inicioInc->copy(); $d->lte($finInc); $d->addDay()) {
                $fecha = $d->format('Y-m-d');
                if (Carbon::parse($fecha)->between(Carbon::parse($inicio), Carbon::parse($fin))) {
                    $dias[] = $fecha;
                }
            }
        }

        return array_unique($dias);
    }

    public function render()
    {
        $datos = $this->obtenerDatos();

        $subpuntosMap = $this->getSubpuntosPorPunto();
        $rol = Auth::user()?->rol;

        if ($rol === 'AUXILIAR OPERACIONES') {
            $subpuntosMap = [
                'MONTERREY' => $subpuntosMap['MONTERREY'] ?? [],
            ];
        }

        return view('livewire.asistencias-tabla', [
            'usuarios' => $datos['usuarios'],
            'fechas' => $datos['fechas'],
            'vacacionesPorUsuario' => $datos['vacacionesPorUsuario'],
            'asistenciasIndexadas' => $datos['asistenciasIndexadas'],
            'horasExtrasPorUsuario' => $datos['horasExtrasPorUsuario'],
            'permisosPorUsuario' => $datos['permisosPorUsuario'],
            'faltasJustificadas' => $datos['faltasJustificadas'],
            'retardosPorUsuario' => $datos['retardosPorUsuario'],
            'incapacidadesPorUsuario' => $datos['incapacidadesPorUsuario'],
            'puntosAsignadosMap' => $this->puntosAsignadosMap,
            'subpuntosMap' => $subpuntosMap,
            'nominaPorUsuario' => $datos['nominaPorUsuario'],
            'showModal' => $this->showModal,
            'detalleNomina' => $this->detalleNomina,
            'userIdModal' => $this->userIdModal,
        ]);
    }

    public function updated($property)
    {
        // Opcional: vuelve a renderizar al cambiar un filtro
        // $this->render();
    }

    public function obtenerDatos()
    {
        if (! $this->fecha_inicio || ! $this->fecha_fin) {
            return [
                'usuarios' => collect(),
                'fechas' => [],
                'vacacionesPorUsuario' => [],
                'asistenciasIndexadas' => collect(),
                'horasExtrasPorUsuario' => [],
                'permisosPorUsuario' => [],
                'faltasJustificadas' => [],
                'retardosPorUsuario' => [],
                'incapacidadesPorUsuario' => [],
                'nominaPorUsuario' => [],
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

        if (! $puntoGeneral && in_array($filtro, ['MARYKAY CORPORATIVO', 'MARY KAY CORPORATIVO'])) {
            $puntoGeneral = 'MONTERREY';
            $subpuntos = [
                collect($this->getSubpuntosPorPunto()['MONTERREY'])->firstWhere('nombre', 'LIKE', $filtro),
            ];
        }

        if (! $puntoGeneral) {
            $puntoGeneral = $filtro;
            $subpuntos = [['nombre' => $filtro, 'codigo' => null]];
        }

        $rol = Auth::user()?->rol;
        if ($rol === 'AUXILIAR OPERACIONES') {
            $puntoGeneral = 'MONTERREY';
            $subpuntos = $this->getSubpuntosPorPunto()['MONTERREY'];
        }

        if ($filtro === 'MONTERREY') {
            $monterreySubpuntos = collect($this->getSubpuntosPorPunto()['MONTERREY'])->pluck('nombre')->toArray();
            $puntosAsistencias = array_merge(['MONTERREY'], $monterreySubpuntos, ['KANSAS', 'MTY']);
        } else {
            $puntosAsistencias = [$filtro];
        }

        $asistenciasIndexadas = Asistencia::with('puntosAsignados', 'usuario')
            ->whereIn('punto', $puntosAsistencias)
            ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->get()
            ->groupBy(fn ($a) => Carbon::parse($a->fecha)->format('Y-m-d'));

        $puntosAsignadosMap = [];
        foreach ($asistenciasIndexadas as $fecha => $registros) {
            foreach ($registros as $asistencia) {
                if ($asistencia->usuario && in_array($asistencia->usuario->punto, ['KANSAS', 'MTY'])) {
                    $puntosAsignadosMap[$fecha] = array_replace(
                        $puntosAsignadosMap[$fecha] ?? [],
                        $asistencia->puntosAsignados->pluck('punto', 'user_id')->toArray()
                    );
                }
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
                            $q->whereRaw('LOWER(punto) LIKE ?', ['%'.strtolower($nombre).'%']);
                        }
                        if ($nombre === 'MARY KAY CORPORATIVO') {
                            $q->orWhereRaw('LOWER(punto) LIKE ?', ['%'.strtolower($nombre).'%'])
                                ->orWhereRaw('LOWER(punto) LIKE ?', ['%marykay corporativo%'])
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
                ['name', 'asc'],
            ]);

        // Filtrar usuarios según el tipo seleccionado
        if ($this->tipoFiltro === 'asistencias') {
            $usuarios = $usuarios->filter(function ($user) use ($asistenciasIndexadas) {
                foreach ($asistenciasIndexadas as $registros) {
                    foreach ($registros as $asistencia) {
                        $enlistados = json_decode($asistencia->elementos_enlistados, true) ?? [];
                        if (in_array($user->id, $enlistados)) {
                            return true;
                        }
                    }
                }

                return false;
            });
        } elseif ($this->tipoFiltro === 'faltas') {
            $usuarios = $usuarios->filter(function ($user) use ($asistenciasIndexadas) {
                foreach ($asistenciasIndexadas as $registros) {
                    foreach ($registros as $asistencia) {
                        $faltantes = json_decode($asistencia->faltas, true) ?? [];
                        if (in_array($user->id, $faltantes)) {
                            return true;
                        }
                    }
                }

                return false;
            });
        } elseif ($this->tipoFiltro === 'descansos') {
            $usuarios = $usuarios->filter(function ($user) use ($asistenciasIndexadas) {
                foreach ($asistenciasIndexadas as $registros) {
                    foreach ($registros as $asistencia) {
                        $descansantes = json_decode($asistencia->descansos, true) ?? [];
                        if (in_array($user->id, $descansantes)) {
                            return true;
                        }
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

        // Cargar permisos especiales ✅ CORREGIDO: convertir con_goce a bool
        $permisosPorUsuario = [];
        $permisos = \App\Models\PermisoEspecial::whereBetween('fecha_inicio', [$this->fecha_inicio, $this->fecha_fin])
            ->orWhereBetween('fecha_fin', [$this->fecha_inicio, $this->fecha_fin])
            ->orWhere(function ($q) {
                $q->where('fecha_inicio', '<', $this->fecha_inicio)
                    ->where('fecha_fin', '>', $this->fecha_fin);
            })
            ->get();

        foreach ($permisos as $permiso) {
            $inicio = Carbon::parse($permiso->fecha_inicio);
            $fin = Carbon::parse($permiso->fecha_fin);
            for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
                $fecha = $d->format('Y-m-d');
                // ✅ Corrección crítica: convertir tinyint a booleano explícito
                $permisosPorUsuario[$permiso->user_id][$fecha] = [
                    'tipo' => $permiso->tipo,
                    'con_goce' => (int) $permiso->con_goce === 1, // ← Esto era el problema
                ];
            }
        }

        // Cargar faltas justificadas
        $faltasJustificadas = [];
        $faltasJustificadasQuery = \App\Models\FaltaJustificada::whereIn('fecha', $fechas)
            ->where('tipo', 'justificada')
            ->get();

        foreach ($faltasJustificadasQuery as $falta) {
            $userId = $falta->user_id;
            $fecha = $falta->fecha->format('Y-m-d');
            $faltasJustificadas[$userId][$fecha] = true;
        }

        // Cargar retardos
        $retardosPorUsuario = [];
        $retardosQuery = Retardo::whereIn('fecha', $fechas)
            ->get();

        foreach ($retardosQuery as $retardo) {
            $userId = $retardo->user_id;
            $fecha = $retardo->fecha->format('Y-m-d');
            $minutos = $retardo->minutos_retardo;
            $retardosPorUsuario[$userId][$fecha] = $minutos;
        }

        // Cargar incapacidades
        $incapacidadesPorUsuario = [];
        $incapacidades = \App\Models\Incapacidad::where(function ($q) {
            $q->whereBetween('fecha_inicio', [$this->fecha_inicio, $this->fecha_fin]);
        })
            ->orWhere(function ($q) {
                $q->whereDate(\DB::raw('DATE_ADD(fecha_inicio, INTERVAL dias_incapacidad - 1 DAY)'), '>=', $this->fecha_inicio)
                    ->where('fecha_inicio', '<=', $this->fecha_fin);
            })
            ->orWhere(function ($q) {
                $q->where('fecha_inicio', '<', $this->fecha_inicio)
                    ->whereDate(\DB::raw('DATE_ADD(fecha_inicio, INTERVAL dias_incapacidad - 1 DAY)'), '>', $this->fecha_fin);
            })
            ->get();

        foreach ($incapacidades as $incapacidad) {
            $inicio = Carbon::parse($incapacidad->fecha_inicio);
            $fin = $inicio->copy()->addDays($incapacidad->dias_incapacidad - 1);

            for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
                $fecha = $d->format('Y-m-d');
                if (Carbon::parse($fecha)->between(Carbon::parse($this->fecha_inicio), Carbon::parse($this->fecha_fin))) {
                    $incapacidadesPorUsuario[$incapacidad->user_id][] = $fecha;
                }
            }
        }

        // Calcular nómina para cada usuario
        $nominaPorUsuario = [];
        foreach ($usuarios as $user) {
            try {
                $resultado = $this->calculoService->calcularPercepciones(
                    $user,
                    $this->fecha_inicio,
                    $this->fecha_fin,
                    [
                        'vacacionesPorUsuario' => $vacacionesPorUsuario,
                        'asistenciasIndexadas' => $asistenciasIndexadas,
                        'horasExtrasPorUsuario' => $horasExtrasPorUsuario,
                        'permisosPorUsuario' => $permisosPorUsuario,
                        'faltasJustificadas' => $faltasJustificadas,
                        'retardosPorUsuario' => $retardosPorUsuario,
                        'incapacidadesPorUsuario' => $incapacidadesPorUsuario,
                    ]
                );

                if ($resultado['success']) {
                    $nominaPorUsuario[$user->id] = $resultado;
                } else {
                    $nominaPorUsuario[$user->id] = [
                        'success' => false,
                        'error' => $resultado['error'] ?? 'Error desconocido',
                        'subtotal_percepciones' => 0,
                    ];
                }
            } catch (\Exception $e) {
                $nominaPorUsuario[$user->id] = [
                    'success' => false,
                    'error' => 'Excepción: '.$e->getMessage(),
                    'subtotal_percepciones' => 0,
                ];
            }
        }

        return [
            'usuarios' => $usuarios,
            'fechas' => $fechas,
            'vacacionesPorUsuario' => $vacacionesPorUsuario,
            'asistenciasIndexadas' => $asistenciasIndexadas,
            'horasExtrasPorUsuario' => $horasExtrasPorUsuario,
            'permisosPorUsuario' => $permisosPorUsuario,
            'faltasJustificadas' => $faltasJustificadas,
            'retardosPorUsuario' => $retardosPorUsuario,
            'incapacidadesPorUsuario' => $incapacidadesPorUsuario,
            'nominaPorUsuario' => $nominaPorUsuario,
        ];
    }

    public function asistenciaUsuarioFecha($indexadas, string $fecha, int $userId)
    {
        $registros = $indexadas->get($fecha, collect());
        if (! ($registros instanceof \Illuminate\Support\Collection)) {
            $registros = collect([$registros]);
        }

        return $registros->first(function ($registro) use ($userId) {
            foreach (['elementos_enlistados', 'faltas', 'descansos'] as $campo) {
                if (in_array($userId, json_decode($registro->{$campo} ?? '[]', true) ?? [])) {
                    return true;
                }
            }

            return false;
        });
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
