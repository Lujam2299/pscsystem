<?php

namespace App\Livewire;

use App\Models\InspeccionUnidad;
use App\Models\Unidades;
use App\Services\AuditLogger;
use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class InspeccionesUnidades extends Component
{
    use WithFileUploads;
    use WithPagination;

    public int $perPage = 10;

    public string $filtroUnidad = '';

    public string $filtroResultado = '';

    public string $filtroDesde = '';

    public string $filtroHasta = '';

    public bool $mostrarFormulario = false;

    public array $evidencias = [];

    public array $form = [
        'unidad_id' => '',
        'fecha_inspeccion' => '',
        'tipo' => 'cambio_turno',
        'kilometraje' => '',
        'resultado' => 'sin_novedad',
        'observaciones' => '',
        'reportado_por' => '',
    ];

    public function mount(): void
    {
        Gate::authorize(Permission::INSPECTIONS_VIEW);
        $unidadSolicitada = request()->query('unidad');
        $this->filtroUnidad = is_numeric($unidadSolicitada) ? (string) $unidadSolicitada : '';
        $this->form['fecha_inspeccion'] = now()->format('Y-m-d\TH:i');
    }

    public function mostrarAlta(): void
    {
        Gate::authorize(Permission::INSPECTIONS_MANAGE);
        $this->resetValidation();
        $this->reset('evidencias');
        $this->form = [
            'unidad_id' => '',
            'fecha_inspeccion' => now()->format('Y-m-d\TH:i'),
            'tipo' => 'cambio_turno',
            'kilometraje' => '',
            'resultado' => 'sin_novedad',
            'observaciones' => '',
            'reportado_por' => '',
        ];
        $this->mostrarFormulario = true;
    }

    public function cancelarAlta(): void
    {
        $this->resetValidation();
        $this->reset('evidencias');
        $this->mostrarFormulario = false;
    }

    public function guardar(): mixed
    {
        Gate::authorize(Permission::INSPECTIONS_MANAGE);

        $data = $this->validate([
            'form.unidad_id' => ['required', 'integer', 'exists:unidades,id'],
            'form.fecha_inspeccion' => ['required', 'date'],
            'form.tipo' => ['required', 'in:cambio_turno,entrega,recepcion,revision,mantenimiento'],
            'form.kilometraje' => ['nullable', 'integer', 'min:0', 'max:99999999'],
            'form.resultado' => ['required', 'in:sin_novedad,con_observaciones,requiere_revision,requiere_reparacion'],
            'form.observaciones' => ['nullable', 'string', 'max:5000'],
            'form.reportado_por' => ['nullable', 'string', 'max:150'],
            'evidencias' => ['required', 'array', 'min:1', 'max:20'],
            'evidencias.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $storedPaths = [];

        try {
            $inspeccion = DB::transaction(function () use ($data, &$storedPaths): InspeccionUnidad {
                $inspeccion = InspeccionUnidad::create([
                    ...$data['form'],
                    'kilometraje' => $data['form']['kilometraje'] !== '' ? $data['form']['kilometraje'] : null,
                    'origen' => 'manual',
                    'estado' => 'validada',
                    'created_by' => auth()->id(),
                    'reviewed_by' => auth()->id(),
                ]);

                $directory = sprintf(
                    'monitoreo/inspecciones/%s/inspeccion-%d',
                    $inspeccion->fecha_inspeccion->format('Y/m'),
                    $inspeccion->id,
                );

                foreach ($this->evidencias as $index => $archivo) {
                    $extension = strtolower($archivo->getClientOriginalExtension() ?: 'jpg');
                    $fileName = Str::uuid().'.'.$extension;
                    $path = $archivo->storeAs($directory, $fileName, 'local');

                    if ($path === false) {
                        throw new \RuntimeException('No fue posible guardar una de las evidencias.');
                    }

                    $storedPaths[] = $path;
                    $inspeccion->evidencias()->create([
                        'disk' => 'local',
                        'path' => $path,
                        'nombre_original' => $archivo->getClientOriginalName(),
                        'mime_type' => $archivo->getMimeType() ?: 'application/octet-stream',
                        'size' => $archivo->getSize(),
                        'sha256' => hash_file('sha256', $archivo->getRealPath()),
                        'orden' => $index + 1,
                        'clasificacion' => 'general',
                    ]);
                }

                app(AuditLogger::class)->record(
                    'Inspecciones de unidades',
                    'Inspección creada',
                    $inspeccion,
                    [],
                    $inspeccion->only(['unidad_id', 'fecha_inspeccion', 'tipo', 'kilometraje', 'resultado', 'estado']),
                    ['cantidad_evidencias' => count($storedPaths)],
                );

                return $inspeccion;
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            report($exception);
            $this->addError('evidencias', 'No fue posible guardar la inspección. Inténtalo nuevamente.');

            return null;
        }

        session()->flash('success', 'Inspección registrada correctamente.');

        return redirect()->route('inspecciones.detalle', $inspeccion);
    }

    public function render()
    {
        $unidadesFiltro = Unidades::query()
            ->orderBy('placas')
            ->get(['id', 'placas', 'marca', 'modelo', 'estado_vehiculo'])
            ->filter(fn (Unidades $unidad): bool => $this->tienePlacaUtilizable($unidad))
            ->values();

        $inspecciones = InspeccionUnidad::query()
            ->with(['unidad:id,placas,marca,modelo'])
            ->withCount('evidencias')
            ->when($this->filtroUnidad, fn ($query) => $query->where('unidad_id', $this->filtroUnidad))
            ->when($this->filtroResultado, fn ($query) => $query->where('resultado', $this->filtroResultado))
            ->when($this->filtroDesde, fn ($query) => $query->whereDate('fecha_inspeccion', '>=', $this->filtroDesde))
            ->when($this->filtroHasta, fn ($query) => $query->whereDate('fecha_inspeccion', '<=', $this->filtroHasta))
            ->latest('fecha_inspeccion')
            ->paginate($this->perPage);

        return view('livewire.inspecciones-unidades', [
            'inspecciones' => $inspecciones,
            'unidadesDisponibles' => $unidadesFiltro
                ->filter(fn (Unidades $unidad): bool => $unidad->is_activo)
                ->values(),
            'unidadesFiltro' => $unidadesFiltro,
        ])->layout('layouts.app');
    }

    private function tienePlacaUtilizable(Unidades $unidad): bool
    {
        $placa = trim((string) $unidad->placas);

        return $placa !== ''
            && strlen($placa) <= 25
            && preg_match('/[A-Za-z]/', $placa) === 1
            && preg_match('/\d/', $placa) === 1
            && preg_match('/^[A-Za-z0-9\s\-\/]+$/', $placa) === 1;
    }

    public function updatingFiltroUnidad(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroResultado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroHasta(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }
}
