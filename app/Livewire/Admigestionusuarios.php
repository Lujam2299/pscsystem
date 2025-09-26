<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Admigestionusuarios extends Component
{
    use WithPagination;

    public $search = '';
    public $tipo_pago = 'todos'; // Nuevo filtro
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'tipo_pago' => ['except' => 'todos'], // Aseguramos que se mantenga en la URL
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

    public function render()
    {
        $baseQuery = User::with('solicitudAlta') // Aseguramos la carga de la relación
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->when($this->tipo_pago !== 'todos', function ($query) {
                $query->whereHas('solicitudAlta', function ($subQuery) {
                    $subQuery->where('tipo_periodo', $this->tipo_pago);
                });
            });

        if (!(Auth::user()->rol == 'admin' ||
            in_array(Auth::user()->solicitudAlta->rol ?? '', [
                'AUXILIAR RECURSOS HUMANOS', 'AUXILIAR RH', 'AUX RH',
                'Auxiliar RH', 'Auxiliar Recursos Humanos', 'Aux RH', 'AUXILIAR NOMINAS', 'Auxiliar Nominas',
                'AUX NOMINAS', 'Aux Nominas', 'Auxiliar nóminas'
            ]) ||
            Auth::user()->solicitudAlta->departamento == 'Recursos Humanos')) {
            $user = Auth::user();
            $puntoUsuarioRaw = $user->punto;
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

            $baseQuery->where('empresa', $user->empresa)
                ->where('rol', '!=', 'Supervisor')
                ->where(function ($query) use ($user, $subpuntosZona) {
                    $query->where('punto', $user->punto);

                    if ($subpuntosZona->isNotEmpty()) {
                        $query->orWhereIn('punto', $subpuntosZona);
                    }
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

        if ($this->sortField === 'progreso_documentos') {
            $users = $this->sortDirection === 'asc'
                ? $users->sortBy('progreso_documentos')
                : $users->sortByDesc('progreso_documentos');
        } else {
            $users = $this->sortDirection === 'asc'
                ? $users->sortBy($this->sortField)
                : $users->sortByDesc($this->sortField);
        }

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
        ]);
    }
}
