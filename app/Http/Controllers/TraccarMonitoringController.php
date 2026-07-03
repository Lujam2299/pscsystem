<?php

namespace App\Http\Controllers;

use App\Services\TraccarClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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
}
