<?php

namespace App\Http\Controllers\ReporteDatos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionAcademica\Docente;
use App\Models\Planificacion\Horario;
use App\Models\ControlSeguimiento\Asistencia;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    /**
     * Mostrar página de reportes
     */
    public function index()
    {
        $docentes = Docente::orderBy('nombre')->get();
        $gestiones = \App\Models\ConfiguracionAcademica\GestionAcademica::orderBy('anio', 'desc')->get();
        
        return view('reporte-datos.reportes.index', compact('docentes', 'gestiones'));
    }

    /**
     * Generar reporte de horarios en PDF
     */
    public function horariosPDF(Request $request)
    {
        $request->validate([
            'id_gestion' => 'nullable|exists:gestion_academica,id_gestion',
            'id_docente' => 'nullable|exists:docente,id_docente',
        ]);

        $query = Horario::with(['grupo.materia', 'grupo.docente', 'aula']);

        if ($request->id_gestion) {
            $query->whereHas('grupo', function($q) use ($request) {
                $q->where('id_gestion', $request->id_gestion);
            });
        }

        if ($request->id_docente) {
            $query->whereHas('grupo', function($q) use ($request) {
                $q->where('id_docente', $request->id_docente);
            });
        }

        $horarios = $query->orderBy('dia_semana')->orderBy('hora_inicio')->get();

        if ($horarios->isEmpty()) {
            return back()->with('warning', 'No hay horarios para generar el reporte');
        }

        $pdf = Pdf::loadView('reportes.horarios-pdf', compact('horarios'))
                  ->setPaper('a4', 'landscape')
                  ->setOption('margin-top', 10)
                  ->setOption('margin-bottom', 10);

        return $pdf->download('horarios-' . date('Y-m-d-H-i-s') . '.pdf');
    }

    /**
     * Generar reporte de asistencia en PDF
     */
    public function asistenciaPDF(Request $request)
    {
        $request->validate([
            'id_gestion' => 'nullable|exists:gestion_academica,id_gestion',
            'id_docente' => 'nullable|exists:docente,id_docente',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
        ]);

        $query = Asistencia::with(['docente', 'horario.grupo.materia', 'horario.aula']);

        if ($request->id_docente) {
            $query->where('id_docente', $request->id_docente);
        }

        if ($request->fecha_inicio && $request->fecha_fin) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $asistencias = $query->orderBy('fecha', 'desc')->get();

        if ($asistencias->isEmpty()) {
            return back()->with('warning', 'No hay registros de asistencia para generar el reporte');
        }

        $pdf = Pdf::loadView('reportes.asistencia-pdf', compact('asistencias'))
                  ->setPaper('a4')
                  ->setOption('margin-top', 10)
                  ->setOption('margin-bottom', 10);

        return $pdf->download('asistencia-' . date('Y-m-d-H-i-s') . '.pdf');
    }

    /**
     * Generar reporte de carga horaria
     */
    public function cargaHoraria(Request $request)
    {
        $request->validate([
            'id_gestion' => 'nullable|exists:gestion_academica,id_gestion',
            'formato' => 'in:pdf,excel',
        ]);

        $query = Docente::with(['grupos.materia', 'grupos.horarios.aula']);

        if ($request->id_gestion) {
            $query->whereHas('grupos', function($q) use ($request) {
                $q->where('id_gestion', $request->id_gestion);
            });
        }

        $docentes = $query->orderBy('nombre')->get();

        if ($docentes->isEmpty()) {
            return back()->with('warning', 'No hay docentes para generar el reporte');
        }

        $formato = $request->formato ?? 'pdf';

        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.carga-horaria-pdf', compact('docentes'))
                      ->setPaper('a4', 'landscape')
                      ->setOption('margin-top', 10)
                      ->setOption('margin-bottom', 10);

            return $pdf->download('carga-horaria-' . date('Y-m-d-H-i-s') . '.pdf');
        } else {
            // Exportar a Excel
            return $this->exportarCargaHorariaExcel($docentes);
        }
    }

    /**
     * Exportar carga horaria a Excel
     */
    private function exportarCargaHorariaExcel($docentes)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Carga Horaria');

        // Encabezado
        $sheet->setCellValue('A1', 'REPORTE DE CARGA HORARIA DOCENTE');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:H2');

        // Encabezados de columna
        $headers = ['Docente', 'Materia', 'Grupo', 'Aula', 'Día', 'Hora Inicio', 'Hora Fin', 'Horas'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getStyle($col . '4')->getFont()->setBold(true);
            $sheet->getStyle($col . '4')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF4CAF50');
            $sheet->getStyle($col . '4')->getFont()->getColor()->setARGB('FFFFFFFF');
            $col++;
        }

        // Datos
        $row = 5;
        $totalHoras = 0;
        foreach ($docentes as $docente) {
            $horasDocente = 0;
            foreach ($docente->grupos as $grupo) {
                foreach ($grupo->horarios as $horario) {
                    $horaInicio = \Carbon\Carbon::createFromFormat('H:i', $horario->hora_inicio);
                    $horaFin = \Carbon\Carbon::createFromFormat('H:i', $horario->hora_fin);
                    $horas = $horaFin->diffInMinutes($horaInicio) / 60;
                    $horasDocente += $horas;
                    $totalHoras += $horas;

                    $sheet->setCellValue('A' . $row, $docente->nombre);
                    $sheet->setCellValue('B' . $row, $grupo->materia->nombre);
                    $sheet->setCellValue('C' . $row, $grupo->nombre);
                    $sheet->setCellValue('D' . $row, $horario->aula->nro ?? 'N/A');
                    $sheet->setCellValue('E' . $row, $horario->dia_semana);
                    $sheet->setCellValue('F' . $row, $horario->hora_inicio);
                    $sheet->setCellValue('G' . $row, $horario->hora_fin);
                    $sheet->setCellValue('H' . $row, number_format($horas, 2));
                    $row++;
                }
            }

            if ($horasDocente > 0) {
                $sheet->setCellValue('A' . $row, 'Total ' . $docente->nombre);
                $sheet->mergeCells('A' . $row . ':G' . $row);
                $sheet->setCellValue('H' . $row, number_format($horasDocente, 2));
                $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF0F0F0');
                $row++;
            }
        }

        // Total general
        $row++;
        $sheet->setCellValue('A' . $row, 'TOTAL GENERAL');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, number_format($totalHoras, 2));
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FF4CAF50');
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(10);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'carga-horaria-' . date('Y-m-d-H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    /**
     * Reporte de asistencia docente por período
     */
    public function asistenciaPorPeriodo(Request $request)
    {
        $request->validate([
            'id_docente' => 'required|exists:docente,id_docente',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        $docente = Docente::findOrFail($request->id_docente);
        $asistencias = Asistencia::where('id_docente', $request->id_docente)
            ->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin])
            ->with(['horario.grupo.materia', 'horario.aula'])
            ->orderBy('fecha')
            ->get();

        $total = $asistencias->count();
        $asistentes = $asistencias->where('asistio', true)->count();
        $inasistentes = $total - $asistentes;
        $porcentaje = $total > 0 ? ($asistentes / $total) * 100 : 0;

        $pdf = Pdf::loadView('reportes.asistencia-periodo-pdf', compact(
            'docente', 'asistencias', 'total', 'asistentes', 'inasistentes', 'porcentaje'
        ))->setPaper('a4');

        return $pdf->download('asistencia-' . $docente->nombre . '-' . date('Y-m-d') . '.pdf');
    }

    /**
     * NUEVA: Reporte de asistencia por asignación de horarios
     * Estructura: Docente → Asignaciones (Horarios) → Asistencia registrada
     * Muestra % de asistencia por cada asignación
     */
    public function asistenciaPorAsignacion(Request $request)
    {
        $request->validate([
            'id_docente' => 'required|exists:docente,id_docente',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'formato' => 'in:pdf,excel'
        ]);

        $docente = Docente::with('grupos')->findOrFail($request->id_docente);
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $formato = $request->formato ?? 'pdf';

        // Obtener todas las asignaciones del docente (horarios)
        $asignaciones = Horario::with(['grupo.materia', 'aula'])
            ->whereHas('grupo', function($q) use ($docente) {
                $q->where('id_docente', $docente->id_docente);
            })
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        // Procesar datos de asistencia por asignación
        $reporteAsignaciones = $asignaciones->map(function($horario) use ($docente, $fechaInicio, $fechaFin) {
            // Obtener registros de asistencia para este horario en el período
            $asistencias = Asistencia::where('id_docente', $docente->id_docente)
                ->where('id_horario', $horario->id_horario)
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->orderBy('fecha')
                ->get();

            $total = $asistencias->count();
            $presentes = $asistencias->where('estado', 'Presente')->count();
            $atrasados = $asistencias->where('estado', 'Atrasado')->count();
            $ausentes = $asistencias->where('estado', 'Ausente')->count();
            $fueraAula = $asistencias->where('estado', 'Fuera de aula')->count();

            $porcentajeAsistencia = $total > 0 ? (($presentes + $atrasados) / $total) * 100 : 0;

            return [
                'id_horario' => $horario->id_horario,
                'materia' => $horario->grupo->materia->nombre,
                'grupo' => $horario->grupo->nombre,
                'aula' => $horario->aula->nro,
                'dia_semana' => $horario->dia_semana,
                'horario' => "{$horario->hora_inicio} - {$horario->hora_fin}",
                'asistencias' => $asistencias,
                'total' => $total,
                'presentes' => $presentes,
                'atrasados' => $atrasados,
                'ausentes' => $ausentes,
                'fuera_aula' => $fueraAula,
                'porcentaje_asistencia' => round($porcentajeAsistencia, 2)
            ];
        });

        // Calcular totales generales
        $totalGeneral = $reporteAsignaciones->sum('total');
        $presentesGeneral = $reporteAsignaciones->sum('presentes');
        $atrasadosGeneral = $reporteAsignaciones->sum('atrasados');
        $ausentesGeneral = $reporteAsignaciones->sum('ausentes');
        $fueraAulaGeneral = $reporteAsignaciones->sum('fuera_aula');
        $porcentajeGeneral = $totalGeneral > 0 ? (($presentesGeneral + $atrasadosGeneral) / $totalGeneral) * 100 : 0;

        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.asistencia-asignacion-pdf', [
                'docente' => $docente,
                'reporteAsignaciones' => $reporteAsignaciones,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'totalGeneral' => $totalGeneral,
                'presentesGeneral' => $presentesGeneral,
                'atrasadosGeneral' => $atrasadosGeneral,
                'ausentesGeneral' => $ausentesGeneral,
                'fueraAulaGeneral' => $fueraAulaGeneral,
                'porcentajeGeneral' => $porcentajeGeneral
            ])->setPaper('a4', 'landscape');

            return $pdf->download("asistencia-asignacion-{$docente->nombre}-" . date('Y-m-d-H-i-s') . '.pdf');
        } else {
            // Exportar a Excel
            return $this->exportarAsistenciaAsignacionExcel(
                $docente, 
                $reporteAsignaciones, 
                $fechaInicio, 
                $fechaFin,
                $totalGeneral,
                $presentesGeneral,
                $atrasadosGeneral,
                $ausentesGeneral,
                $fueraAulaGeneral,
                $porcentajeGeneral
            );
        }
    }

    /**
     * Exportar reporte de asistencia por asignación a Excel
     */
    private function exportarAsistenciaAsignacionExcel(
        $docente, 
        $reporteAsignaciones, 
        $fechaInicio, 
        $fechaFin,
        $totalGeneral,
        $presentesGeneral,
        $atrasadosGeneral,
        $ausentesGeneral,
        $fueraAulaGeneral,
        $porcentajeGeneral
    ) {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Asistencia por Asignación');

        // Encabezado
        $sheet->setCellValue('A1', "REPORTE DE ASISTENCIA POR ASIGNACIÓN - {$docente->nombre}");
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A2', "Período: {$fechaInicio} al {$fechaFin} | Generado: " . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->getFont()->setItalic(true);

        // Encabezados de columna
        $headers = ['Materia', 'Grupo', 'Aula', 'Día', 'Horario', 'Total', 'Presentes', 'Atrasados', 'Ausentes', '% Asistencia'];
        $col = 'A';
        $row = 4;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($col . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FF2E7D32');
            $col++;
        }

        // Datos
        $row = 5;
        foreach ($reporteAsignaciones as $asignacion) {
            $sheet->setCellValue('A' . $row, $asignacion['materia']);
            $sheet->setCellValue('B' . $row, $asignacion['grupo']);
            $sheet->setCellValue('C' . $row, $asignacion['aula']);
            $sheet->setCellValue('D' . $row, $asignacion['dia_semana']);
            $sheet->setCellValue('E' . $row, $asignacion['horario']);
            $sheet->setCellValue('F' . $row, $asignacion['total']);
            $sheet->setCellValue('G' . $row, $asignacion['presentes']);
            $sheet->setCellValue('H' . $row, $asignacion['atrasados']);
            $sheet->setCellValue('I' . $row, $asignacion['ausentes']);
            $sheet->setCellValue('J' . $row, $asignacion['porcentaje_asistencia'] . '%');
            
            // Color de celda según % asistencia
            $color = $asignacion['porcentaje_asistencia'] >= 80 ? 'FFCCF0CC' : 
                    ($asignacion['porcentaje_asistencia'] >= 60 ? 'FFFFF3CD' : 'FFFCCECC');
            $sheet->getStyle('J' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB($color);
            
            $row++;
        }

        // Totales generales
        $row++;
        $sheet->setCellValue('A' . $row, 'TOTAL GENERAL');
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('F' . $row, $totalGeneral);
        $sheet->setCellValue('G' . $row, $presentesGeneral);
        $sheet->setCellValue('H' . $row, $atrasadosGeneral);
        $sheet->setCellValue('I' . $row, $ausentesGeneral);
        $sheet->setCellValue('J' . $row, $porcentajeGeneral . '%');
        
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FF2E7D32');
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(15);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = "asistencia-asignacion-{$docente->nombre}-" . date('Y-m-d-H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }
}
