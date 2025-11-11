<?php

namespace App\Models\ControlSeguimiento;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConfiguracionAcademica\Docente;

class DisponibilidadDocente extends Model
{
    use HasFactory;

    protected $table = 'disponibilidad_docente';
    protected $primaryKey = 'id_disponibilidad';
    public $timestamps = false;

    protected $fillable = [
        'id_docente',
        'dia_semana',
        'hora_inicio',
        'hora_fin'
    ];

    // Relación
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }
}