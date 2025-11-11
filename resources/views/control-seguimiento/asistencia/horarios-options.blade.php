@forelse($horarios as $horario)
    <option value="{{ $horario->id_horario }}" 
            data-hora-inicio="{{ $horario->hora_inicio }}" 
            data-hora-fin="{{ $horario->hora_fin }}">
        <strong>{{ $horario->hora_inicio }} - {{ $horario->hora_fin }}</strong> | 
        {{ $horario->grupo->materia->nombre ?? 'N/A' }} (Grupo {{ $horario->grupo->nombre ?? 'N/A' }}) | 
        @if($horario->aula)
            Aula {{ $horario->aula->nro ?? 'N/A' }}
        @else
            Aula N/A
        @endif
        @if($horario->grupo && $horario->grupo->docente)
            - {{ $horario->grupo->docente->nombre }}
        @endif
    </option>
@empty
    <option value="" disabled>No hay horarios disponibles para hoy</option>
@endforelse
