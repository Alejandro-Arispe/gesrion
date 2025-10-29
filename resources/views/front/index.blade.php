@extends('layouts.app')
@section('title','CU01 — Autenticación y seguridad')
@section('content')
<div class="grid md:grid-cols-2 gap-6">
  <section class="bg-white rounded shadow p-5">
    <h2 class="font-semibold mb-3">Iniciar sesión</h2>
    <form method="post" action="{{ url('/login') }}" class="space-y-3">@csrf
      <input name="email" type="email" placeholder="Email" class="w-full border rounded px-3 py-2" required>
      <input name="password" type="password" placeholder="Contraseña" class="w-full border rounded px-3 py-2" required>
      <label class="text-sm inline-flex items-center gap-2"><input type="checkbox" name="remember">Recordarme</label>
      <button class="px-4 py-2 bg-blue-600 text-white rounded">Entrar</button>
    </form>
  </section>
  <section class="bg-white rounded shadow p-5">
    <h2 class="font-semibold mb-3">Registro</h2>
    <form method="post" action="{{ url('/register') }}" class="space-y-3">@csrf
      <input name="name" placeholder="Nombre" class="w-full border rounded px-3 py-2" required>
      <input name="email" type="email" placeholder="Email" class="w-full border rounded px-3 py-2" required>
      <input name="password" type="password" placeholder="Contraseña" class="w-full border rounded px-3 py-2" required>
      <input name="password_confirmation" type="password" placeholder="Confirmar" class="w-full border rounded px-3 py-2" required>
      <button class="px-4 py-2 bg-green-600 text-white rounded">Crear cuenta</button>
    </form>
  </section>
</div>
@endsection
