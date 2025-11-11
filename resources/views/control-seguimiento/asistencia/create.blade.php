
@extends('layouts.app')
@section('page-title', 'Registrar Asistencia')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Registrar Asistencia Docente</h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Errores en el formulario:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('control-seguimiento.asistencia.store') }}" method="POST" id="asistenciaForm" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Información de Identificación -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Docente *</label>
                            <select class="form-select @error('id_docente') is-invalid @enderror" 
                                    name="id_docente" id="id_docente" required>
                                <option value="">-- Seleccione --</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id_docente }}" {{ old('id_docente') == $docente->id_docente ? 'selected' : '' }}>
                                        {{ $docente->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_docente')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Fecha *</label>
                            <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
                                   name="fecha" id="fecha" value="{{ old('fecha') ?? $hoy }}" required>
                            @error('fecha')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <!-- Horarios Disponibles -->
                    <div class="mb-3">
                        <label class="form-label fw-medium">Horario *</label>
                        <select class="form-select @error('id_horario') is-invalid @enderror" 
                                name="id_horario" id="id_horario" required>
                            <option value="">-- Seleccione un horario --</option>
                            @forelse($horariosHoy as $horario)
                                <option value="{{ $horario->id_horario }}" data-hora-inicio="{{ $horario->hora_inicio }}" data-hora-fin="{{ $horario->hora_fin }}" {{ old('id_horario') == $horario->id_horario ? 'selected' : '' }}>
                                    <strong>{{ $horario->hora_inicio }} - {{ $horario->hora_fin }}</strong> | 
                                    {{ $horario->grupo->materia->nombre ?? 'N/A' }} (Grupo {{ $horario->grupo->nombre ?? 'N/A' }}) | 
                                    Aula {{ $horario->aula->nro ?? 'N/A' }}
                                    @if($horario->grupo && $horario->grupo->docente)
                                        - {{ $horario->grupo->docente->nombre }}
                                    @endif
                                </option>
                            @empty
                                <option value="" disabled>No hay horarios disponibles para hoy</option>
                            @endforelse
                        </select>
                        @error('id_horario')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <!-- Hora y Estado -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Hora de Marcado *</label>
                            <input type="time" class="form-control @error('hora_marcado') is-invalid @enderror" 
                                   name="hora_marcado" id="hora_marcado" value="{{ old('hora_marcado') ?? date('H:i') }}" required>
                            <small class="text-muted" id="hora-info"></small>
                            @error('hora_marcado')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Estado *</label>
                            <select class="form-select @error('estado') is-invalid @enderror" name="estado" id="estado" required>
                                <option value="">-- Seleccione --</option>
                                <option value="Presente" {{ old('estado') == 'Presente' ? 'selected' : '' }}>
                                    <i class="bi bi-check-circle"></i> Presente
                                </option>
                                <option value="Atrasado" {{ old('estado') == 'Atrasado' ? 'selected' : '' }}>
                                    <i class="bi bi-clock"></i> Atrasado
                                </option>
                                <option value="Ausente" {{ old('estado') == 'Ausente' ? 'selected' : '' }}>
                                    <i class="bi bi-x-circle"></i> Ausente
                                </option>
                                <option value="Fuera de aula" {{ old('estado') == 'Fuera de aula' ? 'selected' : '' }}>
                                    <i class="bi bi-geo-alt"></i> Fuera de aula
                                </option>
                            </select>
                            @error('estado')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-geo-alt me-1"></i> Ubicación GPS
                            </label>
                            <button type="button" class="btn btn-outline-primary w-100" id="btn-ubicacion">
                                <i class="bi bi-geo-alt-fill me-1"></i> Obtener Ubicación
                            </button>
                            <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud') }}">
                            <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud') }}">
                            <small id="gps-info" class="text-muted d-block mt-2"></small>
                        </div>
                    </div>

                    <!-- SECCIÓN DE QR OBLIGATORIA -->
                    <div class="card border-danger mb-3">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="bi bi-qr-code me-2"></i>Validación de Aula - Leer QR</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Obligatorio:</strong> Debes escanear el código QR del aula para validar tu presencia
                            </p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <button type="button" class="btn btn-danger w-100" id="btn-scanner-qr">
                                        <i class="bi bi-qr-code me-1"></i> Abrir Lector QR
                                    </button>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-small">O ingresa el QR manualmente:</label>
                                    <input type="text" class="form-control" id="qr_manual" placeholder="Pega el contenido del QR aquí">
                                </div>
                            </div>

                            <!-- Canvas para el lector QR -->
                            <div id="qr-scanner" style="display:none; text-align:center; background:#000; padding:20px; border-radius:0.25rem; margin-bottom:15px;">
                                <video id="qr-video" style="width:100%; max-width:400px; border: 2px solid #fff;"></video>
                                <p class="text-white mt-2 mb-0"><small>Apunta el QR hacia la cámara</small></p>
                            </div>

                            <!-- Estado del QR -->
                            <div id="qr-status" class="alert alert-warning d-none" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <span id="qr-message">Esperando lectura de QR...</span>
                            </div>

                            <!-- Información del aula después de escanear QR -->
                            <div id="qr-success" class="alert alert-success d-none" role="alert">
                                <i class="bi bi-check-circle me-1"></i>
                                <strong>QR Validado Correctamente</strong>
                                <div class="mt-2">
                                    <p class="mb-1"><strong>Aula:</strong> <span id="qr-aula-nro"></span></p>
                                    <p class="mb-1"><strong>Piso:</strong> <span id="qr-aula-piso"></span></p>
                                    <p class="mb-1"><strong>Tipo:</strong> <span id="qr-aula-tipo"></span></p>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-secondary" id="btn-cerrar-scanner" style="display:none;">
                                <i class="bi bi-x me-1"></i> Cerrar Lector
                            </button>

                            <!-- Hidden field para guardar el aula validada -->
                            <input type="hidden" id="qr_aula_validada" name="qr_aula_validada" value="">
                        </div>
                    </div>

                    <!-- Captura de Foto (Opcional) -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="bi bi-camera me-2"></i>Captura de Foto <span class="badge bg-warning text-dark">Opcional</span>
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Acceso a Cámara</label>
                                    <button type="button" class="btn btn-outline-secondary w-100" id="btn-camara">
                                        <i class="bi bi-camera me-1"></i> Activar Cámara
                                    </button>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subir Foto</label>
                                    <input type="file" class="form-control @error('foto') is-invalid @enderror" 
                                           id="foto" name="foto" accept="image/*" onchange="mostrarPreview()">
                                    @error('foto')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- Vista Previa de Video en Tiempo Real -->
                            <div id="preview-video" style="display:none;" class="mb-3">
                                <video id="video-stream" width="100%" height="300" style="border: 2px solid #dee2e6; border-radius: 0.25rem; background: #000;"></video>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary" id="btn-capturar">
                                        <i class="bi bi-camera-fill me-1"></i> Capturar Foto
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" id="btn-cerrar-camara">
                                        <i class="bi bi-x me-1"></i> Cerrar Cámara
                                    </button>
                                </div>
                            </div>

                            <!-- Canvas Oculto para Captura -->
                            <canvas id="canvas" style="display:none;"></canvas>

                            <!-- Preview de Foto Capturada -->
                            <div id="preview-foto" style="display:none;" class="mb-3">
                                <img id="img-preview" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border: 2px solid #dee2e6; border-radius: 0.25rem;">
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-secondary" id="btn-retomar">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Retomar Foto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información y Validación -->
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>⚠️ Requisitos Obligatorios:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>QR de Aula:</strong> OBLIGATORIO - Debes escanear el código QR del aula antes de registrar asistencia</li>
                            <li><strong>Ubicación GPS:</strong> RECOMENDADA - Valida tu presencia en el aula (radio: 50m). Si estás fuera del rango, se registrará como "Fuera de aula"</li>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Información Adicional:</strong>
                        <ul class="mb-0 mt-2">
                            <li>El sistema detectará automáticamente si llegas <strong>atrasado</strong> (>10 minutos después de la hora de inicio)</li>
                            <li>La foto es opcional pero útil para auditorías</li>
                            <li>Si tu ubicación GPS está fuera del rango del aula (>50m), tu asistencia se marcará como "Fuera de aula"</li>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                    <hr>
                    
                    <!-- Botones de Acción -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" id="btn-submit">
                            <i class="bi bi-check-circle me-1"></i> Registrar Asistencia
                        </button>
                        <a href="{{ route('control-seguimiento.asistencia.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualización de foto capturada -->
<div class="modal fade" id="fotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto Capturada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modal-foto" src="" alt="Foto" style="max-width: 100%; max-height: 400px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- jsQR Library -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
let stream = null;
let capturedImageData = null;
let qrScannerActive = false;
let qrValidated = false;

document.addEventListener('DOMContentLoaded', function() {
    // Validar hora de marcado contra horario seleccionado
    document.getElementById('hora_marcado').addEventListener('change', validarHora);
    document.getElementById('id_horario').addEventListener('change', validarHora);
    
    // Botón de ubicación
    document.getElementById('btn-ubicacion').addEventListener('click', obtenerUbicacion);
    
    // Botones de cámara
    document.getElementById('btn-camara').addEventListener('click', activarCamara);
    document.getElementById('btn-cerrar-camara').addEventListener('click', cerrarCamara);
    document.getElementById('btn-capturar').addEventListener('click', capturarFoto);
    document.getElementById('btn-retomar').addEventListener('click', retomar);
    
    // Botones de QR
    document.getElementById('btn-scanner-qr').addEventListener('click', abrirLectorQR);
    document.getElementById('btn-cerrar-scanner').addEventListener('click', cerrarLectorQR);
    document.getElementById('qr_manual').addEventListener('change', procesarQRManual);
    
    // Validación antes de enviar
    document.getElementById('asistenciaForm').addEventListener('submit', function(e) {
        if (!qrValidated) {
            e.preventDefault();
            alert('❌ Debe escanear el QR del aula antes de registrar la asistencia');
            return false;
        }
    });
});

// ============================================
// FUNCIONES DE QR
// ============================================

function abrirLectorQR() {
    const video = document.getElementById('qr-video');
    qrScannerActive = true;
    
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Tu navegador no soporta acceso a cámara');
        return;
    }

    navigator.mediaDevices.getUserMedia({ 
        video: { facingMode: 'environment' } 
    })
    .then(function(mediaStream) {
        stream = mediaStream;
        video.srcObject = stream;
        document.getElementById('qr-scanner').style.display = 'block';
        document.getElementById('btn-scanner-qr').style.display = 'none';
        document.getElementById('btn-cerrar-scanner').style.display = 'inline-block';
        
        // Iniciar lectura de QR
        leerQR();
    })
    .catch(function(error) {
        alert('Error al acceder a la cámara: ' + error.message);
    });
}

function cerrarLectorQR() {
    qrScannerActive = false;
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    document.getElementById('qr-scanner').style.display = 'none';
    document.getElementById('btn-scanner-qr').style.display = 'block';
    document.getElementById('btn-cerrar-scanner').style.display = 'none';
}

function leerQR() {
    const video = document.getElementById('qr-video');
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    const procesarFrame = () => {
        if (!qrScannerActive) return;

        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0);

            try {
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, canvas.width, canvas.height);

                if (code) {
                    // QR detectado
                    validarQRLeido(code.data);
                    return; // Salir del loop después de detectar
                }
            } catch (e) {
                // Continuar buscando
            }
        }

        requestAnimationFrame(procesarFrame);
    };

    procesarFrame();
}

