@extends('layouts.app')
@section('title','CU02 — Usuarios y roles')
@section('content')
<h1 class="text-xl font-semibold mb-3">Usuarios</h1>
<div class="grid md:grid-cols-2 gap-6">
  <div class="bg-white rounded shadow p-4">
    <div class="font-medium mb-2">Nuevo usuario</div>
    <form method="post" action="{{ route('cu.usuarios.store') }}" class="space-y-2">@csrf
      <input name="name" placeholder="Nombre" class="border rounded px-2 py-2 w-full" required>
      <input name="email" type="email" placeholder="Email" class="border rounded px-2 py-2 w-full" required>
      <input name="password" type="password" placeholder="Contraseña" class="border rounded px-2 py-2 w-full" required>
      <button class="px-3 py-2 bg-green-600 text-white rounded">Guardar</button>
    </form>
  </div>
  <div class="bg-white rounded shadow p-4 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead><tr class="border-b"><th class="py-2 pr-3">ID</th><th class="py-2 pr-3">Nombre</th><th class="py-2 pr-3">Email</th><th class="py-2">Acciones</th></tr></thead>
      <tbody>
        @foreach($usuarios as $u)
        <tr class="border-b">
          <td class="py-2 pr-3">{{ $u->id }}</td>
          <td class="py-2 pr-3">{{ $u->name }}</td>
          <td class="py-2 pr-3">{{ $u->email }}</td>
          <td class="py-2">
            <form method="post" action="{{ route('cu.usuarios.destroy',$u->id) }}" onsubmit="return confirm('Eliminar?')">
              @csrf @method('delete')
              <button class="px-2 py-1 bg-red-600 text-white rounded">Eliminar</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
