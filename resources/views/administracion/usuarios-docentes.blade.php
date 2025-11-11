@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5">
                <i class="fas fa-user-tie"></i> Gestión de Usuarios - Docentes
            </h1>
            <p class="text-muted">Crear y gestionar usuarios para docentes de la institución</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-success btn-lg" onclick="generarUsuariosMasivo()">
                <i class="fas fa-users-cog"></i> Generar Usuarios Faltantes
            </button>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h3 class="display-6 text-primary">{{ $estadisticas['total_docentes'] }}</h3>
                    <p class="text-muted mb-0">Docentes Totales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h3 class="display-6 text-success">{{ $estadisticas['usuarios_creados'] }}</h3>
                    <p class="text-muted mb-0">Usuarios Creados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h3 class="display-6 text-warning">{{ $estadisticas['usuarios_pendientes'] }}</h3>
                    <p class="text-muted mb-0">Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h3 class="display-6 text-info">
                        {{ $estadisticas['total_docentes'] > 0 ? round(($estadisticas['usuarios_creados'] / $estadisticas['total_docentes']) * 100) : 0 }}%
                    </h3>
                    <p class="text-muted mb-0">Completado</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="row mb-3">
        <div class="col-md-12">
            <button class="btn btn-info" onclick="descargarCredenciales()">
                <i class="fas fa-file-pdf"></i> Descargar Credenciales en PDF
            </button>
            <button class="btn btn-secondary" onclick="recargarPagina()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Tabla de docentes y usuarios -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-table"></i> Docentes y Sus Usuarios
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">Docente</th>
                            <th style="width: 20%;">Usuario</th>
                            <th style="width: 25%;">Correo</th>
                            <th style="width: 15%;">Estado</th>
                            <th style="width: 10%;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($docentes as $docente)
                            <tr>
                                <td>
                                    <strong>{{ $docente->nombre }}</strong><br>
                                    <small class="text-muted">CI: {{ $docente->ci }}</small>
                                </td>
                                <td>
                                    @if($docente->usuario)
                                        <span class="badge bg-success">{{ $docente->usuario->username }}</span>
                                    @else
                                        <span class="badge bg-secondary">Sin usuario</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $docente->correo ?? 'No registrado' }}
                                </td>
                                <td>
                                    @if($docente->usuario)
                                        @if($docente->usuario->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    @else
                                        <span class="badge bg-warning">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($docente->usuario)
                                        <button class="btn btn-sm btn-warning" 
                                                onclick="regenerarPassword({{ $docente->id_docente }})"
                                                title="Regenerar contraseña">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="desactivarUsuario({{ $docente->id_docente }})"
                                                title="Desactivar usuario">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @else
                                        <span class="text-muted small">---</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No hay docentes disponibles
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    @if($docentes->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $docentes->links() }}
        </div>
    @endif
</div>

<script>
function generarUsuariosMasivo() {
    if (!confirm('¿Generar usuarios para todos los docentes sin usuario? Esto puede tomar unos momentos.')) {
        return;
    }

    const boton = event.target.closest('button');
    boton.disabled = true;
    boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando...';

    fetch('/administracion/usuarios-docentes/generar-masivo', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            mostrarNotificacion(`${data.resumen.creados} usuarios creados`, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            mostrarNotificacion(data.mensaje, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al generar usuarios', 'error');
    })
    .finally(() => {
        boton.disabled = false;
        boton.innerHTML = '<i class="fas fa-users-cog"></i> Generar Usuarios Faltantes';
    });
}

function descargarCredenciales() {
    window.location.href = '/administracion/usuarios-docentes/descargar-credenciales-pdf';
}

function regenerarPassword(idDocente) {
    if (!confirm('¿Regenerar contraseña para este docente? Se generará una nueva: Nombre+123')) {
        return;
    }

    fetch(`/administracion/usuarios-docentes/${idDocente}/regenerar-password`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            mostrarNotificacion(data.mensaje + ' - Nueva contraseña: ' + data.password_plano, 'success');
        } else {
            mostrarNotificacion(data.mensaje, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al regenerar contraseña', 'error');
    });
}

function desactivarUsuario(idDocente) {
    if (!confirm('¿Desactivar usuario de este docente? No podrá ingresar a la plataforma.')) {
        return;
    }

    fetch(`/administracion/usuarios-docentes/${idDocente}/desactivar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            mostrarNotificacion(data.mensaje, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            mostrarNotificacion(data.mensaje, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al desactivar usuario', 'error');
    });
}

function recargarPagina() {
    window.location.reload();
}

function mostrarNotificacion(mensaje, tipo) {
    const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
    const alerta = document.createElement('div');
    alerta.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alerta.style.top = '20px';
    alerta.style.right = '20px';
    alerta.style.zIndex = '9999';
    alerta.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), 4000);
}
</script>

<style>
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
}

.btn-sm {
    padding: 0.4rem 0.6rem;
    font-size: 0.85rem;
}
</style>
@endsection
