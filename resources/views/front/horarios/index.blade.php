@extends('layouts.app')
@section('title','CU07 — Asignar horarios')
@section('content')
<form method="post" action="{{ route('cu.horarios.store') }}" class="bg-white rounded shadow p-4 mb-4">@csrf
  <div class="grid md:grid-cols-6 gap-3">
    <input name="id_grupo" placeholder="ID Grupo" class="border rounded px-2 py-2" required>
    <input name="id_aula" placeholder="ID Aula" class="border rounded px-2 py-2" required>
    <select name="dia" class="border rounded px-2 py-2" required>
      @foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] as $d)<option>{{ $d }}</option>@endforeach
    </select>
    <input type="time" name="hora_inicio" class="border rounded px-2 py-2" required>
    <input type="time" name="hora_fin" class="border rounded px-2 py-2" required>
    <button class="px-3 py-2 bg-green-600 text-white rounded">Asignar</button>
  </div>
</form>

<div class="grid md:grid-cols-6 gap-3">
@foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] as $d)
  <div class="bg-white rounded shadow p-3">
    <div class="font-medium mb-2">{{ $d }}</div>
    @foreach(($horarios[$d] ?? []) as $h)
      <div class="border rounded p-2 mb-2 text-sm flex items-center justify-between">
        <div>G{{ $h->id_grupo }} · A{{ $h->id_aula }} · {{ $h->hora_inicio }}–{{ $h->hora_fin }}</div>
        <form method="post" action="{{ route('cu.horarios.destroy',$h->id) }}" onsubmit="return confirm('Eliminar?')">
          @csrf @method('delete')
          <button class="px-2 py-0.5 bg-red-600 text-white rounded text-xs">X</button>
        </form>
      </div>
    @endforeach
  </div>
@endforeach
</div>
@endsection
