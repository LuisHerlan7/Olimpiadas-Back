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

        // Cargar roles siempre para asegurar que estén disponibles (con todos los campos)
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }
        
        // Obtener slugs y nombres de los roles del usuario (normalizados a mayúsculas)
        $userRoles = $user->roles ?? collect();
        
        // Si no hay roles, intentar cargar directamente desde la base de datos
        if ($userRoles->isEmpty()) {
            $userRoles = $user->roles()->get();
        }
        $userRoleIdentifiers = collect($userRoles)
            ->map(function($r) {
                if (is_array($r)) {
                    $slug = strtoupper(trim((string)($r['slug'] ?? '')));
                    $nombre = strtoupper(trim((string)($r['nombre'] ?? '')));
                    return [$slug, $nombre];
                }
                if (is_object($r)) {
                    $slug = strtoupper(trim((string)($r->slug ?? '')));
                    $nombre = strtoupper(trim((string)($r->nombre ?? '')));
                    return [$slug, $nombre];
                }
                return ['', ''];
            })
            ->flatten()
            ->filter();

        // Normalizar roles requeridos a mayúsculas y también generar variantes
        $required = collect($roles)
            ->map(function($r) {
                $normalized = strtoupper(trim((string)$r));
                // Generar variantes comunes
                $variants = [$normalized];
                // Si es "ADMINISTRADOR", también buscar "ADMIN"
                if ($normalized === 'ADMINISTRADOR') {
                    $variants[] = 'ADMIN';
                }
                // Si es "RESPONSABLE", también buscar variantes
                if (str_contains($normalized, 'RESPONSABLE')) {
                    $variants[] = 'RESPONSABLE_ACADEMICO';
                    $variants[] = 'RESPONSABLE-ACADEMICO';
                }
                return $variants;
            })
            ->flatten()
            ->filter();

        // Verificar si hay intersección entre los identificadores del usuario y los requeridos
        if ($userRoleIdentifiers->intersect($required)->isEmpty()) {
            return response()->json([
                'message' => 'Acceso denegado (rol insuficiente).',
                'debug' => config('app.debug') ? [
                    'user_roles' => $userRoleIdentifiers->toArray(),
                    'required' => $required->toArray(),
                ] : null,
            ], 403);
        }

        return $next($request);
    }
}
