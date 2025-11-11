<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BitacoraSeeder extends Seeder
{
    public function run(): void
    {
        // Insertar registros de ejemplo en bitácora
        $ahora = Carbon::now();
        
        DB::table('bitacora')->insert([
            [
                'id_usuario' => 1,
                'accion' => 'LOGIN',
                'descripcion' => 'Acceso al sistema',
                'fecha_hora' => $ahora->copy()->subDays(2),
                'ip_origen' => '192.168.1.100'
            ],
            [
                'id_usuario' => 1,
                'accion' => 'CREATE',
                'descripcion' => 'Crear gestión académica 2025-1',
                'fecha_hora' => $ahora->copy()->subDays(2)->addHours(1),
                'ip_origen' => '192.168.1.100'
            ],
            [
                'id_usuario' => 1,
                'accion' => 'CREATE',
                'descripcion' => 'Crear docente: Juan Pérez',
                'fecha_hora' => $ahora->copy()->subDays(1),
                'ip_origen' => '192.168.1.100'
            ],
            [
                'id_usuario' => 1,
                'accion' => 'UPDATE',
                'descripcion' => 'Actualizar docente: Juan Pérez',
                'fecha_hora' => $ahora->copy()->subHours(5),
                'ip_origen' => '192.168.1.100'
            ],
            [
                'id_usuario' => 1,
                'accion' => 'CREATE',
                'descripcion' => 'Crear materia: Programación I',
                'fecha_hora' => $ahora->copy()->subHours(3),
                'ip_origen' => '192.168.1.100'
            ],
            [
                'id_usuario' => 1,
                'accion' => 'CREATE',
                'descripcion' => 'Asignar horario: Grupo F1',
                'fecha_hora' => $ahora->copy()->subHours(2),
                'ip_origen' => '192.168.1.100'
            ],
            [
                'id_usuario' => 1,
                'accion' => 'CREATE',
                'descripcion' => 'Registrar asistencia - Docente: Juan Pérez',
                'fecha_hora' => $ahora->copy()->subHours(1),
                'ip_origen' => '192.168.1.100'
            ],
            [
                'id_usuario' => 1,
                'accion' => 'VIEW',
                'descripcion' => 'Ver reportes de asistencia',
                'fecha_hora' => $ahora->copy()->subMinutes(30),
                'ip_origen' => '192.168.1.100'
            ]
        ]);
    }
}
