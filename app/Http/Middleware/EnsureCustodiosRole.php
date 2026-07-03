<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustodiosRole
{
    private const ALLOWED_ROLES = [
        'CUSTODIOS',
        'ADMIN',
        'ADMINISTRADOR',
    ];

    /**
     * Restringe el módulo de custodios a los roles operativos autorizados.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role = strtoupper(trim((string) $request->user()?->rol));

        abort_unless(in_array($role, self::ALLOWED_ROLES, true), 403);

        return $next($request);
    }
}
