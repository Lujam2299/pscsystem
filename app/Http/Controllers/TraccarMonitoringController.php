<?php

namespace App\Http\Controllers;

use App\Services\TraccarClient;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
