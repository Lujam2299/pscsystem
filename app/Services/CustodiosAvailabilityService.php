<?php

namespace App\Services;

use App\Models\Incapacidad;
use App\Models\Misiones;
use App\Models\PermisoEspecial;
use App\Models\SolicitudVacaciones;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CustodiosAvailabilityService
{
    /**
     * Devuelve el directorio de escoltas activos con su disponibilidad.
     *
     * @return Collection<int, array{id:int,name:string,ocupado:bool,motivo:?string,photo_path:?string}>
     */
    public function forPeriod(Carbon $start, Carbon $end, ?int $excludeMissionId = null): Collection
    {
        $agents = User::query()
            ->with('documentacionAltas:id,arch_foto')
            ->where('estatus', 'Activo')
            ->whereRaw('LOWER(rol) LIKE ?', ['%escolta%'])
            ->orderBy('name')
            ->get();

        $reasons = $this->blockingReasons(
            $agents->pluck('id')->map(fn ($id) => (int) $id)->all(),
            $start,
            $end,
            $excludeMissionId,
        );

        return $agents->map(function (User $agent) use ($reasons): array {
            $agentReasons = $reasons->get((int) $agent->id, collect())->unique()->values();

            return [
                'id' => (int) $agent->id,
                'name' => $agent->name,
                'ocupado' => $agentReasons->isNotEmpty(),
                'motivo' => $agentReasons->isEmpty() ? null : $agentReasons->implode(', '),
                'photo_path' => $agent->documentacionAltas?->arch_foto,
            ];
        })->values();
    }

    /**
     * Impide que una petición alterada omita las restricciones del selector.
     *
     * @param array<int, int|string> $agentIds
     */
    public function assertAvailable(
        array $agentIds,
        Carbon $start,
        Carbon $end,
        ?int $excludeMissionId = null,
    ): void {
        $normalizedIds = collect($agentIds)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $reasons = $this->blockingReasons($normalizedIds, $start, $end, $excludeMissionId);

        if ($reasons->isEmpty()) {
            return;
        }

        $names = User::query()
            ->whereIn('id', $reasons->keys())
            ->pluck('name', 'id');

        $details = $reasons->map(function (Collection $agentReasons, int $agentId) use ($names): string {
            $name = $names->get($agentId, "Usuario #{$agentId}");

            return $name.' ('.$agentReasons->unique()->implode(', ').')';
        })->values()->implode('; ');

        throw ValidationException::withMessages([
            'agentes_id' => "No se puede asignar a: {$details}.",
        ]);
    }

    /**
     * @param array<int, int> $agentIds
     * @return Collection<int, Collection<int, string>>
     */
    private function blockingReasons(
        array $agentIds,
        Carbon $start,
        Carbon $end,
        ?int $excludeMissionId,
    ): Collection {
        $reasons = collect();

        if ($agentIds === []) {
            return $reasons;
        }

        $addReason = function (int $agentId, string $reason) use ($reasons): void {
            if (! $reasons->has($agentId)) {
                $reasons->put($agentId, collect());
            }

            $reasons->get($agentId)->push($reason);
        };

        $missions = Misiones::query()
            ->when($excludeMissionId, fn ($query) => $query->whereKeyNot($excludeMissionId))
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->where(function ($query) use ($start): void {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $start->toDateString());
            })
            ->where(function ($query): void {
                $query->whereNull('estatus')
                    ->orWhereRaw("UPPER(TRIM(estatus)) <> 'CANCELADA'");
            })
            ->get(['id', 'agentes_id']);

        foreach ($missions as $mission) {
            foreach (array_intersect($agentIds, $mission->agentesIdsNormalizados()) as $agentId) {
                $addReason((int) $agentId, "misión #{$mission->id}");
            }
        }

        $vacations = SolicitudVacaciones::query()
            ->whereIn('user_id', $agentIds)
            ->whereRaw("UPPER(TRIM(estatus)) = 'ACEPTADA'")
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString())
            ->get(['user_id']);

        foreach ($vacations as $vacation) {
            $addReason((int) $vacation->user_id, 'vacaciones');
        }

        $permissions = PermisoEspecial::query()
            ->whereIn('user_id', $agentIds)
            ->whereRaw("UPPER(TRIM(estatus)) = 'APROBADO'")
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString())
            ->get(['user_id']);

        foreach ($permissions as $permission) {
            $addReason((int) $permission->user_id, 'permiso');
        }

        $disabilities = Incapacidad::query()
            ->whereIn('user_id', $agentIds)
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->get(['user_id', 'fecha_inicio', 'dias_incapacidad']);

        foreach ($disabilities as $disability) {
            $disabilityStart = Carbon::parse($disability->fecha_inicio)->startOfDay();
            $days = max(1, (int) $disability->dias_incapacidad);
            $disabilityEnd = $disabilityStart->copy()->addDays($days - 1)->endOfDay();

            if ($disabilityStart->lte($end) && $disabilityEnd->gte($start)) {
                $addReason((int) $disability->user_id, 'incapacidad');
            }
        }

        return $reasons;
    }
}
