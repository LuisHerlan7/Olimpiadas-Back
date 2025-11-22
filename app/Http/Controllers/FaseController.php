<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Fase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FaseController extends Controller
{
    /**
     * Obtener el estado de la fase de inscripción
     */
    public function getInscripcion(): JsonResponse
    {
        try {
            $fase = Fase::where('nombre', 'inscripcion')->first();
            
            if (!$fase) {
                // Si no existe, crear una por defecto
                $fase = Fase::create([
                    'nombre' => 'inscripcion',
                    'activa' => false,
                    'cancelada' => false,
                ]);
            }

            $activa = $fase->estaActiva();
            
            return response()->json([
                'id' => $fase->id,
                'activa' => $activa,
                'fecha_inicio' => $fase->fecha_inicio?->toIso8601String(),
                'fecha_fin' => $fase->fecha_fin?->toIso8601String(),
                'cancelada' => $fase->cancelada,
                'mensaje' => $fase->mensaje ?? ($activa 
                    ? 'Fase de inscripción activa' 
                    : 'Fase de inscripción no está activa'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Error de base de datos al obtener fase: " . $e->getMessage());
            return response()->json([
                'activa' => false,
                'mensaje' => 'Error de base de datos. Verifica que la tabla "fases" exista. Ejecuta: php artisan migrate',
                'error' => config('app.debug') ? $e->getMessage() : 'Error de base de datos',
            ], 500);
        } catch (\Exception $e) {
            Log::error("Error al obtener fase de inscripción: " . $e->getMessage());
            return response()->json([
                'activa' => false,
                'mensaje' => 'Error al obtener el estado de la fase',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    /**
     * Actualizar la fase de inscripción
     */
    public function updateInscripcion(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'activa' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $fase = Fase::where('nombre', 'inscripcion')->first();
            
            if (!$fase) {
                $fase = new Fase();
                $fase->nombre = 'inscripcion';
                $fase->activa = false;
                $fase->cancelada = false;
            }

            if ($request->has('fecha_inicio') && $request->fecha_inicio) {
                try {
                    $fase->fecha_inicio = \Carbon\Carbon::parse($request->fecha_inicio);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Error al parsear fecha_inicio',
                        'error' => $e->getMessage(),
                    ], 422);
                }
            } elseif ($request->has('fecha_inicio') && !$request->fecha_inicio) {
                $fase->fecha_inicio = null;
            }
            
            if ($request->has('fecha_fin') && $request->fecha_fin) {
                try {
                    $fase->fecha_fin = \Carbon\Carbon::parse($request->fecha_fin);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Error al parsear fecha_fin',
                        'error' => $e->getMessage(),
                    ], 422);
                }
            } elseif ($request->has('fecha_fin') && !$request->fecha_fin) {
                $fase->fecha_fin = null;
            }
            
            if ($request->has('activa')) {
                $fase->activa = (bool) $request->activa;
            }

            $fase->cancelada = false; // Al actualizar, se reactiva si estaba cancelada
            $fase->mensaje = null; // Limpiar mensaje al actualizar
            $fase->save();

            $activa = $fase->estaActiva();

            return response()->json([
                'message' => 'Fase de inscripción actualizada exitosamente',
                'data' => [
                    'id' => $fase->id,
                    'activa' => $activa,
                    'fecha_inicio' => $fase->fecha_inicio?->toIso8601String(),
                    'fecha_fin' => $fase->fecha_fin?->toIso8601String(),
                    'cancelada' => $fase->cancelada,
                    'mensaje' => $fase->mensaje ?? ($activa 
                        ? 'Fase de inscripción activa' 
                        : 'Fase de inscripción no está activa'),
                ],
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Error de base de datos al actualizar fase: " . $e->getMessage());
            return response()->json([
                'message' => 'Error de base de datos. Verifica que la tabla "fases" exista. Ejecuta: php artisan migrate',
                'error' => config('app.debug') ? $e->getMessage() : 'Error de base de datos',
            ], 500);
        } catch (\Exception $e) {
            Log::error("Error al actualizar fase de inscripción: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return response()->json([
                'message' => 'Error al actualizar la fase de inscripción',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Cancelar la fase de inscripción
     */
    public function cancelarInscripcion(): JsonResponse
    {
        try {
            $fase = Fase::where('nombre', 'inscripcion')->first();
            
            if (!$fase) {
                $fase = new Fase();
                $fase->nombre = 'inscripcion';
            }

            $fase->cancelada = true;
            $fase->activa = false;
            $fase->mensaje = 'Fase de inscripción cancelada';
            $fase->save();

            return response()->json([
                'message' => 'Fase de inscripción cancelada exitosamente',
                'data' => [
                    'id' => $fase->id,
                    'activa' => false,
                    'cancelada' => true,
                    'mensaje' => $fase->mensaje,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Error al cancelar fase de inscripción: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al cancelar la fase de inscripción',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener el estado de la fase de asignación (subida de notas)
     */
    public function getAsignacion(): JsonResponse
    {
        try {
            $fase = Fase::where('nombre', 'asignacion')->first();
            
            if (!$fase) {
                $fase = Fase::create([
                    'nombre' => 'asignacion',
                    'activa' => false,
                    'cancelada' => false,
                ]);
            }

            $activa = $fase->estaActiva();
            
            return response()->json([
                'id' => $fase->id,
                'activa' => $activa,
                'fecha_inicio' => $fase->fecha_inicio?->toIso8601String(),
                'fecha_fin' => $fase->fecha_fin?->toIso8601String(),
                'cancelada' => $fase->cancelada,
                'mensaje' => $fase->mensaje ?? ($activa 
                    ? 'Fase de asignación (subida de notas) activa' 
                    : 'Fase de asignación (subida de notas) no está activa'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Error de base de datos al obtener fase de asignación: " . $e->getMessage());
            return response()->json([
                'activa' => false,
                'mensaje' => 'Error de base de datos. Verifica que la tabla "fases" exista.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error de base de datos',
            ], 500);
        } catch (\Exception $e) {
            Log::error("Error al obtener fase de asignación: " . $e->getMessage());
            return response()->json([
                'activa' => false,
                'mensaje' => 'Error al obtener el estado de la fase',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    /**
     * Actualizar la fase de asignación
     */
    public function updateAsignacion(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'activa' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $fase = Fase::where('nombre', 'asignacion')->first();
            
            if (!$fase) {
                $fase = new Fase();
                $fase->nombre = 'asignacion';
                $fase->activa = false;
                $fase->cancelada = false;
            }

            if ($request->has('fecha_inicio') && $request->fecha_inicio) {
                try {
                    $fase->fecha_inicio = \Carbon\Carbon::parse($request->fecha_inicio);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Error al parsear fecha_inicio',
                        'error' => $e->getMessage(),
                    ], 422);
                }
            } elseif ($request->has('fecha_inicio') && !$request->fecha_inicio) {
                $fase->fecha_inicio = null;
            }
            
            if ($request->has('fecha_fin') && $request->fecha_fin) {
                try {
                    $fase->fecha_fin = \Carbon\Carbon::parse($request->fecha_fin);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Error al parsear fecha_fin',
                        'error' => $e->getMessage(),
                    ], 422);
                }
            } elseif ($request->has('fecha_fin') && !$request->fecha_fin) {
                $fase->fecha_fin = null;
            }
            
            if ($request->has('activa')) {
                $fase->activa = (bool) $request->activa;
            }

            $fase->cancelada = false;
            $fase->mensaje = null;
            $fase->save();

            $activa = $fase->estaActiva();

            return response()->json([
                'message' => 'Fase de asignación actualizada exitosamente',
                'data' => [
                    'id' => $fase->id,
                    'activa' => $activa,
                    'fecha_inicio' => $fase->fecha_inicio?->toIso8601String(),
                    'fecha_fin' => $fase->fecha_fin?->toIso8601String(),
                    'cancelada' => $fase->cancelada,
                    'mensaje' => $fase->mensaje ?? ($activa 
                        ? 'Fase de asignación (subida de notas) activa' 
                        : 'Fase de asignación (subida de notas) no está activa'),
                ],
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Error de base de datos al actualizar fase de asignación: " . $e->getMessage());
            return response()->json([
                'message' => 'Error de base de datos. Verifica que la tabla "fases" exista.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error de base de datos',
            ], 500);
        } catch (\Exception $e) {
            Log::error("Error al actualizar fase de asignación: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return response()->json([
                'message' => 'Error al actualizar la fase de asignación',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Cancelar la fase de asignación
     */
    public function cancelarAsignacion(): JsonResponse
    {
        try {
            $fase = Fase::where('nombre', 'asignacion')->first();
            
            if (!$fase) {
                $fase = new Fase();
                $fase->nombre = 'asignacion';
            }

            $fase->cancelada = true;
            $fase->activa = false;
            $fase->mensaje = 'Fase de asignación (subida de notas) cancelada';
            $fase->save();

            return response()->json([
                'message' => 'Fase de asignación cancelada exitosamente',
                'data' => [
                    'id' => $fase->id,
                    'activa' => false,
                    'cancelada' => true,
                    'mensaje' => $fase->mensaje,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Error al cancelar fase de asignación: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al cancelar la fase de asignación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

