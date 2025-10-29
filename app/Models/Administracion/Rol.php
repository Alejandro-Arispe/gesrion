<?php

namespace App\Models\administracion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    // ==========================
    // RELACIONES
    // ==========================
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol');
    }

    public function permisos()
    {
        return $this->belongsToMany(
            Permiso::class,
            'rol_permiso',     // tabla pivote
            'id_rol',          // clave local
            'id_permiso'       // clave relacionada
        );
    }
}
