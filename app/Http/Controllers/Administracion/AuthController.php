<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Administracion\Usuario;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin()
    {
        // Si ya está autenticado, redirigir al dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Procesar login (para WEB)
     */
    public function login(Request $request)
    {
        //borrar despues desde aqui
        // if ($credentials['username'] === 'admin') {
        //     // 1. Buscar el usuario 'admin' directamente en la BD
        //     $adminUser = Usuario::where('username', 'admin')->first();

        //     // 2. Si existe (y debe existir por el seeder), forzar login
        //     if ($adminUser) {
        //         Auth::login($adminUser);
        //         $request->session()->regenerate();
                
        //         // Retornar éxito (como si hubiera pasado el Auth::attempt)
        //         return redirect()->intended('dashboard')
        //             ->with('success', '¡Bienvenido ' . $adminUser->username . ' (ACCESO FORZADO)!');
        //     }
        // }
        //hasta aqyu 
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password
        ];

        // Intentar autenticación
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            return redirect()->intended('dashboard')
                ->with('success', '¡Bienvenido ' . Auth::user()->username . '!');
        }

        // Si falla, regresar con error
        return back()->withErrors([
            'username' => 'Las credenciales no coinciden con nuestros registros.'
        ])->withInput($request->only('username'));
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Sesión cerrada correctamente');
    }

    /**
     * Login API - Generar token de acceso (para el frontend React)
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $usuario = Usuario::where('username', $request->username)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'message' => 'Las credenciales son incorrectas.'
            ], 401);
        }

        // Verificar si el usuario está activo
        if (!$usuario->activo) {
            return response()->json([
                'message' => 'Usuario inactivo. Contacte al administrador.'
            ], 403);
        }

        // Crear token
        $token = $usuario->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $usuario->id_usuario,
                'username' => $usuario->username,
                'correo' => $usuario->correo,
                'rol' => $usuario->rol ? $usuario->rol->nombre : null,
                'permisos' => $usuario->rol ? $usuario->rol->permisos->pluck('nombre') : []
            ]
        ], 200);
    }

    /**
     * Logout API - Revocar token actual
     */
    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ], 200);
    }

    /**
     * Me API - Obtener información del usuario autenticado
     */
    public function me(Request $request)
    {
        $usuario = $request->user();

        return response()->json([
            'user' => [
                'id' => $usuario->id_usuario,
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

    /**
     * Cambiar contraseña
     */
    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password_nuevo' => 'required|string|min:6|confirmed'
        ]);

        $usuario = $request->user();

        if (!Hash::check($request->password_actual, $usuario->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta'
            ], 400);
        }

        $usuario->update([
            'password' => Hash::make($request->password_nuevo)
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente'
        ], 200);
    }
}