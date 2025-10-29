<?php

namespace App\Models\ConfiguracionAcademica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupo';
    protected $primaryKey = 'id_grupo';
    public $timestamps = false;

    protected $fillable = ['nombre', 'id_materia', 'id_docente', 'id_gestion'];

    // Relaciones N:1
    public function materia() {
        return $this->belongsTo(Materia::class, 'id_materia');
    }

    public function docente() {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function gestion() {
        return $this->belongsTo(GestionAcademica::class, 'id_gestion');
    }

    // Relación 1:N
    public function horarios() {
        return $this->hasMany(\App\Models\Planificacion\Horario::class, 'id_grupo');
    }
}