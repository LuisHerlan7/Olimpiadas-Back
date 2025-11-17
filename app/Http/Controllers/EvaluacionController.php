<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarEvaluacionRequest;
use App\Http\Requests\FinalizarEvaluacionRequest;
use App\Models\Evaluacion;
use App\Models\Inscrito;
use App\Models\Area;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EvaluacionController extends Controller
{
    /**
     * GET /evaluaciones/asignadas
     * Lista de inscritos que el evaluador puede evaluar según sus asociaciones (área/nivel).
     * NOTA: En tu modelo Inscrito, área/nivel son STRINGS (no FKs).
     */
    public function asignadas(Request $request)
    {
        /** @var \App\Models\Evaluador|null $evaluador */
        $evaluador = $request->input('evaluador'); // inyectado por middleware auth.evaluador
        if (!$evaluador) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        try {
            $perPage = max(1, min((int)$request->get('per_page', 10), 100));
            $search  = trim((string)$request->get('search', ''));

            // Query de inscritos: mostrar TODOS sin filtrar por área/nivel
            $q = Inscrito::query();
            
            // Solo filtro por búsqueda de texto (nombres/apellidos/documento)
            if ($search !== '') {
                $s = mb_strtolower($search);
                $q->where(function ($qq) use ($s) {
                    $qq->whereRaw('LOWER(nombres)   LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(apellidos) LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(documento) LIKE ?', ["%{$s}%"]);
                });
            }

            $paginator = $q
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->paginate($perPage);

            // Transformamos cada inscrito para adjuntar evaluación y objetos area/nivel {id?, nombre}
            $paginator->getCollection()->transform(function ($i) use ($evaluador) {
                $eval = Evaluacion::where('inscrito_id', $i->id)
                    ->where('evaluador_id', $evaluador->id)
                    ->first();

                $areaId  = $this->resolverAreaIdPorNombre($i->area);
                $nivelId = $this->resolverNivelIdPorNombre($i->nivel);

                return [
                    'id'        => $i->id,
                    'nombres'   => $i->nombres,
                    'apellidos' => $i->apellidos,
                    'documento' => $i->documento,

                    // Enviamos objetos area/nivel con nombre (y id si existe en catálogo)
                    'area'      => $i->area ? ['id' => $areaId,  'nombre' => $i->area] : null,
                    'nivel'     => $i->nivel ? ['id' => $nivelId, 'nombre' => $i->nivel] : null,

                    'evaluacion' => $eval ? [
                        'id'            => $eval->id,
                        'estado'        => $eval->estado,
                        'nota_final'    => $eval->nota_final,
                        'concepto'      => $eval->concepto,
                        'finalizado_at' => $eval->finalizado_at,
                    ] : null,
                ];
            });

            return response()->json($paginator, 200);
        } catch (Throwable $e) {
            Log::error('EVALUACIONES asignadas error', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['message' => 'Error al listar asignaciones.'], 500);
        }
    }

    /**
     * POST /evaluaciones/{inscrito}/guardar
     * Crea/actualiza evaluación en estado "borrador".
     * Resolvemos area_id/nivel_id por nombre (si existen en catálogos).
     */
    public function guardar(GuardarEvaluacionRequest $request, Inscrito $inscrito)
    {
        /** @var \App\Models\Evaluador|null $evaluador */
        $evaluador = $request->input('evaluador');
        if (!$evaluador) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if (!$this->evaluadorPuedeEvaluar($evaluador, $inscrito)) {
            return response()->json(['message' => 'No autorizado para evaluar este inscrito.'], 403);
        }

        $data = $request->validated();

        try {
            $evaluacion = DB::transaction(function () use ($evaluador, $inscrito, $data) {
                $areaId  = $this->resolverAreaIdPorNombre($inscrito->area);
                $nivelId = $this->resolverNivelIdPorNombre($inscrito->nivel);

                // Verificar que el evaluador tenga un ID válido
                if (!isset($evaluador->id) || $evaluador->id <= 0) {
                    throw new \Exception('ID de evaluador inválido');
                }

                $eval = Evaluacion::firstOrNew([
                    'inscrito_id'  => $inscrito->id,
                    'evaluador_id' => $evaluador->id,
                ]);

                $eval->area_id       = $areaId;
                $eval->nivel_id      = $nivelId;
                
                // Manejar notas: asegurar que sea array o null
                if (array_key_exists('notas', $data)) {
                    $eval->notas = is_array($data['notas']) ? $data['notas'] : [];
                } else {
                    $eval->notas = [];
                }
                
                if (array_key_exists('nota_final', $data) && $data['nota_final'] !== null && $data['nota_final'] !== '') {
                    $eval->nota_final = $data['nota_final'];
                } else {
                    $eval->nota_final = null;
                }
                
                if (array_key_exists('concepto', $data) && $data['concepto'] !== null) {
                    $eval->concepto = $data['concepto'];
                } else {
                    $eval->concepto = null;
                }
                
                if (array_key_exists('observaciones', $data) && $data['observaciones'] !== null) {
                    $eval->observaciones = $data['observaciones'];
                } else {
                    $eval->observaciones = null;
                }

                $eval->estado = 'borrador';
                $eval->finalizado_at = null;
                $eval->save();

                return $eval->fresh();
            });

            return response()->json(['message' => 'Guardado en borrador', 'data' => $evaluacion], 200);
        } catch (Throwable $e) {
            Log::error('EVALUACION guardar error', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? null,
                'evaluador_id' => $evaluador->id ?? null,
                'inscrito_id' => $inscrito->id ?? null,
            ]);
            return response()->json([
                'message' => 'No se pudo guardar la evaluación.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * POST /evaluaciones/{inscrito}/finalizar
     * Valida y marca evaluación como "finalizado".
     */
    public function finalizar(FinalizarEvaluacionRequest $request, Inscrito $inscrito)
    {
        /** @var \App\Models\Evaluador|null $evaluador */
        $evaluador = $request->input('evaluador');
        if (!$evaluador) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if (!$this->evaluadorPuedeEvaluar($evaluador, $inscrito)) {
            return response()->json(['message' => 'No autorizado para evaluar este inscrito.'], 403);
        }

        $data = $request->validated();

        try {
            $evaluacion = DB::transaction(function () use ($evaluador, $inscrito, $data) {
                $areaId  = $this->resolverAreaIdPorNombre($inscrito->area);
                $nivelId = $this->resolverNivelIdPorNombre($inscrito->nivel);

                // Verificar que el evaluador tenga un ID válido
                if (!isset($evaluador->id) || $evaluador->id <= 0) {
                    throw new \Exception('ID de evaluador inválido');
                }

                $eval = Evaluacion::firstOrNew([
                    'inscrito_id'  => $inscrito->id,
                    'evaluador_id' => $evaluador->id,
                ]);

                $eval->area_id       = $areaId;
                $eval->nivel_id      = $nivelId;
                
                // Asegurar que notas sea un array
                $eval->notas = isset($data['notas']) && is_array($data['notas']) ? $data['notas'] : [];
                
                // Manejar nota_final: puede ser null si es DESCLASIFICADO
                if (isset($data['nota_final']) && $data['nota_final'] !== null && $data['nota_final'] !== '') {
                    $eval->nota_final = $data['nota_final'];
                } else {
                    // Si el concepto es DESCLASIFICADO, permitir null
                    if (isset($data['concepto']) && $data['concepto'] === 'DESCLASIFICADO') {
                        $eval->nota_final = null;
                    } else {
                        $eval->nota_final = $data['nota_final'] ?? 0;
                    }
                }
                
                $eval->concepto = $data['concepto'] ?? null;
                $eval->observaciones = $data['observaciones'] ?? null;

                $eval->estado = 'finalizado';
                $eval->finalizado_at = now();
                $eval->save();

                return $eval->fresh();
            });

            return response()->json(['message' => 'Evaluación finalizada', 'data' => $evaluacion], 200);
        } catch (Throwable $e) {
            Log::error('EVALUACION finalizar error', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? null,
                'evaluador_id' => $evaluador->id ?? null,
                'inscrito_id' => $inscrito->id ?? null,
            ]);
            return response()->json([
                'message' => 'No se pudo finalizar la evaluación.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * POST /evaluaciones/{inscrito}/reabrir  (Solo RESPONSABLE)
     * Cambia estado a "borrador".
     */
    public function reabrir(Request $request, Inscrito $inscrito)
    {
        /** @var \App\Models\Responsable|null $responsable */
        $responsable = $request->input('responsable'); // middleware auth.responsable
        if (!$responsable) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        try {
            $eval = Evaluacion::where('inscrito_id', $inscrito->id)->first();
            if (!$eval) {
                return response()->json(['message' => 'No existe evaluación para reabrir.'], 404);
            }

            $eval->estado = 'borrador';
            $eval->finalizado_at = null;
            $eval->save();

            return response()->json(['message' => 'Evaluación reabierta', 'data' => $eval], 200);
        } catch (Throwable $e) {
            Log::error('EVALUACION reabrir error', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['message' => 'No se pudo reabrir la evaluación.'], 500);
        }
    }

    /* ======================
       Helpers
       ====================== */

    /**
     * Autoriza si el evaluador puede evaluar al inscrito.
     * Ahora permite evaluar CUALQUIER inscrito sin restricciones de área/nivel.
     */
    private function evaluadorPuedeEvaluar($evaluador, Inscrito $inscrito): bool
    {
        // Todos los evaluadores pueden evaluar cualquier inscrito
        return true;
    }

    /** Resuelve Area.id por nombre (case-insensitive); si no existe retorna null. */
    private function resolverAreaIdPorNombre(?string $nombre): ?int
    {
        if (!$nombre) return null;
        $row = Area::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->first(['id']);
        return $row?->id;
    }

    /** Resuelve Nivel.id por nombre (case-insensitive); si no existe retorna null. */
    private function resolverNivelIdPorNombre(?string $nombre): ?int
    {
        if (!$nombre) return null;
        $row = Nivel::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->first(['id']);
        return $row?->id;
    }
}
