@extends('layouts.app')
@section('title','CU04 — Docentes')
@section('content')

<div class="flex items-center justify-between mb-3">
  <h1 class="text-xl font-semibold">CU04 — Gestionar docentes</h1>
  @if(session('ok')) <div class="text-green-700 text-sm">{{ session('ok') }}</div> @endif
</div>
@if($errors->any())
  <div class="mb-3 p-3 bg-red-50 text-red-700 rounded text-sm">
    <ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="get" class="mb-3">
  <input name="q" value="{{ $q ?? '' }}" placeholder="Buscar CI o nombre"
         class="border rounded px-3 py-2 w-64">
  <button class="ml-2 px-3 py-2 bg-blue-600 text-white rounded">Buscar</button>
</form>

<div class="grid md:grid-cols-2 gap-6">
  {{-- Crear --}}
  <div class="bg-white rounded shadow p-4">
    <div class="font-medium mb-2">Nuevo docente</div>
    <form method="post" action="{{ route('cu.docentes.store') }}" class="space-y-2">
      @csrf
      <div class="grid grid-cols-2 gap-2">
        <input name="ci" placeholder="CI" class="border rounded px-2 py-2" required>
        <input name="nombre" placeholder="Nombre completo" class="border rounded px-2 py-2" required>
        <select name="sexo" class="border rounded px-2 py-2" required>
          <option value="">Sexo</option><option value="M">M</option><option value="F">F</option>
        </select>
        <input name="telefono" placeholder="Teléfono" class="border rounded px-2 py-2">
        <input name="correo" placeholder="Correo" class="border rounded px-2 py-2">
        <input name="id_facultad" placeholder="ID Facultad" class="border rounded px-2 py-2">
      </div>
      <button class="px-3 py-2 bg-green-600 text-white rounded">Guardar</button>
    </form>
  </div>

  {{-- Listado + acciones --}}
  <div class="bg-white rounded shadow p-4 overflow-x-auto">
    <div class="font-medium mb-2">Listado</div>
    <table class="min-w-full text-sm">
      <thead>
        <tr class="text-left border-b">
          <th class="py-2 pr-3">ID</th>
          <th class="py-2 pr-3">CI</th>
          <th class="py-2 pr-3">Nombre</th>
          <th class="py-2 pr-3">Correo</th>
          <th class="py-2 pr-3">Teléfono</th>
          <th class="py-2 pr-3">Estado</th>
          <th class="py-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
      @foreach($docentes as $d)
        <tr class="border-b align-top">
          <td class="py-2 pr-3">{{ $d->id_docente }}</td>
          <td class="py-2 pr-3">{{ $d->ci }}</td>
          <td class="py-2 pr-3">{{ $d->nombre }}</td>
          <td class="py-2 pr-3">{{ $d->correo }}</td>
          <td class="py-2 pr-3">{{ $d->telefono }}</td>
          <td class="py-2 pr-3">{{ $d->estado ? 'Activo':'Inactivo' }}</td>
          <td class="py-2">
            <details>
              <summary class="cursor-pointer text-blue-600">Editar</summary>
              <form method="post" action="{{ route('cu.docentes.update',$d->id_docente) }}" class="mt-2 flex flex-wrap gap-2">
                @csrf @method('put')
                <input name="nombre" value="{{ $d->nombre }}" class="border rounded px-2 py-1">
                <input name="correo" value="{{ $d->correo }}" class="border rounded px-2 py-1">
                <input name="telefono" value="{{ $d->telefono }}" class="border rounded px-2 py-1">
                <select name="estado" class="border rounded px-2 py-1">
                  <option value="1" @selected($d->estado)>Activo</option>
                  <option value="0" @selected(!$d->estado)>Inactivo</option>
                </select>
                <button class="px-2 py-1 bg-blue-600 text-white rounded">Actualizar</button>
              </form>
              <form method="post" action="{{ route('cu.docentes.destroy',$d->id_docente) }}" class="mt-2"
                    onsubmit="return confirm('Eliminar?')">
                @csrf @method('delete')
                <button class="px-2 py-1 bg-red-600 text-white rounded">Eliminar</button>
              </form>
            </details>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    @if(method_exists($docentes,'links'))
      <div class="mt-3">{{ $docentes->links() }}</div>
    @endif
  </div>
</div>
@endsection
