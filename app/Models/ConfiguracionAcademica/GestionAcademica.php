<?php

namespace App\Models\ConfiguracionAcademica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GestionAcademica extends Model
{
    use HasFactory;

    protected $table = 'gestion_academica';
    protected $primaryKey = 'id_gestion';
    public $timestamps = false;

    protected $fillable = ['anio', 'semestre', 'fecha_inicio', 'fecha_fin', 'estado'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => 'boolean'
    ];

    // Relación 1:N
    public function materias() {
        return $this->hasMany(Materia::class, 'id_gestion');
    }

    public function grupos() {
        return $this->hasMany(Grupo::class, 'id_gestion');
    }

    // Scope para obtener la gestión activa
    public function scopeActiva($query) {
        return $query->where('estado', true);
    }
}