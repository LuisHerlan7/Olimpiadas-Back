<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{
    /**
     * Display a listing of the areas.
     */
    public function index()
    {
        return Area::select('id', 'nombre', 'codigo', 'descripcion', 'activo')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Store a newly created area in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:10|unique:areas,codigo',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $area = Area::create([
            'nombre' => $request->nombre,
            'codigo' => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'activo' => $request->boolean('activo', true),
        ]);

        return response()->json($area, 201);
    }

    /**
     * Display the specified area.
     */
    public function show(Area $area)
    {
        return $area;
    }

    /**
     * Update the specified area in storage.
     */
    public function update(Request $request, Area $area)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'codigo' => 'sometimes|required|string|max:10|unique:areas,codigo,' . $area->id,
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $area->update([
            'nombre' => $request->has('nombre') ? $request->nombre : $area->nombre,
            'codigo' => $request->has('codigo') ? strtoupper($request->codigo) : $area->codigo,
            'descripcion' => $request->has('descripcion') ? $request->descripcion : $area->descripcion,
            'activo' => $request->has('activo') ? $request->boolean('activo') : $area->activo,
        ]);

        return response()->json($area);
    }

    /**
     * Remove the specified area from storage.
     */
    public function destroy(Area $area)
    {
        try {
            $area->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'No se pudo eliminar el área',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
