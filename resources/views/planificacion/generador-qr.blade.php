@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5">
                <i class="fas fa-qrcode"></i> Generador de Códigos QR
            </h1>
            <p class="text-muted">Genera y gestiona códigos QR para las aulas</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-success btn-lg" onclick="generarTodosQR()">
                <i class="fas fa-magic"></i> Generar Todos
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label"><strong>Piso</strong></label>
                    <select id="filtro-piso" class="form-select" onchange="aplicarFiltros()">
                        <option value="">-- Todos los pisos --</option>
                        <option value="1">Primer Piso</option>
                        <option value="2">Segundo Piso</option>
                        <option value="3">Tercer Piso</option>
                        <option value="4">Cuarto Piso</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><strong>Tipo de Aula</strong></label>
                    <select id="filtro-tipo" class="form-select" onchange="aplicarFiltros()">
                        <option value="">-- Todos los tipos --</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Teoría">Teoría</option>
                        <option value="Seminario">Seminario</option>
                        <option value="Práctico">Práctico</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><strong>Estado QR</strong></label>
                    <select id="filtro-qr" class="form-select" onchange="aplicarFiltros()">
                        <option value="">-- Todos --</option>
                        <option value="generado">Generados</option>
                        <option value="no-generado">No Generados</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Aulas -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-building"></i> Aulas Disponibles
                <span class="badge bg-light text-primary float-end" id="total-aulas">0</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabla-aulas">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">
                                <input type="checkbox" id="select-all" onchange="toggleSeleccionar()">
                            </th>
                            <th>Aula</th>
                            <th>Piso</th>
                            <th>Tipo</th>
                            <th>Capacidad</th>
                            <th>Ubicación GPS</th>
                            <th>Estado QR</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-aulas">
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección de Descargas -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-download"></i> Descargas
                    </h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-outline-info w-100 mb-2" onclick="descargarQRSeleccionados()">
                        <i class="fas fa-image"></i> Descargar QR Seleccionados (ZIP)
                    </button>
                    <button class="btn btn-outline-info w-100 mb-2" onclick="descargarQRTodos()">
                        <i class="fas fa-images"></i> Descargar Todos QR (ZIP)
                    </button>
                    <button class="btn btn-outline-info w-100" onclick="window.location.href='/planificacion/qr/descargar-pdf?formato=2x3'">
                        <i class="fas fa-file-pdf"></i> Descargar PDF Imprimible
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-sync-alt"></i> Regenerar
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle"></i> La regeneración invalidará los códigos anteriores
                    </p>
                    <button class="btn btn-outline-warning w-100 mb-2" onclick="regenerarSeleccionados()">
                        <i class="fas fa-sync-alt"></i> Regenerar Seleccionados
                    </button>
                    <button class="btn btn-outline-danger w-100" onclick="regenerarTodos()">
                        <i class="fas fa-exclamation-triangle"></i> Regenerar Todos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h3 class="display-6 text-primary" id="stat-total">0</h3>
                    <p class="text-muted mb-0">Aulas Totales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h3 class="display-6 text-success" id="stat-generados">0</h3>
                    <p class="text-muted mb-0">QR Generados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h3 class="display-6 text-warning" id="stat-pendientes">0</h3>
                    <p class="text-muted mb-0">Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h3 class="display-6 text-info" id="stat-porcentaje">0%</h3>
                    <p class="text-muted mb-0">Completado</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para vista previa de QR -->
<div class="modal fade" id="modal-qr" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-aula">Código QR - Aula</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="modal-qr-image" src="" alt="QR" class="img-fluid" style="max-width: 300px;">
                <p class="mt-3 text-muted">
                    <small id="modal-token"></small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="descargarQRIndividual()">
                    <i class="fas fa-download"></i> Descargar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let aulas = [];
let seleccionados = [];

// Cargar aulas al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarAulas();
});

function cargarAulas() {
    fetch('/planificacion/qr/listar', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        aulas = data.aulas;
        renderizarTabla();
        actualizarEstadisticas();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar las aulas');
    });
}

