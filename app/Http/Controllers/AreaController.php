<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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
            'nombre' => 'required|string|max:20',
            'codigo' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ], [
            'codigo.regex' => 'El código sólo puede contener letras y números sin caracteres especiales.',
            'nombre.max' => 'El nombre debe tener como máximo 20 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Normalize codigo to uppercase
        $codigo = strtoupper($request->codigo);

        // Check uniqueness case-insensitive
        $exists = Area::whereRaw('upper(codigo) = ?', [$codigo])->exists();
        if ($exists) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => ['codigo' => ["El código ya está en uso"]],
            ], 422);
        }

        $area = Area::create([
            'nombre' => $request->nombre,
            'codigo' => $codigo,
            'descripcion' => $request->descripcion,
            'activo' => $request->boolean('activo', true),
        ]);

        try {
            $user = Auth::user();
            $email = $user ? $user->correo : 'admin@ohsansi.bo';
            Bitacora::registrar($email, 'ADMIN', "creó área: {$area->nombre}");
        } catch (\Throwable) {}

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
            'nombre' => 'sometimes|required|string|max:20',
            'codigo' => ['sometimes', 'required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ], [
            'codigo.regex' => 'El código sólo puede contener letras y números sin caracteres especiales.',
            'nombre.max' => 'El nombre debe tener como máximo 20 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = [];
        if ($request->has('nombre')) $data['nombre'] = $request->nombre;
        if ($request->has('codigo')) {
            $newCodigo = strtoupper($request->codigo);
            // Check uniqueness case-insensitive excluding current area
            $exists = Area::whereRaw('upper(codigo) = ?', [$newCodigo])->where('id', '!=', $area->id)->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => ['codigo' => ["El código ya está en uso"]],
                ], 422);
            }
            $data['codigo'] = $newCodigo;
        }
        if ($request->has('descripcion')) $data['descripcion'] = $request->descripcion;
        // Use array_key_exists to detect presence of boolean 'activo' even when it's false
        $all = $request->all();
        if (array_key_exists('activo', $all)) {
            $data['activo'] = $request->boolean('activo');
        }

        $area->update($data);

        try {
            $user = Auth::user();
            $email = $user ? $user->correo : 'admin@ohsansi.bo';
            Bitacora::registrar($email, 'ADMIN', "editó área: {$area->nombre}");
        } catch (\Throwable) {}

        return response()->json($area);
    }

    /**
     * Remove the specified area from storage.
     */
    public function destroy(Area $area)
    {
        try {
            $nombreArea = $area->nombre;
            $area->delete();
            try {
                $user = Auth::user();
                $email = $user ? $user->correo : 'admin@ohsansi.bo';
                Bitacora::registrar($email, 'ADMIN', "eliminó área: {$nombreArea}");
            } catch (\Throwable) {}
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'No se pudo eliminar el área',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
