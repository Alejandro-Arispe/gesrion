<?php

namespace App\Http\Controllers\ConfiguracionAcademica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\Facultad;

class FacultadController extends Controller
{
    // Mostrar todas las facultades
    public function index() {
        return response()->json(Facultad::all());
    }

    // Crear nueva facultad
    public function store(Request $request) {
        $request->validate(['nombre' => 'required', 'sigla' => 'required|unique:facultad']);
        $facultad = Facultad::create($request->all());
        return response()->json($facultad, 201);
    }

    // Mostrar una facultad
    public function show($id) {
        return response()->json(Facultad::findOrFail($id));
    }

    // Actualizar facultad
    public function update(Request $request, $id) {
        $facultad = Facultad::findOrFail($id);
        $facultad->update($request->all());
        return response()->json($facultad);
    }

    // Eliminar facultad
    public function destroy($id) {
        Facultad::destroy($id);
        return response()->json(['message' => 'Eliminado']);
    }
}
