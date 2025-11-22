<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

use App\Models\Area;
use App\Models\Audit;

class AreaController extends Controller
{
    /**
     * Listar todas las áreas
     */
    public function index(Request $request)
    {
        try {
            $query = Area::query();

            if ($request->filled('search')) {
                $s = trim((string) $request->search);
                $query->where(function ($q) use ($s) {
                    $q->where('nombre', 'ilike', "%{$s}%")
                      ->orWhere('codigo', 'ilike', "%{$s}%");
                });
            }

            if ($request->filled('estado')) {
                $estado = $request->estado === 'activo' ? true : false;
                $query->where('activo', $estado);
            }

            $perPage = max(1, (int) $request->get('per_page', 50));
            return response()->json(
                $query->orderBy('nombre')->paginate($perPage),
                200
            );
        } catch (Throwable $e) {
            Log::error('AREAS index error', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Error al listar áreas.'], 500);
        }
    }

    /**
     * Obtener un área específica
     */
    public function show(Area $area)
    {
        try {
            return response()->json($area);
        } catch (Throwable $e) {
            Log::error('AREA show error', ['id' => $area->id, 'msg' => $e->getMessage()]);
            return response()->json(['message' => 'Error al obtener área.'], 500);
        }
    }

    /**
     * Crear nueva área
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:areas,nombre',
            'codigo' => 'nullable|string|max:50|unique:areas,codigo,NULL,id,deleted_at,NULL',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        try {
            $area = DB::transaction(function () use ($validated) {
                $data = [
                    'nombre' => $validated['nombre'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'activo' => $validated['activo'] ?? true,
                ];
                // Solo asignar codigo si no es vacío
                if (!empty($validated['codigo'])) {
                    $data['codigo'] = $validated['codigo'];
                }
                return Area::create($data);
            });

            try {
                Audit::log(Auth::id(), 'Area', $area->id, 'CREAR', $area->toArray());
            } catch (Throwable) {
                // Silencio en auditoría
            }

            return response()->json(['message' => 'Área creada', 'data' => $area], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            Log::error('AREA store error', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Error al crear área.'], 500);
        }
    }

    /**
     * Actualizar área
     */
    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:areas,nombre,' . $area->id,
            'codigo' => 'nullable|string|max:50|unique:areas,codigo,' . $area->id . ',id,deleted_at,NULL',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $before = $area->toArray();

        try {
            DB::transaction(function () use ($area, $validated) {
                $data = [
                    'nombre' => $validated['nombre'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'activo' => $validated['activo'] ?? true,
                ];
                // Solo asignar codigo si no es vacío
                if (!empty($validated['codigo'])) {
                    $data['codigo'] = $validated['codigo'];
                } else {
                    $data['codigo'] = null;
                }
                $area->update($data);
            });

            try {
                Audit::log(Auth::id(), 'Area', $area->id, 'EDITAR', [
                    'before' => $before,
                    'after' => $area->fresh()->toArray(),
                ]);
            } catch (Throwable) {
                // Silencio en auditoría
            }

            return response()->json(['message' => 'Área actualizada', 'data' => $area->fresh()]);
        } catch (Throwable $e) {
            Log::error('AREA update error', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Error al actualizar área: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar área
     */
    public function destroy(Area $area)
    {
        try {
            // Verificar si tiene relaciones activas
            $hasEvaluadores = $area->evaluadores()->exists();
            if ($hasEvaluadores) {
                return response()->json([
                    'message' => 'No se puede eliminar. Esta área tiene evaluadores asociados.',
                ], Response::HTTP_CONFLICT);
            }

            $before = $area->toArray();

            DB::transaction(function () use ($area) {
                $area->delete();
            });

            try {
                Audit::log(Auth::id(), 'Area', $area->id, 'ELIMINAR', $before);
            } catch (Throwable) {
                // Silencio en auditoría
            }

            return response()->json(['message' => 'Área eliminada']);
        } catch (Throwable $e) {
            Log::error('AREA destroy error', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Error al eliminar área.'], 500);
        }
    }
}
