<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // Cargar roles siempre para asegurar que estén disponibles
        $user->load('roles');
        
        // Obtener slugs de los roles del usuario
        $userRoles = $user->roles ?? collect();
        $userSlugs = collect($userRoles)
            ->map(function($r) {
                if (is_array($r)) {
                    return strtoupper((string)($r['slug'] ?? ''));
                }
                if (is_object($r)) {
                    return strtoupper((string)($r->slug ?? ''));
                }
                return '';
            })
            ->filter();

        $required = collect($roles)->map(fn($r) => strtoupper((string)$r))->filter();

        if ($userSlugs->intersect($required)->isEmpty()) {
            return response()->json(['message' => 'Acceso denegado (rol insuficiente).'], 403);
        }

        return $next($request);
    }
}
