<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\administracion\Rol;
use App\Models\administracion\Permiso;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        // Rol Administrador - Todos los permisos
        $admin = Rol::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Acceso completo al sistema'
        ]);
        $admin->permisos()->attach(Permiso::all());

        // Rol Coordinador - Gestión académica
        $coordinador = Rol::create([
            'nombre' => 'Coordinador',
            'descripcion' => 'Gestión de configuración académica'
        ]);
        $coordinador->permisos()->attach(
            Permiso::whereIn('nombre', [
                'gestionar_gestiones',
                'gestionar_docentes',
                'gestionar_materias',
                'gestionar_grupos',
                'gestionar_aulas',
                'gestionar_horarios',
                'ver_reportes'
            ])->pluck('id_permiso')
        );

        // Rol Docente - Solo consulta y asistencia
        $docente = Rol::create([
            'nombre' => 'Docente',
            'descripcion' => 'Consulta de horarios y registro de asistencia'
        ]);
        $docente->permisos()->attach(
            Permiso::whereIn('nombre', [
                'registrar_asistencia'
            ])->pluck('id_permiso')
        );
    }
}