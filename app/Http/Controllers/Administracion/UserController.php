<?php

namespace App\Http\Controllers\administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\administracion\Usuario;
use App\Models\administracion\Rol;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listar todos los usuarios
     */
    public function index(Request $request)
    {
        // Si la petición espera JSON, devolver formato API
        if ($request->wantsJson()) {
            $usuarios = Usuario::with('rol.permisos')->get();

            return response()->json([
                'usuarios' => $usuarios->map(function($usuario) {
                    return [
                        'id_usuario' => $usuario->id_usuario,
                        'username' => $usuario->username,
                        'correo' => $usuario->correo,
                        'activo' => $usuario->activo,
                        'rol' => $usuario->rol ? [
                            'id' => $usuario->rol->id_rol,
                            'nombre' => $usuario->rol->nombre
                        ] : null
                    ];
                })
            ], 200);
        }

        // Flujo para la interfaz web: paginado y opción de búsqueda
        $query = Usuario::with('rol');

        if ($request->has('search') && $request->search !== null) {
            $q = $request->search;
            $query->where(function($sub) use ($q) {
                $sub->where('username', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%");
            });
        }

        $usuarios = $query->orderBy('id_usuario', 'desc')->paginate(10);

        return view('administracion.usuarios.index', compact('usuarios'));
    }

    /**
     * Crear nuevo usuario
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:usuario,username',
            'password' => 'required|string|min:6',
            'correo' => 'required|email|unique:usuario,correo',
            'id_rol' => 'required|exists:rol,id_rol',
            'activo' => 'boolean'
        ]);
        $usuario = Usuario::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'correo' => $request->correo,
            'id_rol' => $request->id_rol,
            'activo' => $request->activo ?? true
        ]);

        // Responder según el tipo de petición
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'usuario' => $usuario->load('rol')
            ], 201);
        }

        return redirect()->route('administracion.usuarios.index')
                         ->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Mostrar un usuario específico
     */
    public function show($id)
    {
        $usuario = Usuario::with('rol.permisos')->findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json([
                'usuario' => [
                    'id_usuario' => $usuario->id_usuario,
                    'username' => $usuario->username,
                    'correo' => $usuario->correo,
                    'activo' => $usuario->activo,
                    'rol' => $usuario->rol ? [
                        'id' => $usuario->rol->id_rol,
                        'nombre' => $usuario->rol->nombre,
                        'descripcion' => $usuario->rol->descripcion
                    ] : null,
                    'permisos' => $usuario->rol ? $usuario->rol->permisos->pluck('nombre') : []
                ]
            ], 200);
        }

        // Flujo web: redirigir a edición (no existe vista show, usamos edit)
        return redirect()->route('administracion.usuarios.edit', $usuario->id_usuario);
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('usuario')->ignore($usuario->id_usuario, 'id_usuario')],
            'correo' => ['required', 'email', Rule::unique('usuario')->ignore($usuario->id_usuario, 'id_usuario')],
            'id_rol' => 'nullable|exists:rol,id_rol',
            'activo' => 'boolean',
            'password' => 'nullable|string|min:6'
        ]);

        $data = [
            'username' => $request->username,
            'correo' => $request->correo,
            'activo' => $request->activo ?? $usuario->activo
        ];

        if ($request->filled('id_rol')) {
            $data['id_rol'] = $request->id_rol;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'usuario' => $usuario->load('rol')
            ], 200);
        }

        return redirect()->route('administracion.usuarios.index')
                         ->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Eliminar usuario
     */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        
        // No permitir eliminar al propio usuario autenticado
        if (auth()->id() == $id) {
            if (request()->wantsJson()) {
                return response()->json([
                    'message' => 'No puedes eliminar tu propio usuario'
                ], 400);
            }

            return redirect()->back()->with('error', 'No puedes eliminar tu propio usuario');
        }
        $usuario->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Usuario eliminado exitosamente'
            ], 200);
        }

        return redirect()->route('administracion.usuarios.index')
                         ->with('success', 'Usuario eliminado exitosamente');
    }

    /**
     * Activar/Desactivar usuario
     */
    public function toggleEstado($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update(['activo' => !$usuario->activo]);

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Estado actualizado',
                'usuario' => $usuario
            ], 200);
        }

        return redirect()->route('administracion.usuarios.index')
                         ->with('success', 'Estado actualizado');
    }

    /**
     * Formulario de creación (WEB)
     */
    public function create()
    {
        $roles = Rol::all();
        return view('administracion.usuarios.create', compact('roles'));
    }

    /**
     * Formulario de edición (WEB)
     */
    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::all();
        return view('administracion.usuarios.edit', compact('usuario', 'roles'));
    }
}