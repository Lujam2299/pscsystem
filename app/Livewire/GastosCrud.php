<?php

namespace App\Livewire;

use App\Models\Gastos;
use App\Models\Misiones;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class GastosCrud extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public string $filtro_estatus = 'activas';

    public string $filtro_fecha_inicio = '';

    public string $filtro_fecha_fin = '';

    public string $filtro_busqueda = '';

    public string $filtro_agente = '';

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstatus(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaInicio(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaFin(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroAgente(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'filtro_fecha_inicio',
            'filtro_fecha_fin',
            'filtro_busqueda',
            'filtro_agente',
        ]);

        $this->filtro_estatus = 'activas';
        $this->resetPage();
    }

    public function render()
    {
        $query = Misiones::query();

        $this->aplicarFiltroEstatus($query);
        $this->aplicarFiltros($query);

        $perPage = in_array($this->perPage, [10, 25, 50, 100], true)
            ? $this->perPage
            : 10;

        $misiones = $query
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->paginate($perPage);

        $resumenMisiones = $this->construirResumenMisiones($misiones->getCollection());

        return view('livewire.gastos-crud', [
            'breadcrumbItems' => [
                ['icon' => 'ti-home', 'url' => route('dashboard')],
                ['icon' => 'ti-receipt-2', 'label' => 'Gastos por misión'],
            ],
            'titleMain' => 'Gastos por misión',
            'helpText' => 'Consulta los agentes asignados y los gastos registrados durante el periodo de cada misión.',
            'misiones' => $misiones,
            'resumenMisiones' => $resumenMisiones,
        ])->layout('layouts.app');
    }

    private function aplicarFiltroEstatus(Builder $query): void
    {
        $hoy = Carbon::today()->toDateString();

        if ($this->filtro_estatus === 'terminadas') {
            $query->whereNotNull('fecha_fin')
                ->whereDate('fecha_fin', '<', $hoy);

            return;
        }

        $query->whereDate('fecha_inicio', '<=', $hoy)
            ->whereNotNull('fecha_fin')
            ->whereDate('fecha_fin', '>=', $hoy);
    }

    private function aplicarFiltros(Builder $query): void
    {
        if (
            $this->filtro_fecha_inicio !== ''
            && $this->filtro_fecha_fin !== ''
            && $this->filtro_fecha_inicio > $this->filtro_fecha_fin
        ) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($this->filtro_fecha_inicio !== '') {
            $query->whereDate('fecha_fin', '>=', $this->filtro_fecha_inicio);
        }

        if ($this->filtro_fecha_fin !== '') {
            $query->whereDate('fecha_inicio', '<=', $this->filtro_fecha_fin);
        }

        $busqueda = trim($this->filtro_busqueda);

        if ($busqueda !== '') {
            $query->where(function (Builder $subquery) use ($busqueda): void {
                $subquery->where('cliente', 'like', "%{$busqueda}%")
                    ->orWhere('nombre_clave', 'like', "%{$busqueda}%");

                if (ctype_digit($busqueda)) {
                    $subquery->orWhere('id', (int) $busqueda);
                }
            });
        }

        $nombreAgente = trim($this->filtro_agente);

        if ($nombreAgente === '') {
            return;
        }

        $agentesIds = User::query()
            ->where('name', 'like', "%{$nombreAgente}%")
            ->pluck('id');

        if ($agentesIds->isEmpty()) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $subquery) use ($agentesIds): void {
            foreach ($agentesIds as $agenteId) {
                // JSON_UNQUOTE permite consultar tanto arreglos JSON normales como
                // registros históricos cuyo arreglo quedó codificado dos veces.
                $subquery->orWhereRaw(
                    "JSON_SEARCH(JSON_UNQUOTE(agentes_id), 'one', ?) IS NOT NULL",
                    [(string) $agenteId]
                );
            }
        });
    }

    /**
     * @return array<int, array{
     *     agentes: array<int, array{id: int, nombre: string, gastos: Collection}>,
     *     cantidad_gastos: int,
     *     total: float,
     *     estatus: string
     * }>
     */
    private function construirResumenMisiones(Collection $misiones): array
    {
        if ($misiones->isEmpty()) {
            return [];
        }

        $agentesPorMision = $misiones->mapWithKeys(
            fn (Misiones $mision) => [$mision->id => $mision->agentesIdsNormalizados()]
        );

        $todosLosAgentesIds = $agentesPorMision
            ->flatten()
            ->unique()
            ->values();

        $usuarios = User::query()
            ->whereIn('id', $todosLosAgentesIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $fechaMinima = $misiones->pluck('fecha_inicio')->filter()->min();
        $fechaMaxima = $misiones->pluck('fecha_fin')->filter()->max();

        $gastosCandidatos = collect();

        if ($todosLosAgentesIds->isNotEmpty() && $fechaMinima && $fechaMaxima) {
            $gastosCandidatos = Gastos::query()
                ->whereIn('user_id', $todosLosAgentesIds)
                ->whereBetween('Fecha', [$fechaMinima, $fechaMaxima])
                ->orderBy('Fecha')
                ->orderBy('Hora')
                ->get();
        }

        $hoy = Carbon::today();
        $resumen = [];

        foreach ($misiones as $mision) {
            $agentesIds = $agentesPorMision->get($mision->id, []);
            $inicioMision = Carbon::parse($mision->fecha_inicio);
            $finMision = Carbon::parse($mision->fecha_fin);
            $inicio = $inicioMision->copy();
            $fin = $finMision->copy();

            if ($this->filtro_fecha_inicio !== '') {
                $inicioFiltro = Carbon::parse($this->filtro_fecha_inicio);
                $inicio = $inicioFiltro->greaterThan($inicio) ? $inicioFiltro : $inicio;
            }

            if ($this->filtro_fecha_fin !== '') {
                $finFiltro = Carbon::parse($this->filtro_fecha_fin);
                $fin = $finFiltro->lessThan($fin) ? $finFiltro : $fin;
            }

            $gastosMision = $inicio->greaterThan($fin)
                ? collect()
                : $gastosCandidatos->filter(function (Gastos $gasto) use ($agentesIds, $inicio, $fin): bool {
                    if (! in_array((int) $gasto->user_id, $agentesIds, true) || ! $gasto->Fecha) {
                        return false;
                    }

                    return $gasto->Fecha->betweenIncluded($inicio, $fin);
                })->values();

            $agentes = [];

            foreach ($agentesIds as $agenteId) {
                $agentes[] = [
                    'id' => $agenteId,
                    'nombre' => $usuarios->get($agenteId)?->name ?? "Usuario #{$agenteId}",
                    'gastos' => $gastosMision
                        ->where('user_id', $agenteId)
                        ->values(),
                ];
            }

            $resumen[$mision->id] = [
                'agentes' => $agentes,
                'cantidad_gastos' => $gastosMision->count(),
                'total' => (float) $gastosMision->sum('Monto'),
                'estatus' => $finMision->isBefore($hoy) ? 'Terminada' : 'Activa',
            ];
        }

        return $resumen;
    }
}
