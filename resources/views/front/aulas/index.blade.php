@extends('layouts.app')
@section('title','CU06 — Aulas')
@section('content')
<div class="grid md:grid-cols-2 gap-6">
  <div class="bg-white rounded shadow p-4">
    <div class="font-medium mb-2">Nueva aula</div>
    <form method="post" action="{{ route('cu.aulas.store') }}" class="space-y-2">@csrf
      <input name="codigo" placeholder="Código/Nombre" class="border rounded px-2 py-2 w-full" required>
      <input name="capacidad" type="number" placeholder="Capacidad" class="border rounded px-2 py-2 w-full" required>
      <button class="px-3 py-2 bg-green-600 text-white rounded">Guardar</button>
    </form>
  </div>
  <div class="bg-white rounded shadow p-4 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead><tr class="border-b"><th class="py-2 pr-3">ID</th><th class="py-2 pr-3">Código</th><th class="py-2 pr-3">Capacidad</th><th></th></tr></thead>
    <tbody>@foreach($aulas as $a)
      <tr class="border-b">
        <td class="py-2 pr-3">{{ $a->id }}</td>
        <td class="py-2 pr-3">{{ $a->codigo }}</td>
        <td class="py-2 pr-3">{{ $a->capacidad }}</td>
        <td class="py-2">
          <form method="post" action="{{ route('cu.aulas.destroy',$a->id) }}" onsubmit="return confirm('Eliminar?')">
            @csrf @method('delete')
            <button class="px-2 py-1 bg-red-600 text-white rounded">Eliminar</button>
          </form>
        </td>
      </tr>@endforeach
    </tbody></table>
  </div>
</div>
@endsection
