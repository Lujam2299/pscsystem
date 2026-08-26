<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $module = '';

    public string $action = '';

    public string $subjectType = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function boot(): void
    {
        Gate::authorize(Permission::AUDIT_VIEW);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'module', 'action', 'subjectType', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'module', 'action', 'subjectType', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::query()
            ->with('actor:id,name,email')
            ->when($this->module, fn ($q) => $q->where('module', $this->module))
            ->when($this->action, fn ($q) => $q->where('action', 'like', "%{$this->action}%"))
            ->when($this->subjectType, fn ($q) => $q->where('subject_type', $this->subjectType))
            ->when($this->search, function ($q): void {
                $q->where(function ($nested): void {
                    $nested->whereHas('actor', fn ($actor) => $actor
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%"))
                        ->orWhere('subject_id', $this->search);
                });
            })
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest('id')
            ->paginate(20);

        return view('livewire.audit-log-index', [
            'logs' => $logs,
            'modules' => AuditLog::query()->distinct()->orderBy('module')->pluck('module'),
            'subjectTypes' => AuditLog::query()
                ->whereNotNull('subject_type')
                ->distinct()
                ->orderBy('subject_type')
                ->pluck('subject_type'),
        ])->layout('layouts.app');
    }
}