function validarQRLeido(codigoQr) {
    const statusDiv = document.getElementById('qr-status');
    const messageSpan = document.getElementById('qr-message');
    const successDiv = document.getElementById('qr-success');

    // Mostrar estado de validación
    messageSpan.textContent = 'Validando QR...';
    statusDiv.classList.remove('d-none');

    // Enviar al servidor
    fetch('{{ route("planificacion.qr.validar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            codigo_qr_leido: codigoQr
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.valido) {
            // Éxito
            qrValidated = true;
            document.getElementById('qr_aula_validada').value = data.aula.id_aula;
            
            // Mostrar información del aula
            document.getElementById('qr-aula-nro').textContent = data.aula.nro_aula;
            document.getElementById('qr-aula-piso').textContent = data.aula.piso;
            document.getElementById('qr-aula-tipo').textContent = data.aula.tipo_aula;
            
            // Ocultar estado, mostrar éxito
            statusDiv.classList.add('d-none');
            successDiv.classList.remove('d-none');
            
            // Cerrar scanner
            cerrarLectorQR();
            
            // Actualizar botón
            document.getElementById('btn-scanner-qr').classList.remove('btn-danger');
            document.getElementById('btn-scanner-qr').classList.add('btn-success');
            document.getElementById('btn-scanner-qr').innerHTML = '<i class="bi bi-check-circle me-1"></i> QR Validado ✓';
            document.getElementById('btn-scanner-qr').disabled = true;
        } else {
            // Error
            messageSpan.textContent = '❌ ' + (data.message || 'QR no válido o no registrado');
            statusDiv.classList.remove('alert-warning');
            statusDiv.classList.add('alert-danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageSpan.textContent = '❌ Error al validar QR: ' + error.message;
        statusDiv.classList.remove('alert-warning');
        statusDiv.classList.add('alert-danger');
    });
}

