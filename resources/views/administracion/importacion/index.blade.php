@extends('layouts.app')
@section('page-title', 'Importación de Datos')

@section('content')
<div class="row">
    <!-- Encabezado -->
    <div class="col-12 mb-4">
        <div class="card bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="bi bi-upload me-2"></i>Importación Masiva de Datos
                        </h4>
                        <p class="card-text mt-2 mb-0">Importa docentes, materias, usuarios y grupos desde archivos CSV o XLSX</p>
                    </div>
                    <i class="bi bi-cloud-upload display-6 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Importación -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light border-0">
                <h5 class="mb-0"><i class="bi bi-file-earmark-arrow-up me-2"></i>Cargar Archivo</h5>
            </div>
            <div class="card-body">
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Seleccionar Tipo -->
                    <div class="mb-3">
                        <label class="form-label fw-medium">Tipo de Importación *</label>
                        <select id="tipo" name="tipo" class="form-select" required>
                            <option value="">-- Seleccione el tipo --</option>
                            <option value="docentes">📚 Docentes</option>
                            <option value="materias">📖 Materias</option>
                            <option value="usuarios">👤 Usuarios</option>
                            <option value="grupos">👥 Grupos</option>
                        </select>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            <a href="#" onclick="descargarPlantilla(this.dataset.tipo)" class="plantilla-link">
                                Descargar plantilla
                            </a>
                        </small>
                    </div>

                    <!-- Cargar Archivo -->
                    <div class="mb-3">
                        <label class="form-label fw-medium">Archivo (CSV o XLSX) *</label>
                        <div class="drop-zone border-2 border-dashed rounded p-4 text-center" id="dropZone">
                            <i class="bi bi-cloud-arrow-up fs-1 text-primary mb-2 d-block"></i>
                            <p class="mb-2">Arrastra tu archivo aquí o haz click para seleccionar</p>
                            <small class="text-muted">Formatos soportados: CSV, XLSX (máx. 5MB)</small>
                            <input type="file" id="file" name="file" class="form-control d-none" accept=".csv,.xlsx,.xls" required>
                        </div>
                        <div id="fileInfo" class="mt-2" style="display: none;">
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="bi bi-check-circle me-2"></i>
                                    Archivo seleccionado: <strong id="fileName"></strong>
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="resetFile()">Limpiar</button>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="btnPreview" onclick="previewArchivo()">
                            <i class="bi bi-eye me-1"></i>Ver Previa
                        </button>
                        <button type="submit" class="btn btn-success" id="btnImport" style="display: none;">
                            <i class="bi bi-check-circle me-1"></i>Confirmar Importación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Información de Ayuda -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-start border-4 border-info mb-3">
            <div class="card-header bg-light border-0">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Formatos Aceptados</h6>
            </div>
            <div class="card-body small">
                <ul class="mb-0">
                    <li><strong>CSV:</strong> Valores separados por comas</li>
                    <li><strong>XLSX:</strong> Excel 2007 en adelante</li>
                    <li>Máximo 5 MB por archivo</li>
                    <li>Primera fila = encabezados</li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm border-start border-4 border-warning">
            <div class="card-header bg-light border-0">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Recomendaciones</h6>
            </div>
            <div class="card-body small">
                <ul class="mb-0">
                    <li>Revisar la previa antes de importar</li>
                    <li>Los errores no detienen la importación</li>
                    <li>Se validarán duplicados automáticamente</li>
                    <li>Las transacciones son atómicas</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Previa -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>Vista Previa de Importación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent" class="table-responsive">
                    <!-- Contenido de previa se cargará aquí -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarImportacion()">
                    <i class="bi bi-check-circle me-1"></i>Confirmar Importación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Resultados -->
<div class="modal fade" id="resultadosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>Resultados de Importación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="resultadosContent">
                    <!-- Resultados se cargarán aquí -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="resetearFormulario()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Nueva Importación
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('file');
const fileInfo = document.getElementById('fileInfo');
const tipoSelect = document.getElementById('tipo');

// Drag and drop
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('bg-light');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('bg-light');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('bg-light');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        mostrarArchivoSeleccionado(files[0]);
    }
});

dropZone.addEventListener('click', () => {
    fileInput.click();
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        mostrarArchivoSeleccionado(e.target.files[0]);
    }
});

function mostrarArchivoSeleccionado(file) {
    document.getElementById('fileName').textContent = file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
    fileInfo.style.display = 'block';
}

function resetFile() {
    fileInput.value = '';
    fileInfo.style.display = 'none';
}

