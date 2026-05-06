<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $userRole = $user?->role instanceof \App\Enums\Role
            ? $user->role->value
            : (string) $user?->role;

        if (!$user || !in_array($userRole, $roles)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return $next($request);
    }
}
