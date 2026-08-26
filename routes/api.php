<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RealtimePositionController;
use Illuminate\Http\Request;
use App\Support\Authorization\RolePermissionMap;
use App\Support\Authorization\Permission;
use Illuminate\Validation\Rule;

// Rutas de autenticación API
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/generate-token', function (Request $request) {
        abort_unless(RolePermissionMap::allows($request->user(), Permission::TOKENS_MANAGE), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'array|nullable',
            'abilities.*' => [Rule::in(['mobile:read', 'mobile:write', 'messages:read', 'messages:write'])],
        ]);

        $user = $request->user();
        $expiresAt = now()->addHours(24);
        $token = $user->createToken(
            $request->name,
            $request->abilities ?: ['mobile:read', 'mobile:write'],
            $expiresAt,
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_id' => $token->accessToken->id ?? null,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    });

});
