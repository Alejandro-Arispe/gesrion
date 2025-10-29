<?php

namespace App\Http\Controllers\ConfiguracionAcademica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\Docente;
use Exception;

class DocenteController extends Controller
{
    /**
     * Listar docentes
     */
    public function index(Request $request)
    {
        try {
            $query = Docente::with('facultad');

            // Filtro por facultad
            if ($request->has('id_facultad')) {
                $query->where('id_facultad', $request->id_facultad);
            }

            // Filtro por estado
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            // Búsqueda por nombre o CI
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'ILIKE', "%{$search}%")
                      ->orWhere('ci', 'ILIKE', "%{$search}%");
                });
            }

            $docentes = $query->orderBy('nombre')->get();

            return response()->json([
                'docentes' => $docentes
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener docentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear docente
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'ci' => 'required|string|max:20|unique:docente,ci',
                'nombre' => 'required|string|max:100',
                'correo' => 'required|email|unique:docente,correo',
                'telefono' => 'nullable|string|max:20',
                'sexo' => 'nullable|in:M,F',
                'id_facultad' => 'required|exists:facultad,id_facultad',
                'estado' => 'boolean'
            ]);

            $docente = Docente::create($request->all());

            return response()->json([
                'message' => 'Docente creado exitosamente',
                'docente' => $docente->load('facultad')
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al crear docente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar docente
     */
    public function show($id)
    {
        try {
            $docente = Docente::with(['facultad', 'grupos.materia', 'horarios'])->findOrFail($id);

            return response()->json([
                'docente' => $docente
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Docente no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar docente
     */
    public function update(Request $request, $id)
    {
        try {
            $docente = Docente::findOrFail($id);

            $request->validate([
                'ci' => 'required|string|max:20|unique:docente,ci,' . $id . ',id_docente',
                'nombre' => 'required|string|max:100',
                'correo' => 'required|email|unique:docente,correo,' . $id . ',id_docente',
                'telefono' => 'nullable|string|max:20',
                'sexo' => 'nullable|in:M,F',
                'id_facultad' => 'required|exists:facultad,id_facultad',
                'estado' => 'boolean'
            ]);

            $docente->update($request->all());

            return response()->json([
                'message' => 'Docente actualizado exitosamente',
                'docente' => $docente->load('facultad')
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar docente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar docente
     */
    public function destroy($id)
    {
        try {
            $docente = Docente::findOrFail($id);

            // Verificar si tiene grupos asignados
            if ($docente->grupos()->count() > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar el docente porque tiene grupos asignados'
                ], 400);
            }

            $docente->delete();

            return response()->json([
                'message' => 'Docente eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar docente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}