function renderizarTabla() {
    const tbody = document.getElementById('tbody-aulas');
    tbody.innerHTML = '';

    if (aulas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No hay aulas disponibles</td></tr>';
        return;
    }

    aulas.forEach(aula => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td class="text-center">
                <input type="checkbox" value="${aula.id_aula}" onchange="toggleSeleccionado(this)">
            </td>
            <td><strong>Aula ${aula.nro}</strong></td>
            <td>Piso ${aula.piso}</td>
            <td><span class="badge bg-secondary">${aula.tipo || 'N/A'}</span></td>
            <td>${aula.capacidad} personas</td>
            <td><small class="text-muted">${aula.ubicacion_gps || 'No registrada'}</small></td>
            <td>
                ${aula.qr_generado 
                    ? '<span class="badge bg-success"><i class="fas fa-check"></i> Generado</span>' 
                    : '<span class="badge bg-warning"><i class="fas fa-hourglass-half"></i> Pendiente</span>'}
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-info" onclick="verQR(${aula.id_aula})" title="Ver QR">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-primary" onclick="generarQR(${aula.id_aula})" title="Generar QR">
                    <i class="fas fa-qrcode"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="regenerarQR(${aula.id_aula})" title="Regenerar">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </td>
        `;
        tbody.appendChild(fila);
    });

    document.getElementById('total-aulas').textContent = aulas.length;
}

function toggleSeleccionado(checkbox) {
    if (checkbox.checked) {
        if (!seleccionados.includes(parseInt(checkbox.value))) {
            seleccionados.push(parseInt(checkbox.value));
        }
    } else {
        seleccionados = seleccionados.filter(id => id !== parseInt(checkbox.value));
    }
    actualizarCheckboxTodos();
}

function toggleSeleccionar() {
    const checkboxTodos = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('#tbody-aulas input[type="checkbox"]');
    
    seleccionados = [];
    checkboxes.forEach(checkbox => {
        checkbox.checked = checkboxTodos.checked;
        if (checkboxTodos.checked) {
            seleccionados.push(parseInt(checkbox.value));
        }
    });
}

function actualizarCheckboxTodos() {
    const checkboxTodos = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('#tbody-aulas input[type="checkbox"]');
    const todosSeleccionados = Array.from(checkboxes).every(cb => cb.checked);
    checkboxTodos.checked = todosSeleccionados;
}

function aplicarFiltros() {
    const piso = document.getElementById('filtro-piso').value;
    const tipo = document.getElementById('filtro-tipo').value;
    const qr = document.getElementById('filtro-qr').value;

    const tbody = document.getElementById('tbody-aulas');
    tbody.innerHTML = '';

    let filtrados = aulas.filter(aula => {
        if (piso && aula.piso != piso) return false;
        if (tipo && aula.tipo !== tipo) return false;
        if (qr === 'generado' && !aula.qr_generado) return false;
        if (qr === 'no-generado' && aula.qr_generado) return false;
        return true;
    });

    if (filtrados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No hay aulas que coincidan con los filtros</td></tr>';
        return;
    }

    filtrados.forEach(aula => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td class="text-center">
                <input type="checkbox" value="${aula.id_aula}" onchange="toggleSeleccionado(this)">
            </td>
            <td><strong>Aula ${aula.nro}</strong></td>
            <td>Piso ${aula.piso}</td>
            <td><span class="badge bg-secondary">${aula.tipo || 'N/A'}</span></td>
            <td>${aula.capacidad} personas</td>
            <td><small class="text-muted">${aula.ubicacion_gps || 'No registrada'}</small></td>
            <td>
                ${aula.qr_generado 
                    ? '<span class="badge bg-success"><i class="fas fa-check"></i> Generado</span>' 
                    : '<span class="badge bg-warning"><i class="fas fa-hourglass-half"></i> Pendiente</span>'}
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-info" onclick="verQR(${aula.id_aula})" title="Ver QR">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-primary" onclick="generarQR(${aula.id_aula})" title="Generar QR">
                    <i class="fas fa-qrcode"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="regenerarQR(${aula.id_aula})" title="Regenerar">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </td>
        `;
        tbody.appendChild(fila);
    });
}

