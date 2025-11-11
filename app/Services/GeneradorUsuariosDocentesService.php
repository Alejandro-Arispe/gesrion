<?php

namespace App\Services;

use App\Models\Administracion\Usuario;
use App\Models\ConfiguracionAcademica\Docente;
use Illuminate\Support\Facades\Hash;
use Exception;

class GeneradorUsuariosDocentesService
{
    /**
     * Generar usuarios para docentes sin usuario
     * Contraseña: nombre + 123 (ej: Juan123)
     * 
     * @return array Resumen de creación
     */
    public function generarUsuariosDocentes()
    {
        try {
            $docentes = Docente::where('estado', true)->get();
            
            $resumen = [
                'creados' => 0,
                'omitidos' => 0,
                'errores' => 0,
                'detalles' => []
            ];

            $rolDocente = \DB::table('rol')->where('nombre', 'Docente')->first();
            
            if (!$rolDocente) {
                throw new Exception('No existe el rol "Docente". Ejecuta el seeder PermisoDocenteSeeder');
            }

            foreach ($docentes as $docente) {
                try {
                    // Verificar si ya existe usuario para este docente
                    $usuarioExistente = Usuario::where('id_docente', $docente->id_docente)
                                              ->orWhere('correo', $docente->correo)
                                              ->first();

                    if ($usuarioExistente) {
                        $resumen['omitidos']++;
                        $resumen['detalles'][] = [
                            'docente' => $docente->nombre,
                            'estado' => 'omitido',
                            'razon' => 'Usuario ya existe: ' . $usuarioExistente->username
                        ];
                        continue;
                    }

                    // Generar credenciales
                    $username = $this->generarUsername($docente);
                    $passwordPlano = $this->generarPassword($docente);
                    $passwordHasheada = Hash::make($passwordPlano);

                    // Crear usuario
                    $usuario = Usuario::create([
                        'username' => $username,
                        'password' => $passwordHasheada,
                        'correo' => $docente->correo,
                        'activo' => true,
                        'id_rol' => $rolDocente->id_rol,
                        'id_docente' => $docente->id_docente
                    ]);

                    $resumen['creados']++;
                    $resumen['detalles'][] = [
                        'docente' => $docente->nombre,
                        'estado' => 'creado',
                        'username' => $username,
                        'password_plano' => $passwordPlano, // Solo para mostrar en PDF
                        'correo' => $docente->correo
                    ];

                } catch (Exception $e) {
                    $resumen['errores']++;
                    $resumen['detalles'][] = [
                        'docente' => $docente->nombre,
                        'estado' => 'error',
                        'razon' => $e->getMessage()
                    ];
                }
            }

            return $resumen;

        } catch (Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar nombre de usuario desde el docente
     * Formato: primer.apellido (ej: juan.perez)
     */
    private function generarUsername(Docente $docente): string
    {
        // Separar nombre y apellido
        $partes = explode(' ', trim($docente->nombre));
        $nombre = strtolower(array_shift($partes));
        $apellido = strtolower(implode('', $partes)); // Resto es apellido

        $username = $nombre . '.' . $apellido;
        $username = preg_replace('/[^a-z0-9.]/i', '', $username); // Solo letras y puntos

        // Verificar que sea único
        $contador = 1;
        $usernameOriginal = $username;
        while (Usuario::where('username', $username)->exists()) {
            $username = $usernameOriginal . $contador;
            $contador++;
        }

        return $username;
    }

    /**
     * Generar contraseña: nombre + 123
     * Formato: Juan123 (primera letra mayúscula, sin espacios)
     */
    private function generarPassword(Docente $docente): string
    {
        // Obtener primer nombre
        $partes = explode(' ', trim($docente->nombre));
        $primerNombre = $partes[0];
        
        // Formato: PrimerNombre123
        return ucfirst(strtolower($primerNombre)) . '123';
    }

    /**
     * Obtener datos de usuarios creados para PDF
     * (sin contraseñas hasheadas, solo las planas)
     */
    public function obtenerCredencialesDocentes()
    {
        $usuarios = \DB::table('usuario')
                      ->join('docente', 'usuario.id_docente', '=', 'docente.id_docente')
                      ->where('usuario.id_rol', function($query) {
                          $query->select('id_rol')
                                ->from('rol')
                                ->where('nombre', 'Docente')
                                ->limit(1);
                      })
                      ->select(
                          'docente.nombre',
                          'usuario.username',
                          'usuario.correo',
                          'usuario.activo',
                          'usuario.created_at'
                      )
                      ->orderBy('docente.nombre')
                      ->get();

        return $usuarios;
    }

    /**
     * Regenerar contraseña para un docente específico
     */
    public function regenerarPassword($idDocente)
    {
        try {
            $docente = Docente::findOrFail($idDocente);
            $usuario = Usuario::where('id_docente', $idDocente)->firstOrFail();

            $passwordPlano = $this->generarPassword($docente);
            $usuario->password = Hash::make($passwordPlano);
            $usuario->save();

            return [
                'exito' => true,
                'mensaje' => 'Contraseña regenerada exitosamente',
                'usuario' => $usuario->username,
                'password_plano' => $passwordPlano
            ];

        } catch (Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Desactivar usuario de docente
     */
    public function desactivarUsuario($idDocente)
    {
        try {
            $usuario = Usuario::where('id_docente', $idDocente)->firstOrFail();
            $usuario->activo = false;
            $usuario->save();

            return [
                'exito' => true,
                'mensaje' => 'Usuario desactivado exitosamente'
            ];

        } catch (Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
