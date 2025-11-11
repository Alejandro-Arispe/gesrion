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
         Facultad::create([
            'nombre' => 'Facultad de Ciencias de la Computacino y telecomunicaicones',
            'modulo' => 'Módulo 236'
        ]);
    }
}