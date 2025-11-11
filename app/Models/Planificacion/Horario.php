<?php

namespace App\Models\Planificacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ConfiguracionAcademica\Grupo;
use App\Models\ConfiguracionAcademica\Aula;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horario';
    protected $primaryKey = 'id_horario';
    public $timestamps = false;

    protected $fillable = [
        'id_grupo', 
        'id_aula', 
        'dia_semana', 
        'hora_inicio', 
        'hora_fin', 
        'tipo_asignacion',
        'distribucion_dias'
    ];

    protected $casts = [
        'distribucion_dias' => 'array',
    ];

    // Relaciones N:1
    public function grupo() {
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }

    public function aula() {
        return $this->belongsTo(Aula::class, 'id_aula');
    }

    // Relación indirecta al docente a través del grupo
    public function getDocenteAttribute() {
        return $this->grupo?->docente;
    }

    // Scope para buscar por día
    public function scopePorDia($query, $dia) {
        return $query->where('dia_semana', $dia);
    }

    // Scope para buscar por rango de horas
    public function scopeEnHorario($query, $horaInicio, $horaFin) {
        return $query->where(function($q) use ($horaInicio, $horaFin) {
            $q->whereBetween('hora_inicio', [$horaInicio, $horaFin])
              ->orWhereBetween('hora_fin', [$horaInicio, $horaFin])
              ->orWhere(function($q2) use ($horaInicio, $horaFin) {
                  $q2->where('hora_inicio', '<=', $horaInicio)
                     ->where('hora_fin', '>=', $horaFin);
              });
        });
    }
}