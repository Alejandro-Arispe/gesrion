@extends('layouts.app')
@section('page-title', 'Generador de Distribución de Horarios')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="display-5">
                <i class="fas fa-calendar-check"></i> Generador Automático de Distribución de Horarios
            </h1>
            <p class="text-muted">Distribuye automáticamente las clases en múltiples días según la carga horaria</p>
        </div>
    </div>

    <div class="row">
        <!-- Panel de Selección -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Seleccionar Grupo</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Grupos sin horario</label>
                        <select id="selectGrupo" class="form-select" onchange="cargarDetallesGrupo()">
                            <option value="">-- Seleccionar grupo --</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id_grupo }}" data-carga="{{ $grupo->materia->carga_horaria }}">
                                    {{ $grupo->nombre }} - {{ $grupo->materia->nombre }} 
                                    ({{ $grupo->materia->carga_horaria }}h) - {{ $grupo->docente->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="detallesGrupo" style="display: none;">
                        <div class="alert alert-info">
                            <strong id="nombreGrupo"></strong><br>
                            <small id="docenteGrupo"></small><br>
                            <span class="badge bg-primary" id="cargaGrupo"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Patrones -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Patrones Predeterminados</h5>
                </div>
                <div class="card-body">
                    <div id="patronesContainer"></div>
                </div>
            </div>
        </div>

        <!-- Panel de Configuración -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Configuración de Distribución</h5>
                </div>
                <div class="card-body">
                    <form id="formDistribucion">
                        <!-- Opción 1: Patrón Predeterminado -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <span class="badge bg-primary">Opción 1</span> Usar Patrón Predeterminado
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Seleccionar Patrón</label>
                                    <select id="selectPatron" class="form-select" onchange="actualizarPreview()">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach($patrones as $patron)
                                            <option value="{{ $patron['clave'] }}">
                                                {{ $patron['descripcion'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Hora de Inicio</label>
                                    <input type="time" id="horaInicio" class="form-control" value="08:00" onchange="actualizarPreview()">
                                </div>
                            </div>

                            <div id="previewPatron" class="mt-3" style="display: none;">
                                <small class="text-muted">Preview:</small>
                                <div id="textoPreview" class="alert alert-light mt-2"></div>
                            </div>
                        </div>

                        <hr>

                        <!-- Opción 2: Personalizado -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <span class="badge bg-warning">Opción 2</span> Configuración Personalizada
                            </h6>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Seleccionar días</label>
                                <div class="dias-checkbox">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia" value="Lunes" id="dia_lunes">
                                        <label class="form-check-label" for="dia_lunes">Lunes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia" value="Martes" id="dia_martes">
                                        <label class="form-check-label" for="dia_martes">Martes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia" value="Miércoles" id="dia_miercoles">
                                        <label class="form-check-label" for="dia_miercoles">Miércoles</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia" value="Jueves" id="dia_jueves">
                                        <label class="form-check-label" for="dia_jueves">Jueves</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia" value="Viernes" id="dia_viernes">
                                        <label class="form-check-label" for="dia_viernes">Viernes</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Duración por día (horas)</label>
                                    <input type="number" id="duracionPersonalizada" class="form-control" min="0.5" step="0.25" onchange="actualizarPreview()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Hora de Inicio</label>
                                    <input type="time" id="horaInicioPersonalizado" class="form-control" value="08:00">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Botones de Acción -->
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-lg" onclick="generarDistribucion()">
                                <i class="fas fa-check-circle"></i> Generar Distribución
                            </button>
                            <button type="reset" class="btn btn-secondary btn-lg">
                                <i class="fas fa-redo"></i> Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resultado -->
            <div id="resultadoContainer" style="display: none;" class="mt-4">
                <div id="resultadoContenido"></div>
            </div>
        </div>
    </div>
</div>

<style>
.dias-checkbox {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
}

.form-check {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-check-input:checked + .form-check-label {
    font-weight: bold;
    color: #0d6efd;
}

.patron-btn {
    display: block;
    width: 100%;
    text-align: left;
    padding: 15px;
    margin-bottom: 10px;
    border: 2px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    transition: all 0.3s;
}

.patron-btn:hover {
    border-color: #0d6efd;
    background: #f8f9fa;
}

.patron-btn.active {
    border-color: #0d6efd;
    background: #e7f1ff;
    font-weight: bold;
}

.patron-btn strong {
    display: block;
    margin-bottom: 5px;
}

.patron-btn small {
    display: block;
    color: #666;
}
</style>

<script>
// Cargar patrones
document.addEventListener('DOMContentLoaded', function() {
    fetch('/planificacion/distribucion/patrones', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('patronesContainer');
        data.patrones.forEach(patron => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'patron-btn';
            btn.onclick = () => seleccionarPatron(patron.clave);
            btn.innerHTML = `
                <strong>${patron.descripcion}</strong>
                <small>${patron.dias}</small>
                <small style="color: #28a745; font-weight: bold;">
                    ${patron.duracion_horas}h x ${(patron.descripcion.match(/\d+/g) || []).length} días = ${(patron.duracion_horas * (patron.descripcion.match(/,/g) || []).length + patron.duracion_horas).toFixed(1)}h
                </small>
            `;
            container.appendChild(btn);
        });
    });
});

function cargarDetallesGrupo() {
    const select = document.getElementById('selectGrupo');
    if (!select.value) {
        document.getElementById('detallesGrupo').style.display = 'none';
        return;
    }

    const option = select.options[select.selectedIndex];
    document.getElementById('nombreGrupo').textContent = option.text.split(' - ')[0];
    document.getElementById('cargaGrupo').textContent = option.dataset.carga + ' horas';
    document.getElementById('detallesGrupo').style.display = 'block';
}

function seleccionarPatron(clave) {
    document.getElementById('selectPatron').value = clave;
    document.querySelectorAll('.patron-btn').forEach(btn => btn.classList.remove('active'));
    event.target.closest('.patron-btn').classList.add('active');
    actualizarPreview();
}

function actualizarPreview() {
    const patron = document.getElementById('selectPatron').value;
    const preview = document.getElementById('previewPatron');
    const textoPreview = document.getElementById('textoPreview');

    if (!patron) {
        preview.style.display = 'none';
        return;
    }

    preview.style.display = 'block';
    textoPreview.innerHTML = '<i class="fas fa-check-circle text-success"></i> Configuración válida y lista para generar';
}

function generarDistribucion() {
    const idGrupo = document.getElementById('selectGrupo').value;
    const patron = document.getElementById('selectPatron').value;
    const horaInicio = document.getElementById('horaInicio').value;

    if (!idGrupo) {
        alert('Selecciona un grupo');
        return;
    }

    if (!patron && !document.querySelector('input[name="dia"]:checked')) {
        alert('Selecciona un patrón o personaliza los días');
        return;
    }

    const boton = event.target;
    boton.disabled = true;
    boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando...';

    const datos = {
        id_grupo: idGrupo,
        patron: patron || 'PERSONALIZADO',
        hora_inicio: horaInicio,
        dias_personalizados: patron ? [] : Array.from(document.querySelectorAll('input[name="dia"]:checked')).map(el => el.value),
        duracion_personalizada: patron ? null : parseFloat(document.getElementById('duracionPersonalizada').value)
    };

    fetch('/planificacion/distribucion/generar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('resultadoContainer');
        const contenido = document.getElementById('resultadoContenido');

        if (data.exito) {
            contenido.innerHTML = `
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle"></i> ¡Éxito!</h5>
                    <p>${data.mensaje}</p>
                    <hr>
                    <h6>Distribución generada:</h6>
                    <ul>
                        ${data.horarios.map(h => `<li>${h.dia}: ${h.hora_inicio} - ${h.hora_fin}</li>`).join('')}
                    </ul>
                    <hr>
                    <small class="text-muted">
                        Patrón: ${data.distribucion.patron} | 
                        Carga total: ${data.distribucion.carga_total_horas}h
                    </small>
                </div>
            `;
        } else {
            contenido.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-times-circle"></i> Error</h5>
                    <p>${data.mensaje}</p>
                </div>
            `;
        }

        container.style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al generar distribución');
    })
    .finally(() => {
        boton.disabled = false;
        boton.innerHTML = '<i class="fas fa-check-circle"></i> Generar Distribución';
    });
}
</script>
@endsection
