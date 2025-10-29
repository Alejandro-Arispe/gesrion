<?php

namespace App\Http\Controllers\ConfiguracionAcademica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\Grupo;

class GrupoController extends Controller
{
    public function index() {
        return response()->json(Grupo::with('materia')->get());
    }

    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required',
            'id_materia' => 'required|exists:materia,id_materia'
        ]);
        $grupo = Grupo::create($request->all());
        return response()->json($grupo, 201);
    }

    public function update(Request $request, $id) {
        $grupo = Grupo::findOrFail($id);
        $grupo->update($request->all());
        return response()->json($grupo);
    }

    public function destroy($id) {
        Grupo::destroy($id);
        return response()->json(['message' => 'Eliminado']);
    }
}
