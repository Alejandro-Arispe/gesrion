<?php

namespace App\Exports;

use App\Models\ControlSeguimiento\Asistencia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AsistenciaExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        $query = Asistencia::with(['docente', 'horario.grupo.materia', 'horario.aula']);

        if (isset($this->filtros['id_docente'])) {
            $query->where('id_docente', $this->filtros['id_docente']);
        }

        if (isset($this->filtros['fecha_inicio']) && isset($this->filtros['fecha_fin'])) {
            $query->whereBetween('fecha', [
                $this->filtros['fecha_inicio'],
                $this->filtros['fecha_fin']
            ]);
        }

        return $query->orderBy('fecha', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Docente',
            'CI',
            'Materia',
            'Grupo',
            'Aula',
            'Hora Marcado',
            'Estado',
            'Latitud',
            'Longitud'
        ];
    }

    public function map($asistencia): array
    {
        return [
            $asistencia->fecha,
            $asistencia->docente->nombre,
            $asistencia->docente->ci,
            $asistencia->horario->grupo->materia->nombre ?? 'N/A',
            $asistencia->horario->grupo->nombre ?? 'N/A',
            $asistencia->horario->aula->nro ?? 'N/A',
            $asistencia->hora_marcado,
            $asistencia->estado,
            $asistencia->latitud,
            $asistencia->longitud
        ];
    }
}