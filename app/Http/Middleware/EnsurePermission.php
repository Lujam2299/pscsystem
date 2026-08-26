<?php

namespace App\Http\Middleware;

use App\Support\Authorization\RolePermissionMap;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || ! collect($permissions)->contains(
            fn (string $permission) => RolePermissionMap::allows($user, $permission)
        )) {
            abort(403, 'No tienes permiso para realizar esta operación.');
        }

        return $next($request);
    }
}
