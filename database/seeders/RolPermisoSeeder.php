<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\administracion\Rol;
use App\Models\administracion\Permiso;
use App\Models\administracion\Usuario;

class RolPermisoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Rol::create(['nombre' => 'Administrador', 'descripcion' => 'Acceso total']);
        $docente = Rol::create(['nombre' => 'Docente', 'descripcion' => 'Acceso limitado a su carga horaria']);

        $permisos = [
            'ver_docentes', 'crear_docentes', 'editar_docentes', 'eliminar_docentes',
            'ver_horarios', 'asignar_horarios', 'ver_reportes'
        ];

        foreach ($permisos as $p) {
            $permiso = Permiso::create(['nombre' => $p]);
            $admin->permisos()->attach($permiso);
        }

        Usuario::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'correo' => 'admin@ficct.edu.bo',
            'id_rol' => $admin->id_rol,
        ]);
    }
}
