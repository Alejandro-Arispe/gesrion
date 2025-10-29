<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\administracion\Permiso;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            ['nombre' => 'gestionar_usuarios', 'descripcion' => 'Crear, editar y eliminar usuarios'],
            ['nombre' => 'gestionar_roles', 'descripcion' => 'Gestionar roles y permisos'],
            ['nombre' => 'gestionar_facultades', 'descripcion' => 'Gestionar facultades'],
            ['nombre' => 'gestionar_gestiones', 'descripcion' => 'Gestionar gestiones académicas'],
            ['nombre' => 'gestionar_docentes', 'descripcion' => 'Gestionar docentes'],
            ['nombre' => 'gestionar_materias', 'descripcion' => 'Gestionar materias'],
            ['nombre' => 'gestionar_grupos', 'descripcion' => 'Gestionar grupos'],
            ['nombre' => 'gestionar_aulas', 'descripcion' => 'Gestionar aulas'],
            ['nombre' => 'gestionar_horarios', 'descripcion' => 'Gestionar horarios'],
            ['nombre' => 'ver_reportes', 'descripcion' => 'Ver reportes del sistema'],
            ['nombre' => 'registrar_asistencia', 'descripcion' => 'Registrar asistencia docente'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::create($permiso);
        }
    }
}