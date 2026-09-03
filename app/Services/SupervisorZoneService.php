<?php

namespace App\Services;

use App\Models\SolicitudAlta;
use App\Models\Subpunto;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SupervisorZoneService
{
    public function available(): Collection
    {
        return Subpunto::query()->whereNotNull('zona')->select('zona')->distinct()->pluck('zona')
            ->map(fn ($zone) => trim($zone))->filter(fn ($zone) => $zone !== '')
            ->unique()->sort()->values();
    }

    public function availableOptions(): Collection
    {
        $subpoints = Subpunto::query()
            ->whereNotNull('zona')
            ->whereNotNull('nombre')
            ->get(['zona', 'nombre'])
            ->groupBy(fn (Subpunto $subpoint) => trim((string) $subpoint->zona));

        return $this->available()->mapWithKeys(function (string $zone) use ($subpoints): array {
            $names = $subpoints->get($zone, collect())
                ->map(fn (Subpunto $subpoint) => trim((string) $subpoint->nombre))
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->implode(', ');

            return [$zone => $names === '' ? $zone : "$zone — $names"];
        });
    }

    public function applySelection(SolicitudAlta $solicitud, ?string $role, ?string $zone): ?string
    {
        $solicitud->rol = strtoupper(trim((string) $role)) === 'SUPERVISOR' ? 'SUPERVISOR' : $role;
        $zone = trim((string) $zone);
        // Empty/omitted selections never clear existing zones or assignments.
        if ($solicitud->rol !== 'SUPERVISOR' || $zone === '') {
            return null;
        }
        $this->subpointIds($zone);
        $solicitud->zona_supervisor = $zone;

        return $zone;
    }

    public function sync(User $user, ?string $zone): void
    {
        if (strtoupper(trim((string) $user->rol)) !== 'SUPERVISOR' || trim((string) $zone) === '') {
            return;
        }
        $user->subpuntosSupervisados()->sync($this->subpointIds(trim($zone)));
    }

    private function subpointIds(string $zone): array
    {
        $ids = Subpunto::query()->whereRaw('TRIM(zona) = ?', [$zone])->pluck('id')->all();
        if ($ids === []) {
            throw ValidationException::withMessages(['zona_supervisor' => 'Selecciona una zona existente.']);
        }

        return $ids;
    }
}
