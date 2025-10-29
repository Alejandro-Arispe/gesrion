<?php

namespace App\Http\Controllers\ConfiguracionAcademica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\Materia;

class MateriaController extends Controller
{
    public function index() {
        return response()->json(Materia::with('gestion')->get());
    }

    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required',
            'codigo' => 'required|unique:materia',
            'id_gestion' => 'required|exists:gestion_academica,id_gestion'
        ]);
        $materia = Materia::create($request->all());
        return response()->json($materia, 201);
    }

    public function update(Request $request, $id) {
        $materia = Materia::findOrFail($id);
        $materia->update($request->all());
        return response()->json($materia);
    }

    public function destroy($id) {
        Materia::destroy($id);
        return response()->json(['message' => 'Eliminado']);
    }
}
