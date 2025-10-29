<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Administracion\Rol;
use App\Models\Administracion\Permiso;

class RolController extends Controller
{
    /**
     * Listar todos los roles
     */
    public function index()
    {
        $roles = Rol::with('permisos')->get();

        return response()->json([
            'roles' => $roles->map(function($rol) {
                return [
                    'id_rol' => $rol->id_rol,
                    'nombre' => $rol->nombre,
                    'descripcion' => $rol->descripcion,
                    'permisos' => $rol->permisos->pluck('nombre'),
                    'cantidad_usuarios' => $rol->usuarios->count()
                ];
            })
        ], 200);
    }

    /**
     * Crear nuevo rol
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:rol,nombre',
            'descripcion' => 'nullable|string|max:100',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permiso,id_permiso'
        ]);

        $rol = Rol::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        if ($request->has('permisos')) {
            $rol->permisos()->sync($request->permisos);
        }

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'rol' => $rol->load('permisos')
        ], 201);
    }

    /**
     * Mostrar un rol específico
     */
    public function show($id)
    {
        $rol = Rol::with('permisos', 'usuarios')->findOrFail($id);

        return response()->json([
            'rol' => [
                'id_rol' => $rol->id_rol,
                'nombre' => $rol->nombre,
                'descripcion' => $rol->descripcion,
                'permisos' => $rol->permisos,
                'usuarios_count' => $rol->usuarios->count()
            ]
        ], 200);
    }

    /**
     * Actualizar rol
     */
    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:50|unique:rol,nombre,' . $id . ',id_rol',
            'descripcion' => 'nullable|string|max:100',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permiso,id_permiso'
        ]);

        $rol->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        if ($request->has('permisos')) {
            $rol->permisos()->sync($request->permisos);
        }

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'rol' => $rol->load('permisos')
        ], 200);
    }

    /**
     * Eliminar rol
     */
    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);

        if ($rol->usuarios()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el rol porque tiene usuarios asignados'
            ], 400);
        }

        $rol->delete();

        return response()->json([
            'message' => 'Rol eliminado exitosamente'
        ], 200);
    }

    /**
     * Asignar permisos a un rol
     */
    public function asignarPermisos(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'permisos' => 'required|array',
            'permisos.*' => 'exists:permiso,id_permiso'
        ]);

        $rol->permisos()->sync($request->permisos);

        return response()->json([
            'message' => 'Permisos asignados exitosamente',
            'rol' => $rol->load('permisos')
        ], 200);
    }

    /**
     * Listar todos los permisos disponibles
     */
    public function listarPermisos()
    {
        $permisos = Permiso::all();

        return response()->json([
            'permisos' => $permisos
        ], 200);
    }
}