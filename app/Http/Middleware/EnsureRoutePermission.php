<?php

namespace App\Http\Middleware;

use App\Support\Authorization\RolePermissionMap;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoutePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (! $user || ! $routeName) {
            return $next($request);
        }

        foreach (config('route-permissions', []) as $pattern => $permissions) {
            if (! Str::is($pattern, $routeName)) {
                continue;
            }

            $allowed = collect((array) $permissions)->contains(
                fn (string $permission) => RolePermissionMap::allows($user, $permission)
            );

            if (! $allowed) {
                Log::warning('Acceso denegado por permiso de ruta.', [
                    'user_id' => $user->id,
                    'route' => $routeName,
                    'permission' => $permissions,
                    'ip' => $request->ip(),
                ]);

                abort(403, 'No tienes permiso para acceder a este módulo.');
            }

            break;
        }

        return $next($request);
    }
}
