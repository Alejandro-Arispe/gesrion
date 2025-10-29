<?php

namespace App\Http\Controllers\ConfiguracionAcademica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\GestionAcademica;
use Exception;

class GestionController extends Controller
{
    /**
     * Listar gestiones académicas
     */
    public function index(Request $request)
    {
        try {
            $query = GestionAcademica::query();

            // Filtro por estado
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            // Filtro por año
            if ($request->has('anio')) {
                $query->where('anio', $request->anio);
            }

            $gestiones = $query->orderBy('anio', 'desc')->get();

            return response()->json([
                'gestiones' => $gestiones
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener gestiones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva gestión
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'anio' => 'required|integer|min:2020|max:2100',
                'semestre' => 'required|in:1,2',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after:fecha_inicio',
                'estado' => 'boolean'
            ]);

            // Verificar que no exista la misma gestión
            $existe = GestionAcademica::where('anio', $request->anio)
                ->where('semestre', $request->semestre)
                ->exists();

            if ($existe) {
                return response()->json([
                    'message' => 'Ya existe una gestión para este año y semestre'
                ], 400);
            }

            $gestion = GestionAcademica::create($request->all());

            return response()->json([
                'message' => 'Gestión creada exitosamente',
                'gestion' => $gestion
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al crear gestión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una gestión específica
     */
    public function show($id)
    {
        try {
            $gestion = GestionAcademica::with(['materias', 'grupos'])->findOrFail($id);

            return response()->json([
                'gestion' => $gestion
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gestión no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar gestión
     */
    public function update(Request $request, $id)
    {
        try {
            $gestion = GestionAcademica::findOrFail($id);

            $request->validate([
                'anio' => 'required|integer|min:2020|max:2100',
                'semestre' => 'required|in:1,2',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after:fecha_inicio',
                'estado' => 'boolean'
            ]);

            $gestion->update($request->all());

            return response()->json([
                'message' => 'Gestión actualizada exitosamente',
                'gestion' => $gestion
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar gestión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar una gestión y desactivar las demás
     */
    public function activar($id)
    {
        try {
            GestionAcademica::query()->update(['estado' => false]);
            $gestion = GestionAcademica::findOrFail($id);
            $gestion->update(['estado' => true]);

            return response()->json([
                'message' => 'Gestión activada exitosamente',
                'gestion' => $gestion
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al activar gestión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener gestión activa
     */
    public function activa()
    {
        try {
            $gestion = GestionAcademica::where('estado', true)->first();

            if (!$gestion) {
                return response()->json([
                    'message' => 'No hay gestión activa'
                ], 404);
            }

            return response()->json([
                'gestion' => $gestion
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener gestión activa',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}