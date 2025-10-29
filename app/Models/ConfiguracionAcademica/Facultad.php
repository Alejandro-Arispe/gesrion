<?php

namespace App\Models\ConfiguracionAcademica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    use HasFactory;

    protected $table = 'facultad';
    protected $primaryKey = 'id_facultad';
    public $timestamps = false;

    protected $fillable = ['nombre', 'sigla', 'activo'];

    // Relaciones 1:N
    public function docentes() {
        return $this->hasMany(Docente::class, 'id_facultad');
    }

    public function materias() {
        return $this->hasMany(Materia::class, 'id_facultad');
    }

    public function aulas() {
        return $this->hasMany(Aula::class, 'id_facultad');
    }
}