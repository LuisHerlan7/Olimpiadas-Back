<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BitacoraController extends Controller
{
    /**
     * GET /admin/bitacoras
     * Lista los eventos de bitácora (solo ADMIN)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = max(10, min(200, (int) $request->input('per_page', 50)));
            $page = max(1, (int) $request->input('page', 1));

            // Ordenar del más reciente al más antiguo (más actual primero)
            // Ordenar por created_at descendente y luego por id descendente para garantizar orden consistente
            $query = Bitacora::query()
                ->orderByDesc('created_at')
                ->orderByDesc('id');

            // Filtro opcional por correo (sanitizado para prevenir SQL injection)
            if ($request->filled('actor_email')) {
                $email = trim($request->input('actor_email'));
                if (!empty($email)) {
                    $query->where('actor_email', 'like', '%' . $email . '%');
                }
            }

            // Filtro opcional por tipo (validado contra valores permitidos)
            if ($request->filled('actor_tipo')) {
                $tipo = strtoupper(trim($request->input('actor_tipo')));
                $tiposPermitidos = ['ADMIN', 'ADMINISTRADOR', 'EVALUADOR', 'RESPONSABLE'];
                if (in_array($tipo, $tiposPermitidos)) {
                    $query->where('actor_tipo', $tipo);
                }
            }

            $bitacoras = $query->paginate($perPage, ['*'], 'page', $page);

            // Transformar para el frontend
            $bitacoras->getCollection()->transform(function ($b) {
                try {
                    $createdAt = $b->created_at;
                    $hora = null;
                    $fecha = null;
                    $createdAtStr = null;

                    if ($createdAt) {
                        // Asegurar que sea una instancia de Carbon con zona horaria correcta
                        if (is_string($createdAt)) {
                            $dt = \Carbon\Carbon::parse($createdAt)->setTimezone('America/La_Paz');
                        } elseif ($createdAt instanceof \Carbon\Carbon) {
                            $dt = $createdAt->copy()->setTimezone('America/La_Paz');
                        } else {
                            $dt = \Carbon\Carbon::parse($createdAt)->setTimezone('America/La_Paz');
                        }
                        
                        $createdAtStr = $dt->toIso8601String();
                        $hora = $dt->format('H:i:s');
                        $fecha = $dt->format('Y-m-d');
                    }

                    return [
                        'id' => $b->id ?? 0,
                        'actor_email' => $b->actor_email ?? '',
                        'actor_tipo' => $b->actor_tipo ?? '',
                        'mensaje' => $b->mensaje ?? '',
                        'created_at' => $createdAtStr,
                        'hora' => $hora,
                        'fecha' => $fecha,
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error transforming bitacora entry: ' . $e->getMessage());
                    return [
                        'id' => $b->id ?? 0,
                        'actor_email' => $b->actor_email ?? '',
                        'actor_tipo' => $b->actor_tipo ?? '',
                        'mensaje' => $b->mensaje ?? '',
                        'created_at' => null,
                        'hora' => null,
                        'fecha' => null,
                    ];
                }
            });

            return response()->json($bitacoras);
        } catch (\Exception $e) {
            \Log::error('Error en BitacoraController@index: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Error al cargar bitácoras',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }
}

