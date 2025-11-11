<?php

namespace App\Models\administracion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'correo',
        'activo',
        'id_rol',
        'id_docente',
    ];

    protected $hidden = ['password'];

    // ==========================
    // RELACIONES
    // ==========================
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function docente()
    {
        return $this->belongsTo(\App\Models\ConfiguracionAcademica\Docente::class, 'id_docente');
    }

    // ==========================
    // JWT REQUIRED METHODS
    // ==========================
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // ==========================
    // PERMISOS
    // ==========================
    public function hasPermission($permiso)
    {
        return $this->rol && $this->rol->permisos()->where('nombre', $permiso)->exists();
    }





    
    public function findForToken($identifier)
    {
         return $this->where('username', $identifier)->first();
    }

    /**
 * También es recomendable redefinir este si estás usando el sistema de autenticación estándar
 */
    public function findForPassport($identifier)
    {
        return $this->where('username', $identifier)->first();
    }


}
