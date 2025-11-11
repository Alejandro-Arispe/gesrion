<?php

namespace App\Services;

use App\Models\ConfiguracionAcademica\Materia;
use App\Models\ConfiguracionAcademica\Grupo;
use App\Models\Planificacion\Horario;
use Carbon\Carbon;
use Exception;

class DistribucionHorariosService
{
    /**
     * Patrones predeterminados de distribución
     * LMV: Lunes, Miércoles, Viernes (1.5 horas c/día)
     * MJ: Martes, Jueves (2.25 horas c/día)
     */
    const PATRONES = [
        'LMV' => [
            'dias' => ['Lunes', 'Miércoles', 'Viernes'],
            'duracion_horas' => 1.5,
            'descripcion' => 'Lunes, Miércoles, Viernes - 1:30 hrs cada día'
        ],
        'MJ' => [
            'dias' => ['Martes', 'Jueves'],
            'duracion_horas' => 2.25,
            'descripcion' => 'Martes, Jueves - 2:15 hrs cada día'
        ],
        'L' => [
            'dias' => ['Lunes'],
            'duracion_horas' => 4.5,
            'descripcion' => 'Lunes - 4:30 hrs'
        ],
        'M' => [
            'dias' => ['Martes'],
            'duracion_horas' => 4.5,
            'descripcion' => 'Martes - 4:30 hrs'
        ],
        'X' => [
            'dias' => ['Miércoles'],
            'duracion_horas' => 4.5,
            'descripcion' => 'Miércoles - 4:30 hrs'
        ],
        'J' => [
            'dias' => ['Jueves'],
            'duracion_horas' => 4.5,
            'descripcion' => 'Jueves - 4:30 hrs'
        ],
        'V' => [
            'dias' => ['Viernes'],
            'duracion_horas' => 4.5,
            'descripcion' => 'Viernes - 4:30 hrs'
        ]
    ];

    /**
     * Obtener patrones disponibles
     */
    public function obtenerPatronesDisponibles()
    {
        return array_map(function ($patron, $key) {
            return [
                'clave' => $key,
                'dias' => implode(', ', $patron['dias']),
                'duracion_horas' => $patron['duracion_horas'],
                'descripcion' => $patron['descripcion']
            ];
        }, self::PATRONES, array_keys(self::PATRONES));
    }