function procesarQRManual() {
    const codigoQr = document.getElementById('qr_manual').value.trim();
    if (codigoQr) {
        validarQRLeido(codigoQr);
        document.getElementById('qr_manual').value = '';
    }
}

// ============================================
// FUNCIONES EXISTENTES
// ============================================

function validarHora() {
    const horaMarkado = document.getElementById('hora_marcado').value;
    const selectHorario = document.getElementById('id_horario');
    const horaInicio = selectHorario.options[selectHorario.selectedIndex]?.dataset.horaInicio;
    
    if (horaMarkado && horaInicio) {
        const [hm, mm] = horaMarkado.split(':').map(Number);
        const [hi, mi] = horaInicio.split(':').map(Number);
        const minutosMarcado = hm * 60 + mm;
        const minutosInicio = hi * 60 + mi;
        const diferencia = minutosMarcado - minutosInicio;
        
        if (diferencia > 10) {
            document.getElementById('hora-info').innerHTML = 
                `<i class="bi bi-exclamation-triangle text-warning me-1"></i>Llegada con ${diferencia} minutos de atraso`;
            document.getElementById('estado').value = 'Atrasado';
        } else if (diferencia >= 0) {
            document.getElementById('hora-info').innerHTML = 
                `<i class="bi bi-check-circle text-success me-1"></i>Llegada a tiempo`;
            document.getElementById('estado').value = 'Presente';
        } else {
            document.getElementById('hora-info').innerHTML = 
                `<i class="bi bi-info-circle text-info me-1"></i>Hora anterior al inicio (${Math.abs(diferencia)} min)`;
        }
    }
}

