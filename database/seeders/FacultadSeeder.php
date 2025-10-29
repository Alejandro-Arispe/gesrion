<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionAcademica\Facultad;

class FacultadSeeder extends Seeder
{
    public function run(): void
    {
        Facultad::create([
            'nombre' => 'Facultad de Ciencias y Tecnología',
            'modulo' => 'Módulo 3'
        ]);
    }
}