function descargarPlantilla(tipo) {
    event.preventDefault();
    tipo = tipoSelect.value;
    if (!tipo) {
        alert('Por favor selecciona un tipo de importación');
        return;
    }
    window.location.href = '{{ route("administracion.importacion.descargar-plantilla") }}?tipo=' + tipo;
}

tipoSelect.addEventListener('change', function() {
    const link = document.querySelector('.plantilla-link');
    if (this.value) {
        link.onclick = function(e) {
            e.preventDefault();
            descargarPlantilla(tipoSelect.value);
        };
        link.classList.remove('text-muted');
    } else {
        link.classList.add('text-muted');
    }
});

function previewArchivo() {
    const tipo = tipoSelect.value;
    const file = fileInput.files[0];

    if (!tipo || !file) {
        alert('Por favor selecciona tipo de importación y archivo');
        return;
    }

    const formData = new FormData();
    formData.append('tipo', tipo);
    formData.append('file', file);

    const btn = document.getElementById('btnPreview');
    btn.disabled = true;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Procesando...';

    fetch('{{ route("administracion.importacion.preview") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-eye me-1"></i>Ver Previa';

        if (data.success) {
            mostrarPrevia(data);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-eye me-1"></i>Ver Previa';
        alert('Error al procesar archivo: ' + error.message);
    });
}

function mostrarPrevia(data) {
    const columnas = data.columnas || Object.keys(data.preview[0] || {});
    let html = `
        <div class="alert alert-info">
            <strong>Total de registros:</strong> ${data.total}
            <br>
            <strong>Vista previa (máximo 10):</strong>
        </div>
        <table class="table table-hover table-sm">
            <thead class="table-light">
                <tr>
    `;

    columnas.forEach(col => {
        html += `<th>${col}</th>`;
    });

    html += `
                </tr>
            </thead>
            <tbody>
    `;

    data.preview.forEach(fila => {
        html += '<tr>';
        columnas.forEach(col => {
            html += `<td>${fila[col] || '-'}</td>`;
        });
        html += '</tr>';
    });

    html += `
            </tbody>
        </table>
    `;

    document.getElementById('previewContent').innerHTML = html;
    document.getElementById('btnImport').style.display = 'inline-block';
    
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

function confirmarImportacion() {
    const tipo = tipoSelect.value;
    const file = fileInput.files[0];

    if (!tipo || !file) {
        alert('Error: Datos incompletos');
        return;
    }

    const formData = new FormData();
    formData.append('tipo', tipo);
    formData.append('file', file);

    document.getElementById('btnImport').disabled = true;
    document.getElementById('btnImport').innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Importando...';

    fetch('{{ route("administracion.importacion.import") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('btnImport').disabled = false;
        document.getElementById('btnImport').innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirmar Importación';

        mostrarResultados(data);
    })
    .catch(error => {
        document.getElementById('btnImport').disabled = false;
        document.getElementById('btnImport').innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirmar Importación';
        alert('Error en importación: ' + error.message);
    });
}

function mostrarResultados(data) {
    const icon = data.success ? 'check-circle text-success' : 'exclamation-triangle text-danger';
    let html = `
        <div class="alert alert-${data.success ? 'success' : 'danger'}">
            <i class="bi bi-${icon} me-2"></i>
            <strong>${data.message}</strong>
        </div>
    `;

    if (data.creados || data.creadas) {
        const cantidad = data.creados || data.creadas;
        html += `
            <div class="card mb-3">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle fs-1 text-success mb-2 d-block"></i>
                    <h5>${cantidad} registros importados exitosamente</h5>
                </div>
            </div>
        `;
    }

    if (data.errores && data.errores.length > 0) {
        html += `
            <div class="card border-warning">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Errores detectados (${data.total_errores})
                    </h6>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <ul class="mb-0">
        `;

        data.errores.forEach(error => {
            html += `<li class="text-danger"><small>${error}</small></li>`;
        });

        html += `
                    </ul>
                </div>
            </div>
        `;
    }

    document.getElementById('resultadosContent').innerHTML = html;
    
    // Cerrar modal de previa
    bootstrap.Modal.getInstance(document.getElementById('previewModal')).hide();
    
    // Mostrar modal de resultados
    const modal = new bootstrap.Modal(document.getElementById('resultadosModal'));
    modal.show();
}

function resetearFormulario() {
    document.getElementById('importForm').reset();
    resetFile();
    document.getElementById('btnImport').style.display = 'none';
    bootstrap.Modal.getInstance(document.getElementById('resultadosModal')).hide();
}
</script>
@endsection
