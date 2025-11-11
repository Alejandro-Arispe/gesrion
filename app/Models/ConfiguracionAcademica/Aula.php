<?php

namespace App\Models\ConfiguracionAcademica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

    protected $table = 'aula';
    protected $primaryKey = 'id_aula';
    public $timestamps = false;

    protected $fillable = ['nro', 'piso', 'capacidad', 'ubicacion_gps', 'id_facultad', 'tipo_aula', 'disponible'];

    protected $casts = [
        'capacidad' => 'integer',
        'disponible' => 'boolean'
    ];

    // Relación N:1
    public function facultad() {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }

    // Relación 1:N
    public function horarios() {
        return $this->hasMany(\App\Models\Planificacion\Horario::class, 'id_aula');
    }
}