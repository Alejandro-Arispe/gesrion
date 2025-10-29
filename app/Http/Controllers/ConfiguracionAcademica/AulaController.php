<?php

namespace App\Http\Controllers\ConfiguracionAcademica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\Aula;

class AulaController extends Controller
{
    public function index() {
        return response()->json(Aula::all());
    }

    public function store(Request $request) {
        $request->validate(['codigo' => 'required|unique:aula']);
        $aula = Aula::create($request->all());
        return response()->json($aula, 201);
    }

    public function update(Request $request, $id) {
        $aula = Aula::findOrFail($id);
        $aula->update($request->all());
        return response()->json($aula);
    }

    public function destroy($id) {
        Aula::destroy($id);
        return response()->json(['message' => 'Eliminado']);
    }
}
