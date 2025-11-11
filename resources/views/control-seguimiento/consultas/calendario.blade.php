@extends('layouts.app')
@section('page-title', 'Calendario de Asistencia')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar me-2"></i>Calendario de Asistencia</h5>
                    <div>
                        <form method="GET" class="d-inline">
                            <input type="month" name="mes" class="form-control d-inline" style="width: auto;" value="{{ $mes->format('Y-m') }}" onchange="this.form.submit()">
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($asistencias->count() > 0)
                    <div class="row">
                        @foreach($calendarioData as $fecha => $datos)
                        @php
                            $carbonFecha = \Carbon\Carbon::parse($fecha);
                            $color = $datos['total'] > 0 ? 'primary' : 'light';
                            if ($datos['ausentes'] > 0 && $datos['ausentes'] == $datos['total']) {
                                $color = 'danger';
                            } elseif ($datos['atrasados'] > 0) {
                                $color = 'warning';
                            }
                        @endphp
                        <div class="col-md-3 mb-3">
                            <div class="card border-2 border-{{ $color }}">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $carbonFecha->format('d/m/Y') }}</h6>
                                    <div class="row g-2 text-center">
                                        <div class="col-6">
                                            <small class="text-muted">Total</small>
                                            <div class="h6 mb-0">{{ $datos['total'] }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-success">Presentes</small>
                                            <div class="h6 mb-0 text-success">{{ $datos['presentes'] }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-warning">Atrasados</small>
                                            <div class="h6 mb-0 text-warning">{{ $datos['atrasados'] }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-danger">Ausentes</small>
                                            <div class="h6 mb-0 text-danger">{{ $datos['ausentes'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>No hay datos de asistencia para este período
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
