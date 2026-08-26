<?php

namespace App\Http\Controllers;

use App\Models\GpsAlert;
use App\Models\GpsSpeedLimit;
use App\Services\TraccarClient;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class TraccarMonitoringController extends Controller
{
    public function index(): View
    {
        return view('monitoreo.unidades-gps', [
            'websocketUrl' => config('services.traccar.websocket_url'),
        ]);
    }

    public function data(TraccarClient $traccar): JsonResponse
    {
        try {
            return response()->json([
                'devices' => $traccar->devices(),
                'positions' => $traccar->positions(),
                'fetched_at' => now()->utc()->toIso8601String(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('No fue posible obtener datos de Traccar.', [
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'No fue posible consultar las unidades GPS en este momento.',
            ], 502);
        }
    }

    public function socketToken(TraccarClient $traccar): JsonResponse
    {
        try {
            return response()->json([
                'token' => $traccar->generateSocketToken(),
                'expires_at' => $traccar->socketTokenExpiresAt(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('No fue posible generar el token temporal de Traccar.', [
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'No fue posible iniciar el seguimiento en tiempo real.',
            ], 502);
        }
    }

    public function history(Request $request, TraccarClient $traccar): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'integer', 'min:1'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
        ]);

        $from = CarbonImmutable::parse($validated['from'])->utc();
        $to = CarbonImmutable::parse($validated['to'])->utc();

        if ($from->diffInSeconds($to) > 7 * 24 * 60 * 60) {
            throw ValidationException::withMessages([
                'to' => 'El periodo máximo permitido es de 7 días.',
            ]);
        }

        $deviceId = (int) $validated['device_id'];
        $fromIso = $from->toIso8601String();
        $toIso = $to->toIso8601String();

        try {
            $positions = $traccar->routeReport($deviceId, $fromIso, $toIso);
            $events = $this->optionalReport(
                fn () => $traccar->eventReport($deviceId, $fromIso, $toIso),
                'eventos',
            );
            $stops = $this->optionalReport(
                fn () => $traccar->stopReport($deviceId, $fromIso, $toIso),
                'paradas',
            );

            return response()->json([
                'positions' => $positions,
                'events' => $events,
                'stops' => $stops,
                'from' => $fromIso,
                'to' => $toIso,
            ]);
        } catch (Throwable $exception) {
            Log::warning('No fue posible obtener el historial de Traccar.', [
                'device_id' => $deviceId,
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'No fue posible consultar el recorrido de la unidad.',
            ], 502);
        }
    }

    public function address(Request $request, TraccarClient $traccar): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $latitude = round((float) $validated['latitude'], 5);
        $longitude = round((float) $validated['longitude'], 5);
        $cacheKey = "traccar:address:{$latitude}:{$longitude}";

        try {
            $address = Cache::remember(
                $cacheKey,
                now()->addDay(),
                fn () => $traccar->reverseGeocode($latitude, $longitude),
            );

            return response()->json(['address' => $address]);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'La dirección no está disponible para esta posición.',
            ], 404);
        }
    }

    public function geofences(TraccarClient $traccar): JsonResponse
    {
        try {
            return response()->json([
                'geofences' => $traccar->geofences(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('No fue posible obtener las geocercas de Traccar.', [
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'No fue posible consultar las geocercas.',
            ], 502);
        }
    }

    public function alerts(Request $request, TraccarClient $traccar): JsonResponse
    {
        $validated = $request->validate([
            'priority' => ['nullable', 'in:critical,high,medium,info'],
            'type' => ['nullable', 'string', 'max:80'],
            'device_id' => ['nullable', 'integer', 'min:1'],
            'read' => ['nullable', 'in:read,unread'],
        ]);

        try {
            $devices = $traccar->devices();
            $deviceIds = collect($devices)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
            $from = now()->subDay()->utc()->toIso8601String();
            $to = now()->utc()->toIso8601String();

            foreach ($traccar->recentEvents($deviceIds, $from, $to) as $event) {
                if (empty($event['id']) || empty($event['deviceId']) || empty($event['type'])) {
                    continue;
                }

                GpsAlert::updateOrCreate(
                    ['traccar_event_id' => (int) $event['id']],
                    [
                        'device_id' => (int) $event['deviceId'],
                        'position_id' => isset($event['positionId']) ? (int) $event['positionId'] : null,
                        'geofence_id' => isset($event['geofenceId']) ? (int) $event['geofenceId'] : null,
                        'type' => (string) $event['type'],
                        'priority' => $this->alertPriority((string) $event['type'], $event['attributes'] ?? []),
                        'event_time' => $event['eventTime'] ?? now(),
                        'attributes' => $event['attributes'] ?? [],
                    ],
                );
            }
        } catch (Throwable $exception) {
            Log::info('No fue posible sincronizar eventos recientes de Traccar; se usarán los eventos locales.', [
                'exception' => $exception::class,
            ]);
        }

        $query = GpsAlert::query()
            ->where('event_time', '>=', now()->subDay())
            ->addSelect([
                'is_read' => DB::table('gps_alert_reads')
                    ->selectRaw('1')
                    ->whereColumn('gps_alert_reads.gps_alert_id', 'gps_alerts.id')
                    ->where('gps_alert_reads.user_id', $request->user()->id)
                    ->limit(1),
            ])
            ->when($validated['priority'] ?? null, fn ($builder, $priority) => $builder->where('priority', $priority))
            ->when($validated['type'] ?? null, fn ($builder, $type) => $builder->where('type', $type))
            ->when($validated['device_id'] ?? null, fn ($builder, $deviceId) => $builder->where('device_id', $deviceId));

        if (($validated['read'] ?? null) === 'read') {
            $query->whereExists(fn ($readQuery) => $readQuery
                ->selectRaw('1')
                ->from('gps_alert_reads')
                ->whereColumn('gps_alert_reads.gps_alert_id', 'gps_alerts.id')
                ->where('gps_alert_reads.user_id', $request->user()->id));
        } elseif (($validated['read'] ?? null) === 'unread') {
            $query->whereNotExists(fn ($readQuery) => $readQuery
                ->selectRaw('1')
                ->from('gps_alert_reads')
                ->whereColumn('gps_alert_reads.gps_alert_id', 'gps_alerts.id')
                ->where('gps_alert_reads.user_id', $request->user()->id));
        }

        $alerts = $query->latest('event_time')->limit(250)->get();

        return response()->json([
            'alerts' => $alerts,
            'unread_count' => GpsAlert::query()
                ->where('event_time', '>=', now()->subDay())
                ->whereNotExists(fn ($readQuery) => $readQuery
                    ->selectRaw('1')
                    ->from('gps_alert_reads')
                    ->whereColumn('gps_alert_reads.gps_alert_id', 'gps_alerts.id')
                    ->where('gps_alert_reads.user_id', $request->user()->id))
                ->count(),
        ]);
    }

    public function readAlerts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['nullable', 'array', 'max:250'],
            'ids.*' => ['integer', 'exists:gps_alerts,id'],
            'all' => ['nullable', 'boolean'],
        ]);

        $alertIds = !empty($validated['all'])
            ? GpsAlert::where('event_time', '>=', now()->subDay())->pluck('id')->all()
            : ($validated['ids'] ?? []);

        $now = now();
        $rows = collect($alertIds)->unique()->map(fn ($alertId) => [
            'gps_alert_id' => (int) $alertId,
            'user_id' => $request->user()->id,
            'read_at' => $now,
        ])->all();

        if ($rows !== []) {
            DB::table('gps_alert_reads')->insertOrIgnore($rows);
        }

        return response()->json(['ok' => true]);
    }

    public function speedLimits(): JsonResponse
    {
        return response()->json(['limits' => GpsSpeedLimit::orderBy('device_id')->get()]);
    }

    public function saveSpeedLimit(Request $request, int $deviceId, TraccarClient $traccar): JsonResponse
    {
        $validated = $request->validate([
            'speed_limit_kmh' => ['required', 'numeric', 'between:10,200'],
            'tolerance_seconds' => ['required', 'integer', 'between:0,600'],
            'active' => ['required', 'boolean'],
        ]);
        $exists = collect($traccar->devices())->contains(fn ($device) => (int) ($device['id'] ?? 0) === $deviceId);
        abort_unless($exists, 404, 'La unidad GPS no existe o no está disponible.');

        $limit = GpsSpeedLimit::updateOrCreate(['device_id' => $deviceId], [
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        Log::notice('Límite de velocidad GPS actualizado.', ['device_id' => $deviceId, 'user_id' => $request->user()->id]);

        return response()->json(['limit' => $limit]);
    }

    public function createGeofence(Request $request, TraccarClient $traccar): JsonResponse
    {
        $validated = $this->validateGeofence($request);
        $geofence = $traccar->createGeofence([
            'id' => 0,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'area' => $validated['area'],
            'calendarId' => 0,
            'attributes' => $validated['attributes'] ?? [],
        ]);
        Log::notice('Geocerca creada en Traccar.', ['geofence_id' => $geofence['id'] ?? null, 'user_id' => $request->user()->id]);

        return response()->json(['geofence' => $geofence], 201);
    }

    public function updateGeofence(Request $request, int $geofenceId, TraccarClient $traccar): JsonResponse
    {
        $validated = $this->validateGeofence($request);
        $current = collect($traccar->geofences())->firstWhere('id', $geofenceId);
        abort_unless($current, 404, 'La geocerca no existe o no está disponible.');
        $geofence = $traccar->updateGeofence($geofenceId, [
            ...$current,
            'id' => $geofenceId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'area' => $validated['area'],
            'attributes' => $validated['attributes'] ?? ($current['attributes'] ?? []),
        ]);
        Log::notice('Geocerca actualizada en Traccar.', ['geofence_id' => $geofenceId, 'user_id' => $request->user()->id]);

        return response()->json(['geofence' => $geofence]);
    }

    public function deleteGeofence(Request $request, int $geofenceId, TraccarClient $traccar): JsonResponse
    {
        $traccar->deleteGeofence($geofenceId);
        Log::warning('Geocerca eliminada de Traccar.', ['geofence_id' => $geofenceId, 'user_id' => $request->user()->id]);

        return response()->json(['ok' => true]);
    }

    /** @return array<string, mixed> */
    private function validateGeofence(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:128'],
            'description' => ['nullable', 'string', 'max:500'],
            'area' => ['required', 'string', 'max:50000', 'regex:/^(CIRCLE|POLYGON|LINESTRING)\s*\(/i'],
            'attributes' => ['nullable', 'array'],
            'attributes.color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
    }

    private function alertPriority(string $type, array $attributes): string
    {
        $alarm = strtolower((string) ($attributes['alarm'] ?? ''));
        if ($type === 'alarm' && in_array($alarm, ['sos', 'panic', 'tampering', 'removing'], true)) {
            return 'critical';
        }

        return match ($type) {
            'deviceOffline', 'overspeed' => 'high',
            'alarm', 'geofenceEnter', 'geofenceExit', 'deviceUnknown' => 'medium',
            default => 'info',
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function optionalReport(callable $callback, string $report): array
    {
        try {
            $result = $callback();

            return is_array($result) ? $result : [];
        } catch (Throwable $exception) {
            Log::info("El reporte de {$report} de Traccar no está disponible.", [
                'exception' => $exception::class,
            ]);

            return [];
        }
    }
}
