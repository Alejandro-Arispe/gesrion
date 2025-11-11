@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="display-5">
                <i class="fas fa-file-pdf"></i> Descargar PDF de QRs Imprimibles
            </h1>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Opciones de Descarga</h5>
                    <p class="text-muted">Elige el formato de impresión que desees:</p>
                    
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action" onclick="descargarPDF('4x4')">
                            <h6 class="mb-1"><i class="fas fa-qrcode"></i> QR Individual (10x10 cm)</h6>
                            <small>Un código QR por página</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="descargarPDF('2x3')">
                            <h6 class="mb-1"><i class="fas fa-th"></i> Matriz 2x3 (Recomendado)</h6>
                            <small>6 códigos QR por página</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="descargarPDF('3x4')">
                            <h6 class="mb-1"><i class="fas fa-th"></i> Matriz 3x4</h6>
                            <small>12 códigos QR por página</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="descargarPDF('5x6')">
                            <h6 class="mb-1"><i class="fas fa-th-large"></i> Matriz 5x6</h6>
                            <small>30 códigos QR por página</small>
                        </a>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5>Vista Previa</h5>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Información:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Los QRs se imprimirán con información de la aula</li>
                            <li>Recomendamos imprimir en papel blanco de 80g</li>
                            <li>Ajusta la escala a 100% al imprimir</li>
                            <li>Cada QR incluye el número de aula para identificación rápida</li>
                        </ul>
                    </div>

                    <div class="bg-light p-3" style="border: 2px dashed #ccc; border-radius: 5px;">
                        <small class="text-muted">
                            <strong>Tamaño de QR por formato:</strong><br>
                            • 4x4: 10 cm x 10 cm<br>
                            • 2x3: ~8.5 cm x ~8.5 cm<br>
                            • 3x4: ~6 cm x ~6 cm<br>
                            • 5x6: ~4 cm x ~4 cm
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function descargarPDF(formato) {
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
</script>
@endsection
