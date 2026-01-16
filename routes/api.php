<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RealtimePositionController;
use Illuminate\Http\Request;

// Rutas de autenticación API
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/generate-token', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'array|nullable'
        ]);

        $user = $request->user();
        $token = $user->createToken(
            $request->name,
            $request->abilities ?: ['*']
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_id' => $token->accessToken->id ?? null,
        ]);
    });

});
