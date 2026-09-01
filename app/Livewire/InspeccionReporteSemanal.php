<?php

namespace App\Livewire;

use App\Services\InspeccionReporteSemanalService;
use App\Support\Authorization\Permission;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

class InspeccionReporteSemanal extends Component
{
    #[Url(as: 'semana')]
    public string $semana = '';

    public function mount(): void
    {
        Gate::authorize(Permission::INSPECTION_INBOX_VIEW);
        $this->semana = $this->inicioSemana($this->semana)->toDateString();
    }

    public function anterior(): void
    {
        $this->semana = $this->inicioSemana($this->semana)->subWeek()->toDateString();
    }

    public function siguiente(): void
    {
        $this->semana = $this->inicioSemana($this->semana)->addWeek()->toDateString();
    }

    public function actual(): void
    {
        $this->semana = now()->startOfWeek()->toDateString();
    }

    public function updatedSemana(): void
    {
        $this->semana = $this->inicioSemana($this->semana)->toDateString();
    }

    public function render(InspeccionReporteSemanalService $reportes)
    {
        $reporte = $reportes->generar($this->semana);

        return view('livewire.inspeccion-reporte-semanal', compact('reporte'))
            ->layout('layouts.app');
    }

    private function inicioSemana(?string $fecha): CarbonImmutable
    {
        return CarbonImmutable::parse($fecha ?: 'now')->startOfWeek(CarbonImmutable::MONDAY);
    }
}
