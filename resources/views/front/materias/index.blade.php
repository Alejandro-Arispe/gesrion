@extends('layouts.app')
@section('title','CU05 — Materias y grupos')
@section('content')
<div class="grid md:grid-cols-2 gap-6">
  <div class="bg-white rounded shadow p-4">
    <div class="font-medium mb-2">Nueva materia</div>
    <form method="post" action="{{ route('cu.materias.store') }}" class="space-y-2">@csrf
      <input name="codigo" placeholder="Código" class="border rounded px-2 py-2 w-full" required>
      <input name="nombre" placeholder="Nombre" class="border rounded px-2 py-2 w-full" required>
      <button class="px-3 py-2 bg-green-600 text-white rounded">Guardar</button>
    </form>
    <div class="font-medium mt-6 mb-2">Nuevo grupo</div>
    <form method="post" action="{{ route('cu.grupos.store') }}" class="space-y-2">@csrf
      <input name="id_materia" placeholder="ID Materia" class="border rounded px-2 py-2 w-full" required>
      <input name="turno" placeholder="Turno (M/T/N)" class="border rounded px-2 py-2 w-full" required>
      <input name="paralelo" placeholder="Paralelo" class="border rounded px-2 py-2 w-full" required>
      <button class="px-3 py-2 bg-green-600 text-white rounded">Guardar</button>
    </form>
  </div>
  <div class="bg-white rounded shadow p-4 overflow-x-auto">
    <div class="font-medium mb-2">Materias</div>
    <table class="min-w-full text-sm mb-6">
      <thead><tr class="border-b"><th class="py-2 pr-3">ID</th><th class="py-2 pr-3">Código</th><th class="py-2 pr-3">Nombre</th><th></th></tr></thead>
      <tbody>@foreach($materias as $m)
        <tr class="border-b">
          <td class="py-2 pr-3">{{ $m->id }}</td>
          <td class="py-2 pr-3">{{ $m->codigo }}</td>
          <td class="py-2 pr-3">{{ $m->nombre }}</td>
          <td class="py-2">
            <form method="post" action="{{ route('cu.materias.destroy',$m->id) }}" onsubmit="return confirm('Eliminar?')">
              @csrf @method('delete')
              <button class="px-2 py-1 bg-red-600 text-white rounded">Eliminar</button>
            </form>
          </td>
        </tr>@endforeach
      </tbody>
    </table>

    <div class="font-medium mb-2">Grupos</div>
    <table class="min-w-full text-sm">
      <thead><tr class="border-b"><th class="py-2 pr-3">ID</th><th class="py-2 pr-3">Materia</th><th class="py-2 pr-3">Turno</th><th class="py-2 pr-3">Paralelo</th><th></th></tr></thead>
      <tbody>@foreach($grupos as $g)
        <tr class="border-b">
          <td class="py-2 pr-3">{{ $g->id }}</td>
          <td class="py-2 pr-3">{{ $g->id_materia }}</td>
          <td class="py-2 pr-3">{{ $g->turno }}</td>
          <td class="py-2 pr-3">{{ $g->paralelo }}</td>
          <td class="py-2">
            <form method="post" action="{{ route('cu.grupos.destroy',$g->id) }}" onsubmit="return confirm('Eliminar?')">
              @csrf @method('delete')
              <button class="px-2 py-1 bg-red-600 text-white rounded">Eliminar</button>
            </form>
          </td>
        </tr>@endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
