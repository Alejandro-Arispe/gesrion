<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidarPermisoRol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permiso
     */
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $usuario = auth()->user();
        
        // Obtener permisos del rol del usuario
        $permisos = \DB::table('rol_permiso')
                       ->join('permiso', 'rol_permiso.id_permiso', '=', 'permiso.id_permiso')
                       ->where('rol_permiso.id_rol', $usuario->id_rol)
                       ->pluck('permiso.nombre')
                       ->toArray();

        if (!in_array($permiso, $permisos)) {
            return response()->json([
                'mensaje' => 'No tienes permiso para realizar esta acción'
            ], 403);
        }

        return $next($request);
    }
}
