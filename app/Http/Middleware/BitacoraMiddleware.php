<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\administracion\Bitacora;
use Illuminate\Support\Facades\Auth;

class BitacoraMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check()) {
            Bitacora::create([
                'id_usuario' => Auth::id(),
                'accion' => $request->method() . ' ' . $request->path(),
                'descripcion' => json_encode($request->all()),
                'ip_origen' => $request->ip(),
            ]);
        }

        return $response;
    }
}