function generarQR(id_aula) {
    if (!confirm('¿Generar QR para esta aula?')) return;

    const boton = event.target.closest('button');
    boton.disabled = true;

    fetch(`/planificacion/qr/${id_aula}/generar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('QR generado exitosamente', 'success');
            cargarAulas();
        } else {
            mostrarNotificacion('Error al generar QR', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al generar QR', 'error');
    })
    .finally(() => {
        boton.disabled = false;
    });
}

function generarTodosQR() {
    if (!confirm('¿Generar QR para TODAS las aulas? Esta acción puede tomar varios minutos.')) return;

    const boton = event.target;
    boton.disabled = true;
    boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando...';

    fetch('/planificacion/qr/generar-todos', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(`${data.generados} QR generados exitosamente`, 'success');
            cargarAulas();
        } else {
            mostrarNotificacion('Error al generar QRs', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al generar QRs', 'error');
    })
    .finally(() => {
        boton.disabled = false;
        boton.innerHTML = '<i class="fas fa-magic"></i> Generar Todos';
    });
}

function regenerarQR(id_aula) {
    if (!confirm('¿Regenerar QR para esta aula? El código anterior será invalidado.')) return;

    fetch(`/planificacion/qr/${id_aula}/regenerar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('QR regenerado exitosamente', 'success');
            cargarAulas();
        } else {
            mostrarNotificacion('Error al regenerar QR', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al regenerar QR', 'error');
    });
}

function verQR(id_aula) {
    const aula = aulas.find(a => a.id_aula === id_aula);
    if (!aula || !aula.qr_generado) {
        alert('QR no disponible');
        return;
    }

    document.getElementById('modal-aula').textContent = `Código QR - Aula ${aula.nro}`;
    document.getElementById('modal-token').textContent = `Token: ${aula.qr_token}`;
    document.getElementById('modal-qr-image').src = `/planificacion/qr/${id_aula}/mostrar`;
    
    new bootstrap.Modal(document.getElementById('modal-qr')).show();
}

function descargarQRIndividual() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-qr'));
    const aula = aulas.find(a => a.id_aula === parseInt(document.getElementById('modal-aula').textContent.split('Aula ')[1]));
    
    if (aula) {
        const link = document.createElement('a');
        link.href = `/planificacion/qr/${aula.id_aula}/mostrar`;
        link.download = `qr-aula-${aula.nro}.png`;
        link.click();
    }
}

function descargarQRSeleccionados() {
    if (seleccionados.length === 0) {
        alert('Selecciona al menos una aula');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/planificacion/qr/descargar-zip';
    form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
        <input type="hidden" name="aulas" value="${seleccionados.join(',')}">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function descargarQRTodos() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/planificacion/qr/descargar-zip-todos';
    form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function descargarPDF(formato) {
    // Esta función se usa desde el modal de opciones PDF
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/planificacion/qr/descargar-pdf';
    form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
        <input type="hidden" name="formato" value="${formato}">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function regenerarSeleccionados() {
    if (seleccionados.length === 0) {
        alert('Selecciona al menos una aula');
        return;
    }

    if (!confirm(`¿Regenerar QR para ${seleccionados.length} aulas seleccionadas? Los códigos anteriores serán invalidados.`)) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/planificacion/qr/regenerar-multiples';
    form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
        <input type="hidden" name="aulas" value="${seleccionados.join(',')}">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function regenerarTodos() {
    if (!confirm('¿Regenerar QR para TODAS las aulas? Esta acción no se puede deshacer.')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/planificacion/qr/regenerar-todos';
    form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function actualizarEstadisticas() {
    const total = aulas.length;
    const generados = aulas.filter(a => a.qr_generado).length;
    const pendientes = total - generados;
    const porcentaje = total > 0 ? Math.round((generados / total) * 100) : 0;

    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-generados').textContent = generados;
    document.getElementById('stat-pendientes').textContent = pendientes;
    document.getElementById('stat-porcentaje').textContent = `${porcentaje}%`;
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

.card {
    border: none;
}

.badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
}

.btn-sm {
    padding: 0.4rem 0.6rem;
    font-size: 0.85rem;
}

.display-6 {
    font-weight: bold;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}
</style>
@endsection
