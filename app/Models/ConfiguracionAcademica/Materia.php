<?php

namespace App\Models\ConfiguracionAcademica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materia';
    protected $primaryKey = 'id_materia';
    public $timestamps = false;

    protected $fillable = ['codigo', 'nombre', 'carga_horaria', 'id_facultad', 'requiere_laboratorio'];

    protected $casts = [
        'requiere_laboratorio' => 'boolean',
    ];

    // Relaciones N:1
    public function facultad() {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }

    // Relación 1:N
    public function grupos() {
        return $this->hasMany(Grupo::class, 'id_materia');
    }
}