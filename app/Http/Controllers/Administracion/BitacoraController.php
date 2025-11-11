<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BitacoraController extends Controller
{
    public function index()
    {
        // Obtener registros de bitácora directamente desde la BD
        $bitacoras = DB::table('bitacora')
            ->leftJoin('usuario', 'bitacora.id_usuario', '=', 'usuario.id_usuario')
            ->select('bitacora.*', 'usuario.username', 'usuario.correo')
            ->orderBy('bitacora.fecha_hora', 'desc')
            ->paginate(20);
            
        return view('administracion.bitacora.index', compact('bitacoras'));
    }

    public function show($id)
    {
        $bitacora = DB::table('bitacora')
            ->leftJoin('usuario', 'bitacora.id_usuario', '=', 'usuario.id_usuario')
            ->select('bitacora.*', 'usuario.username', 'usuario.correo')
            ->where('bitacora.id_bitacora', $id)
            ->first();
            
        if (!$bitacora) {
            return back()->with('error', 'Bitácora no encontrada');
        }
        
        return view('administracion.bitacora.show', compact('bitacora'));
    }
}
