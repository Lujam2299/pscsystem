<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RealtimePosition;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RealtimePositionController extends Controller
{
    public function getUserRecentPositions($id, Request $request)
    {
        // Verificar si el usuario está autenticado (por sesión)
        if (!Auth::check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        // Opcional: Verificar si el usuario puede ver este historial
        // if (Auth::id() !== (int)$id) {
        //     abort(403, 'No tienes permiso para ver este historial');
        // }

        $periodo = Carbon::now()->subHours(24);

        $positionsQuery = RealtimePosition::where('user_id', $id)
            ->where('recorded_at', '>', $periodo)
            ->orderBy('recorded_at', 'desc')
            ->select('latitude', 'longitude', 'recorded_at', 'device_id');

        $limit = max(0, min($request->integer('limit', 0), 500));
        if ($limit > 0) {
            $positionsQuery->limit($limit);
        }

        $positions = $positionsQuery->get();

        return response()->json([
            'user_id' => $id,
            'positions' => $positions,
            'total' => $positions->count()
        ]);
    }
}
