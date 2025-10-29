@extends('layouts.app')
@section('page-title', 'Bitácora del Sistema')

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Registro de Actividades del Sistema</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>IP Origen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bitacoras as $bitacora)
                    <tr>
                        <td>
                            <small>
                                <i class="bi bi-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($bitacora->fecha_hora)->format('d/m/Y H:i:s') }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ $bitacora->usuario->username ?? 'Sistema' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $bitacora->accion }}</span>
                        </td>
                        <td>{{ $bitacora->descripcion }}</td>
                        <td><code>{{ $bitacora->ip_origen }}</code></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No hay registros en la bitácora</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $bitacoras->links() }}
        </div>
    </div>
</div>
@endsection