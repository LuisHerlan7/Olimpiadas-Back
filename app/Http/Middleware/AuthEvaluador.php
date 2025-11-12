<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\EvaluadorToken;
use App\Models\Evaluador;
use App\Models\Usuario;
use Laravel\Sanctum\PersonalAccessToken;

class AuthEvaluador
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1) Bearer estándar
        $bearer = $request->bearerToken();

        // 2) Fallback: ?token=xxxx (para exportaciones vía <a>)
        if (!$bearer && $request->has('token')) {
            $bearer = (string) $request->query('token');
        }

        if (!$bearer) {
            return response()->json(['message' => 'Token no provisto.'], 401);
        }

        // ============================================================
        // INTENTO 1: Token plano de EvaluadorToken
        // ============================================================
        $hash = hash('sha256', $bearer);
        $row = EvaluadorToken::with('evaluador')
            ->where('token', $hash)
            ->first();

        if ($row && $row->evaluador) {
            // if ($row->evaluador->activo === false) {
            //     return response()->json(['message' => 'Evaluador inactivo.'], 401);
            // }

            $request->merge(['evaluador' => $row->evaluador]);
            return $next($request);
        }

        // ============================================================
        // INTENTO 2: Token de Sanctum (Usuario con rol EVALUADOR)
        // ============================================================
        try {
            $sanctumToken = PersonalAccessToken::findToken($bearer);
            if ($sanctumToken && $sanctumToken->tokenable) {
                $usuario = $sanctumToken->tokenable;
                
                // Asegurar que los roles estén cargados
                if (!$usuario->relationLoaded('roles')) {
                    $usuario->load('roles');
                }
                
                // Verificar que sea un Usuario y tenga rol EVALUADOR
                if ($usuario instanceof Usuario) {
                    // Verificar rol (case-insensitive)
                    $tieneRol = $usuario->roles->contains(function ($rol) {
                        $slugUpper = strtoupper(trim($rol->slug ?? ''));
                        return $slugUpper === 'EVALUADOR';
                    });
                    
                    if ($tieneRol) {
                        // Buscar Evaluador por correo (pueden estar relacionados por correo)
                        $evaluador = Evaluador::where('correo', $usuario->correo)->first();
                        
                        if ($evaluador) {
                            $request->merge(['evaluador' => $evaluador]);
                            return $next($request);
                        }
                        
                        // Si no hay Evaluador asociado, crear uno temporal basado en el Usuario
                        // Esto permite que usuarios del sistema con rol EVALUADOR accedan
                        $evaluador = new Evaluador();
                        $evaluador->setAttribute('id', -1); // ID temporal
                        $evaluador->setAttribute('nombres', $usuario->nombres);
                        $evaluador->setAttribute('apellidos', $usuario->apellidos);
                        $evaluador->setAttribute('correo', $usuario->correo);
                        $evaluador->setAttribute('ci', $usuario->ci ?? '');
                        $evaluador->setAttribute('telefono', $usuario->telefono);
                        $evaluador->setAttribute('activo', $usuario->estado ?? true);
                        $evaluador->exists = false; // Marcar como no persistido
                        
                        $request->merge(['evaluador' => $evaluador]);
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
        return response()->json(['message' => 'Token inválido o sin permisos de evaluador.'], 401);
    }
}
