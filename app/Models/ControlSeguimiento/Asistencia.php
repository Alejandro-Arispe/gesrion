<?php

namespace App\Models\ControlSeguimiento;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConfiguracionAcademica\Docente;
use App\Models\Planificacion\Horario;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencia';
    protected $primaryKey = 'id_asistencia';
    public $timestamps = false;

    protected $fillable = [
        'id_docente',
        'id_horario',
        'fecha',
        'hora_marcado',
        'estado',
        'latitud',
        'longitud',
        'foto'
    ];

    protected $casts = [
        'fecha' => 'date',
        'latitud' => 'decimal:6',
        'longitud' => 'decimal:6'
    ];

    // Relaciones
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario');
    }

    // Scopes
    public function scopePorDocente($query, $idDocente)
    {
        return $query->where('id_docente', $idDocente);
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}