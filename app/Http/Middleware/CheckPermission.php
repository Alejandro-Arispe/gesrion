<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Verificar si el usuario tiene el permiso requerido
     */
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        if (!$usuario->hasPermission($permiso)) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción',
                'permiso_requerido' => $permiso
            ], 403);
        }

        return $next($request);
    }
}