<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Administracion\Permiso;
use App\Models\Administracion\Rol;
use App\Models\Administracion\RolPermiso;

class PermisoDocenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos específicos para docentes
        $permisosData = [
            ['nombre' => 'marcar_asistencia_qr', 'descripcion' => 'Marcar asistencia mediante escaneo de QR'],
            ['nombre' => 'ver_mis_horarios', 'descripcion' => 'Ver distribución de sus propios horarios'],
            ['nombre' => 'ver_mis_asistencias', 'descripcion' => 'Consultar registro de sus asistencias'],
            ['nombre' => 'actualizar_perfil', 'descripcion' => 'Actualizar información personal'],
        ];

        $permisos = [];
        foreach ($permisosData as $permisoData) {
            $permiso = Permiso::updateOrCreate(
                ['nombre' => $permisoData['nombre']],
                ['descripcion' => $permisoData['descripcion']]
            );
            $permisos[] = $permiso;
        }

        // Obtener o crear rol docente
        $rolDocente = Rol::updateOrCreate(
            ['nombre' => 'Docente'],
            ['descripcion' => 'Profesor/a de la institución']
        );

        // Asignar permisos al rol docente
        foreach ($permisos as $permiso) {
            RolPermiso::updateOrCreate(
                [
                    'id_rol' => $rolDocente->id_rol,
                    'id_permiso' => $permiso->id_permiso
                ]
            );
        }

        $this->command->info('Permisos de docente creados exitosamente');
    }
}
