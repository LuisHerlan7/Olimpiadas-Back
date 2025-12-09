<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ResponsableToken;
use App\Models\Responsable;
use App\Models\Usuario;
use Laravel\Sanctum\PersonalAccessToken;

class AuthResponsable
{
    public function handle(Request $request, Closure $next)
    {
        // 1) Bearer estándar
        $bearer = $request->bearerToken();

        // 2) Fallback: ?token=xxxx (para exportaciones vía <a>)
        if (!$bearer && $request->has('token')) {
            $bearer = (string) $request->query('token');
        }

        if (!$bearer) {
            return response()->json(['message' => 'Token faltante.'], 401);
        }

        // ============================================================
        // INTENTO 1: Token plano de ResponsableToken
        // ============================================================
        $hash = hash('sha256', $bearer);
        $tokenRow = ResponsableToken::where('token', $hash)->first();
        
        if ($tokenRow) {
            if ($tokenRow->expires_at && now()->greaterThan($tokenRow->expires_at)) {
                return response()->json(['message' => 'Token expirado.'], 401);
            }

            $responsable = Responsable::find($tokenRow->responsable_id);
            if (!$responsable) {
                return response()->json(['message' => 'Responsable no encontrado.'], 401);
            }

            $request->merge(['responsable' => $responsable]);
            return $next($request);
        }

        // ============================================================
        // INTENTO 2: Token de Sanctum (Usuario con rol RESPONSABLE)
        // ============================================================
        try {
            $sanctumToken = PersonalAccessToken::findToken($bearer);
            if ($sanctumToken && $sanctumToken->tokenable) {
                $usuario = $sanctumToken->tokenable;
                
                // Asegurar que los roles estén cargados
                if (!$usuario->relationLoaded('roles')) {
                    $usuario->load('roles');
                }
                
                // Verificar que sea un Usuario y tenga rol RESPONSABLE
                if ($usuario instanceof Usuario) {
                    // Verificar rol (case-insensitive)
                    $tieneRol = $usuario->roles->contains(function ($rol) {
                        $slugUpper = strtoupper(trim($rol->slug ?? ''));
                        return $slugUpper === 'RESPONSABLE' || 
                               $slugUpper === 'RESPONSABLE_ACADEMICO' ||
                               $slugUpper === 'RESPONSABLE-ACADEMICO';
                    });
                    
                    if ($tieneRol) {
                        // Buscar Responsable por correo (pueden estar relacionados por correo)
                        $responsable = Responsable::where('correo', $usuario->correo)->first();
                        
                        if ($responsable) {
                            $request->merge(['responsable' => $responsable]);
                            return $next($request);
                        }
                        
                        // Si no hay Responsable asociado, crear uno temporal basado en el Usuario
                        // Esto permite que usuarios del sistema con rol RESPONSABLE accedan
                        $responsable = new Responsable();
                        $responsable->setAttribute('id', $usuario->id); // Usar el ID del Usuario
                        $responsable->setAttribute('nombres', $usuario->nombres);
                        $responsable->setAttribute('apellidos', $usuario->apellidos);
                        $responsable->setAttribute('correo', $usuario->correo);
                        $responsable->setAttribute('ci', $usuario->ci ?? '');
                        $responsable->setAttribute('telefono', $usuario->telefono);
                        $responsable->setAttribute('activo', $usuario->estado ?? true);
                        $responsable->setAttribute('area_id', null);
                        $responsable->setAttribute('nivel_id', null);
                        $responsable->exists = false; // Marcar como no persistido
                        
                        $request->merge(['responsable' => $responsable]);
                        return $next($request);
                    }
                }
            }
        } catch (\Exception $e) {
            // Si hay error al validar Sanctum, continuar con el flujo normal
            // (no hacer nada, dejar que falle al final)
        }

        // ============================================================
        // Si llegamos aquí, el token no es válido
        // ============================================================
        return response()->json(['message' => 'Token inválido o sin permisos de responsable.'], 401);
    }
}
