<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\administracion\Bitacora;

class BitacoraController extends Controller
{
    public function index()
    {
        return response()->json(
            Bitacora::with('usuario:id_usuario,username,correo')
                ->orderBy('fecha_hora', 'desc')
                ->get()
        );
    }

    public function show($id)
    {
        return response()->json(Bitacora::with('usuario')->findOrFail($id));
    }
}
