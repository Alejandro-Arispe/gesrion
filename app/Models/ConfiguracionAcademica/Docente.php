<?php

namespace App\Models\ConfiguracionAcademica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    use HasFactory;

    protected $table = 'docente';
    protected $primaryKey = 'id_docente';
    public $timestamps = false;

    protected $fillable = [
        'ci', 'nombre', 'sexo', 'telefono', 'correo', 'estado', 'id_facultad'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    // Relación N:1
    public function facultad() {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }

    // Relación 1:1 con Usuario
    public function usuario() {
        return $this->hasOne(\App\Models\Administracion\Usuario::class, 'id_docente');
    }

    // Relación 1:N
    public function grupos() {
        return $this->hasMany(Grupo::class, 'id_docente');
    }

    public function horarios() {
        return $this->hasMany(\App\Models\Planificacion\Horario::class, 'id_docente');
    }

    public function disponibilidades() {
        return $this->hasMany(\App\Models\ControlSeguimiento\DisponibilidadDocente::class, 'id_docente');
    }
}