function obtenerUbicacion() {
    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización');
        return;
    }

    const btn = document.getElementById('btn-ubicacion');
    btn.disabled = true;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Obteniendo...';

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            
            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lon;
            
            const info = document.getElementById('gps-info');
            info.innerHTML = `
                <i class="bi bi-check-circle text-success me-1"></i>
                Ubicación obtenida: ${lat.toFixed(6)}, ${lon.toFixed(6)}
                <small class="d-block text-muted">Precisión: ±${position.coords.accuracy.toFixed(0)}m</small>
            `;
            
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i> Ubicación Obtenida ✓';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
        },
        function(error) {
            let mensaje = 'Error al obtener ubicación';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    mensaje = 'Permiso denegado. Habilita ubicación en tu navegador';
                    break;
                case error.POSITION_UNAVAILABLE:
                    mensaje = 'Información de ubicación no disponible';
                    break;
                case error.TIMEOUT:
                    mensaje = 'Tiempo agotado al obtener ubicación';
                    break;
            }
            alert(mensaje);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i> Obtener Ubicación';
        }
    );
}

function activarCamara() {
    const previewVideo = document.getElementById('preview-video');
    const video = document.getElementById('video-stream');
    
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Tu navegador no soporta acceso a cámara');
        return;
    }

    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
        .then(function(mediaStream) {
            stream = mediaStream;
            video.srcObject = stream;
            previewVideo.style.display = 'block';
            document.getElementById('btn-camara').style.display = 'none';
        })
        .catch(function(error) {
            alert('Error al acceder a la cámara: ' + error.message);
        });
}

function cerrarCamara() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    document.getElementById('preview-video').style.display = 'none';
    document.getElementById('btn-camara').style.display = 'block';
}

function capturarFoto() {
    const video = document.getElementById('video-stream');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);
    
    capturedImageData = canvas.toDataURL('image/jpeg');
    
    // Mostrar preview
    const previewFoto = document.getElementById('preview-foto');
    document.getElementById('img-preview').src = capturedImageData;
    previewFoto.style.display = 'block';
    
    // Ocultar videostream
    document.getElementById('preview-video').style.display = 'none';
    cerrarCamara();
}

function retomar() {
    document.getElementById('preview-foto').style.display = 'none';
    activarCamara();
}

function mostrarPreview() {
    const input = document.getElementById('foto');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            capturedImageData = e.target.result;
            document.getElementById('img-preview').src = e.target.result;
            document.getElementById('preview-foto').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('asistenciaForm').addEventListener('submit', function(e) {
    // Si capturamos foto desde cámara, guardarla
    if (capturedImageData && !document.getElementById('foto').files.length) {
        fetch(capturedImageData)
            .then(res => res.blob())
            .then(blob => {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(new File([blob], 'asistencia.jpg', { type: 'image/jpeg' }));
                document.getElementById('foto').files = dataTransfer.files;
            });
    }
});

// Cargar dinámicamente los horarios cuando se selecciona un docente
document.getElementById('id_docente').addEventListener('change', function() {
    const docenteId = this.value;
    const horariosSelect = document.getElementById('id_horario');
    
    if (!docenteId) {
        horariosSelect.innerHTML = '<option value="">-- Seleccione un horario --</option>';
        return;
    }
    
    // Mostrar estado de carga
    horariosSelect.innerHTML = '<option value="" disabled>Cargando horarios...</option>';
    horariosSelect.disabled = true;
    
    // Enviar AJAX para obtener horarios
    fetch('{{ route("control-seguimiento.asistencia.horarios-docente") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            id_docente: docenteId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            horariosSelect.innerHTML = data.html;
            horariosSelect.disabled = false;
        } else {
            horariosSelect.innerHTML = '<option value="" disabled>Error al cargar horarios</option>';
            horariosSelect.disabled = true;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        horariosSelect.innerHTML = '<option value="" disabled>Error al cargar horarios</option>';
        horariosSelect.disabled = true;
    });
});

</script>
@endsection