    /**
     * Generar distribución de horarios para un grupo
     * 
     * @param Grupo $grupo
     * @param string $patron (LMV, MJ, L, M, X, J, V o personalizado)
     * @param string $horaInicio (HH:MM formato)
     * @param array $diasPersonalizados (opcional)
     * @param float $duracionPersonalizada (opcional, en horas)
     * 
     * @return array Horarios generados o error
     */
    public function generarDistribucion(
        Grupo $grupo,
        string $patron = 'LMV',
        string $horaInicio = '08:00',
        array $diasPersonalizados = [],
        float $duracionPersonalizada = null
    )
    {
        try {
            // Validar carga horaria
            $materia = $grupo->materia;
            if (!$materia->carga_horaria) {
                throw new Exception('La materia no tiene carga horaria definida');
            }

            // Obtener configuración del patrón
            $configuracion = !empty($diasPersonalizados) 
                ? $this->construirConfiguracionPersonalizada($diasPersonalizados, $duracionPersonalizada)
                : self::PATRONES[$patron] ?? self::PATRONES['LMV'];

            $dias = $configuracion['dias'];
            $duracionPorDia = $configuracion['duracion_horas'];

            // Validar que la carga horaria coincida
            $cargaCalculada = count($dias) * $duracionPorDia;
            if (abs($cargaCalculada - $materia->carga_horaria) > 0.1) {
                // Advertencia pero permitir
                $aviso = "La carga horaria configurada ({$cargaCalculada}h) no coincide exactamente con la definida ({$materia->carga_horaria}h)";
            }

            // Convertir hora inicio a minutos
            [$horas, $minutos] = explode(':', $horaInicio);
            $minutosTotales = ($horas * 60) + $minutos;
            $duracionMinutos = (int)($duracionPorDia * 60);
            $horaFin = $this->minutosAHora($minutosTotales + $duracionMinutos);

            $horariosGenerados = [];

            foreach ($dias as $dia) {
                // Validar que no exista horario en conflicto
                $conflicto = $this->verificarConflicto($grupo, $dia, $horaInicio, $horaFin);
                
                if ($conflicto) {
                    return [
                        'exito' => false,
                        'mensaje' => "Conflicto de horario en {$dia}: {$conflicto['descripcion']}"
                    ];
                }

                // Crear horario
                $horario = Horario::create([
                    'id_grupo' => $grupo->id_grupo,
                    'id_aula' => null, // Se asignará luego
                    'dia_semana' => $dia,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'tipo_asignacion' => 'Automática',
                    'distribucion_dias' => [
                        'patron' => $patron,
                        'dias' => $dias,
                        'duracion_minutos' => $duracionMinutos
                    ]
                ]);

                $horariosGenerados[] = [
                    'id_horario' => $horario->id_horario,
                    'dia' => $dia,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin
                ];
            }

            return [
                'exito' => true,
                'mensaje' => count($horariosGenerados) . ' horarios creados exitosamente',
                'aviso' => $aviso ?? null,
                'horarios' => $horariosGenerados,
                'distribucion' => [
                    'patron' => $patron,
                    'dias' => $dias,
                    'duracion_horas_por_dia' => $duracionPorDia,
                    'carga_total_horas' => $cargaCalculada
                ]
            ];

        } catch (Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error al generar distribución: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Construir configuración personalizada
     */
    private function construirConfiguracionPersonalizada(array $dias, ?float $duracion): array
    {
        if (!$duracion) {
            throw new Exception('Debe especificar duración en horas para configuración personalizada');
        }

        return [
            'dias' => $dias,
            'duracion_horas' => $duracion
        ];
    }

    /**
     * Verificar conflictos de horario
     */
    private function verificarConflicto(Grupo $grupo, string $dia, string $horaInicio, string $horaFin): ?array
    {
        // Conflicto con otro grupo del docente
        $docente = $grupo->docente;
        if ($docente) {
            $conflictoDocente = Horario::whereHas('grupo', function ($q) use ($docente) {
                $q->where('id_docente', $docente->id_docente);
            })
            ->where('dia_semana', $dia)
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->whereBetween('hora_inicio', [$horaInicio, $horaFin])
                  ->orWhereBetween('hora_fin', [$horaInicio, $horaFin])
                  ->orWhere(function ($q2) use ($horaInicio, $horaFin) {
                      $q2->where('hora_inicio', '<=', $horaInicio)
                         ->where('hora_fin', '>=', $horaFin);
                  });
            })
            ->first();

            if ($conflictoDocente) {
                $grupo2 = $conflictoDocente->grupo;
                return [
                    'tipo' => 'docente',
                    'descripcion' => "Docente {$docente->nombre} ya tiene clase el {$dia} de {$conflictoDocente->hora_inicio} a {$conflictoDocente->hora_fin}"
                ];
            }
        }

        return null;
    }

    /**
     * Convertir minutos a formato HH:MM
     */
    private function minutosAHora(int $minutos): string
    {
        $horas = intdiv($minutos, 60);
        $mins = $minutos % 60;
        return sprintf('%02d:%02d', $horas, $mins);
    }

    /**
     * Obtener sugerencia de patrón basado en carga horaria
     */
    public function sugerirPatron(int $cargaHoraria): string
    {
        // Si la carga es múltiplo de 4.5, sugerir un día completo
        if ($cargaHoraria % 4.5 == 0) {
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            return match($cargaHoraria / 4.5) {
                1 => 'L',
                2 => 'L',
                default => 'LMV'
            };
        }

        // Si es cercano a 4.5 (3 horas = LMV x 1.5)
        if (abs($cargaHoraria - 4.5) < 0.5) {
            return 'LMV';
        }

        // Si es cercano a 4.5 (MJ x 2.25)
        if (abs($cargaHoraria - 4.5) < 0.5) {
            return 'MJ';
        }

        // Default
        return 'LMV';
    }
}
