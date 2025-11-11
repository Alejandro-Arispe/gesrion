<div class="alert alert-info mb-4">
    <i class="bi bi-calendar-week me-2"></i>
    <strong>Resumen de la semana del {{ $inicioSemana->format('d/m/Y') }} al {{ $finSemana->format('d/m/Y') }}</strong>
</div>

@if(!empty($resumenPorDia) && count($resumenPorDia) > 0)
    <div class="row">
        @foreach($resumenPorDia as $dia => $datos)
        <div class="col-md-6 mb-3">
            <div class="card border-start border-4" style="border-color: {{ $datos['total'] > 0 ? '#28a745' : '#ccc' }};">
                <div class="card-body">
                    <h6 class="card-title mb-2">
                        <i class="bi bi-calendar-day me-2"></i>{{ ucfirst($dia) }} 
                        <small class="text-muted">({{ $datos['fecha'] }})</small>
                    </h6>
                    
                    @if($datos['total'] > 0)
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted">Total</small>
                                <div class="h5 mb-0 text-primary">{{ $datos['total'] }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Presentes</small>
                                <div class="h5 mb-0 text-success">{{ $datos['presentes'] }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Atrasados</small>
                                <div class="h5 mb-0 text-warning">{{ $datos['atrasados'] }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Ausentes</small>
                                <div class="h5 mb-0 text-danger">{{ $datos['ausentes'] }}</div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">Sin registros este día</p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        No hay datos de asistencia para esta semana
    </div>
@endif
