<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\administracion\Usuario;
use App\Models\administracion\Rol;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $adminRol = Rol::where('nombre', 'Administrador')->first();
        $coordinadorRol = Rol::where('nombre', 'Coordinador')->first();
        $docenteRol = Rol::where('nombre', 'Docente')->first();

        // Usuario Administrador
        Usuario::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'correo' => 'admin@ficct.edu.bo',
            'activo' => true,
            'id_rol' => $adminRol->id_rol
        ]);

        Usuario::create([
            'username' => 'ale',
            'password' => Hash::make('ale123'),
            'correo' => 'ale@gmail.com',
            'activo' => true,
            'id_rol' => $adminRol->id_rol
        ]);

        // Usuario Coordinador
        Usuario::create([
            'username' => 'coordinador',
            'password' => Hash::make('coord123'),
            'correo' => 'coordinador@ficct.edu.bo',
            'activo' => true,
            'id_rol' => $coordinadorRol->id_rol
        ]);

        // Usuario Docente
        Usuario::create([
            'username' => 'docente',
            'password' => Hash::make('docente123'),
            'correo' => 'docente@ficct.edu.bo',
            'activo' => true,
            'id_rol' => $docenteRol->id_rol
        ]);
    }
}