<?php

namespace App\Services;

use App\Models\GpsSpeedLimit;

class GpsOperationalReportService
{
    public function __construct(private readonly TraccarClient $traccar)
    {
    }

    /** @return array<string, mixed> */
    public function generate(array $requestedDeviceIds, string $from, string $to): array
    {
        $devices = collect($this->traccar->devices());
        $availableIds = $devices->pluck('id')->map(fn ($id) => (int) $id);
        $deviceIds = $requestedDeviceIds === []
            ? $availableIds->all()
            : $availableIds->intersect(array_map('intval', $requestedDeviceIds))->values()->all();

        $summaries = collect($this->traccar->summaryReport($deviceIds, $from, $to));
        $trips = collect($this->traccar->tripsReport($deviceIds, $from, $to));
        $stops = collect($this->traccar->stopsReport($deviceIds, $from, $to));
        $events = collect($this->traccar->recentEvents($deviceIds, $from, $to));
        $limits = GpsSpeedLimit::whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $rows = $devices->whereIn('id', $deviceIds)->map(function (array $device) use ($summaries, $trips, $stops, $events, $limits) {
            $deviceId = (int) $device['id'];
            $summary = $summaries->firstWhere('deviceId', $deviceId) ?? [];
            $deviceTrips = $trips->where('deviceId', $deviceId);
            $deviceStops = $stops->where('deviceId', $deviceId);
            $deviceEvents = $events->where('deviceId', $deviceId);
            $limit = $limits->get($deviceId);
            $limitKmh = $limit?->active ? (float) $limit->speed_limit_kmh : null;

            return [
                'device_id' => $deviceId,
                'device_name' => $device['name'] ?? "Unidad {$deviceId}",
                'distance_km' => round(((float) ($summary['distance'] ?? 0)) / 1000, 2),
                'max_speed_kmh' => round(((float) ($summary['maxSpeed'] ?? 0)) * 1.852, 1),
                'average_speed_kmh' => round(((float) ($summary['averageSpeed'] ?? 0)) * 1.852, 1),
                'engine_hours' => round(((float) ($summary['engineHours'] ?? 0)) / 3600000, 2),
                'spent_fuel' => round((float) ($summary['spentFuel'] ?? 0), 2),
                'trips_count' => $deviceTrips->count(),
                'stops_count' => $deviceStops->count(),
                'stopped_hours' => round($deviceStops->sum(fn ($stop) => (float) ($stop['duration'] ?? 0)) / 3600000, 2),
                'offline_events' => $deviceEvents->where('type', 'deviceOffline')->count(),
                'overspeed_events' => $deviceEvents->where('type', 'overspeed')->count(),
                'speed_limit_kmh' => $limitKmh,
                'trips_over_limit' => $limitKmh === null ? 0 : $deviceTrips->filter(
                    fn ($trip) => ((float) ($trip['maxSpeed'] ?? 0)) * 1.852 > $limitKmh,
                )->count(),
            ];
        })->values()->all();

        return [
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
            'trips' => $trips->values()->all(),
            'stops' => $stops->values()->all(),
            'events' => $events->values()->all(),
        ];
    }
}
