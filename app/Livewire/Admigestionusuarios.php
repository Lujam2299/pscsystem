<?php

namespace App\Livewire;

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\User;
use App\Models\Punto;
use App\Models\Subpunto;
use Illuminate\Support\Facades\Auth;

class Admigestionusuarios extends Component
{
    public function boot(): void
    {
        Gate::authorize(Permission::USERS_VIEW);
    }

    use WithPagination;

    public $search = '';
    public $tipo_pago = 'todos';
    public $estatus = 'Activo'; // ✅ Valor por defecto 'Activo'
    public $punto = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'tipo_pago' => ['except' => 'todos'],
        'estatus' => ['except' => 'Activo'],
        'punto' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipoPago()
    {
        $this->resetPage();
    }

    public function updatingEstatus()
    {
        $this->resetPage();
    }

    public function updatingPunto()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    protected function getSubpuntosPorPunto()
    {
        $monterreyId = Punto::where('nombre', 'MONTERREY')->value('id');
        $codigos = $monterreyId
            ? Subpunto::where('punto_id', $monterreyId)->pluck('codigo', 'nombre')->toArray()
            : [];

        $codigoMaryKay = $codigos['MARY KAY CORPORATIVO'] ?? $codigos['MARYKAY CORPORATIVO'] ?? $codigos['MAR KAY CORPORATIVO'] ?? null;

        return [
            'MONTERREY' => [
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
            ],
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

    public function render()
    {
        $subpuntosMap = $this->getSubpuntosPorPunto();

        $rol = Auth::user()?->rol;
        if ($rol === 'AUXILIAR OPERACIONES') {
            $subpuntosMap = [
                'MONTERREY' => $subpuntosMap['MONTERREY'] ?? []
            ];
        }

        $baseQuery = User::with('solicitudAlta', 'documentacionAltas')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->tipo_pago !== 'todos', function ($query) {
                $query->whereHas('solicitudAlta', function ($subQuery) {
                    $subQuery->where('tipo_periodo', $this->tipo_pago);
                });
            })
            ->when($this->estatus !== 'todos', function ($query) {
                $query->where('estatus', $this->estatus);
            });

        // Aplicar filtro por punto si se seleccionó uno
        if ($this->punto !== '') {
            $filtro = strtoupper($this->punto);

            if (in_array($filtro, ['MARYKAY CORPORATIVO', 'MAR KAY CORPORATIVO'])) {
                $filtro = 'MARY KAY CORPORATIVO';
            }

            $puntoGeneral = null;
            $subpuntos = [];

            foreach ($subpuntosMap as $p => $subs) {
                if (strtoupper($p) === $filtro) {
                    $puntoGeneral = $p;
                    $subpuntos = $subs;
                    break;
                }
                foreach ($subs as $sub) {
                    if (strtoupper($sub['nombre']) === $filtro || (string) $sub['codigo'] === $filtro) {
                        $puntoGeneral = $p;
                        $subpuntos = [$sub];
                        break 2;
                    }
                }
            }

            if (!$puntoGeneral) {
                $baseQuery->where(function ($query) use ($filtro) {
                    $query->where('punto', $filtro)
                          ->orWhere('punto', strtoupper($filtro))
                          ->orWhere('punto', strtolower($filtro));
                });
            } else {
                $baseQuery->where(function ($query) use ($subpuntos, $puntoGeneral) {
                    foreach ($subpuntos as $sub) {
                        $nombre = $sub['nombre'] ?? null;
                        $codigo = $sub['codigo'] ?? null;

                        $query->orWhere(function ($q) use ($nombre, $codigo, $puntoGeneral) {
                            if ($nombre) {
                                $q->whereRaw('LOWER(punto) = ?', [strtolower($nombre)]);
                            }
                            if ($nombre === 'MARY KAY CORPORATIVO') {
                                $q->orWhereRaw('LOWER(punto) = ?', ['marykay corporativo'])
                                  ->orWhereRaw('LOWER(punto) = ?', ['mar kay corporativo']);
                            }
                            if ($codigo !== null && $puntoGeneral === 'MONTERREY') {
                                $q->orWhere('punto', $codigo);
                            }
                        });
                    }
                });
            }
        }

        $authUser = Auth::user();
        $esAdminOrRH = $authUser->rol == 'admin' ||
            in_array($authUser->solicitudAlta?->rol ?? '', [
                'AUXILIAR RECURSOS HUMANOS', 'AUXILIAR RH', 'AUX RH',
                'Auxiliar RH', 'Auxiliar Recursos Humanos', 'Aux RH',
                'AUXILIAR NOMINAS', 'Auxiliar Nominas',
                'AUX NOMINAS', 'Aux Nominas', 'Auxiliar nóminas'
            ], true) ||
            ($authUser->solicitudAlta?->departamento == 'Recursos Humanos');

        if (!$esAdminOrRH) {
            $puntoUsuarioRaw = $authUser->punto;
            $subpuntosZona = collect();

            $punto = \App\Models\Punto::where('nombre', $puntoUsuarioRaw)->first();

            if (!$punto) {
                $subpunto = \App\Models\Subpunto::where('nombre', $puntoUsuarioRaw)->first()
                    ?? \App\Models\Subpunto::where('codigo', $puntoUsuarioRaw)->first();

                if ($subpunto && $subpunto->zona) {
                    $subpuntosZona = \App\Models\Subpunto::where('zona', $subpunto->zona)
                        ->pluck('nombre')
                        ->merge(
                            \App\Models\Subpunto::where('zona', $subpunto->zona)->pluck('codigo')
                        );
                }
            }

            $baseQuery->where('empresa', $authUser->empresa)
                ->where('rol', '!=', 'Supervisor')
                ->where(function ($query) use ($authUser, $subpuntosZona) {
                    $query->where('punto', $authUser->punto);

                    if ($subpuntosZona->isNotEmpty()) {
                        $query->orWhereIn('punto', $subpuntosZona);
                    }
                });
        }

        $rolesOperaciones = ['OPERACIONES', 'AUXILIAR OPERACIONES'];
        if (in_array($authUser->rol, $rolesOperaciones, true)) {
            $baseQuery->where(function ($query) {
                $query->whereRaw('LOWER(rol) LIKE ?', ['%guardia%'])
                      ->orWhereRaw('LOWER(rol) LIKE ?', ['%patrullero%']);
            });
        }

        $users = $baseQuery->get();

        $users = $users->map(function ($user) {
            $tipoEmpleado = $user->solicitudAlta?->tipo_empleado;
            $documentacion = $user->solicitudAlta?->documentacion;

            $documentosBase = [
                'arch_ine', 'arch_solicitud_empleo', 'arch_curp', 'arch_rfc', 'arch_nss',
                'arch_acta_nacimiento', 'arch_comprobante_estudios', 'arch_comprobante_domicilio',
                'arch_carta_rec_laboral', 'arch_carta_rec_personal',
            ];
            $documentosExtraArmado = ['arch_cartilla_militar', 'arch_carta_no_penales', 'arch_antidoping'];

            $documentos = $tipoEmpleado === 'armado'
                ? array_merge($documentosBase, $documentosExtraArmado)
                : $documentosBase;

            $completados = 0;
            foreach ($documentos as $campo) {
                if (!empty($documentacion?->$campo)) {
                    $completados++;
                }
            }

            $total = count($documentos);
            $user->progreso_documentos = $total > 0 ? round(($completados / $total) * 100) : 0;

            return $user;
        });

        // ✅ ORDENAMIENTO POR APELLIDOS + NOMBRE EN MAYÚSCULAS
        if ($this->sortField === 'name') {
            $users = $this->sortDirection === 'asc'
                ? $users->sortBy(function ($user) {
                    $solicitud = $user->solicitudAlta;
                    // Concatenar apellidos y nombre en mayúsculas para ordenamiento consistente
                    return strtoupper(sprintf(
                        '%s %s %s',
                        $solicitud?->apellido_paterno ?? '',
                        $solicitud?->apellido_materno ?? '',
                        $solicitud?->nombre ?? ''
                    ));
                })
                : $users->sortByDesc(function ($user) {
                    $solicitud = $user->solicitudAlta;
                    return strtoupper(sprintf(
                        '%s %s %s',
                        $solicitud?->apellido_paterno ?? '',
                        $solicitud?->apellido_materno ?? '',
                        $solicitud?->nombre ?? ''
                    ));
                });
        } elseif ($this->sortField === 'progreso_documentos') {
            $users = $this->sortDirection === 'asc'
                ? $users->sortBy('progreso_documentos')
                : $users->sortByDesc('progreso_documentos');
        } else {
            $users = $this->sortDirection === 'asc'
                ? $users->sortBy($this->sortField)
                : $users->sortByDesc($this->sortField);
        }
        // ✅ FIN ORDENAMIENTO

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $users->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedUsers = new LengthAwarePaginator(
            $currentItems,
            $users->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.admigestionusuarios', [
            'users' => $paginatedUsers,
            'subpuntosMap' => $subpuntosMap,
        ]);
    }
}
