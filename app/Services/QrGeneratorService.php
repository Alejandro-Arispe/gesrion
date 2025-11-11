<?php

namespace App\Services;

use App\Models\Planificacion\QrAula;
use App\Models\ConfiguracionAcademica\Aula;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrGeneratorService
{
    /**
     * Generar QR único para un aula
     * Si ya existe, retorna el existente
     * 
     * @param int $idAula
     * @return QrAula
     * @throws \Exception
     */
    public function generarQrAula($idAula)
    {
        // Validar que el aula exista
        $aula = Aula::findOrFail($idAula);

        // Verificar si ya existe QR para esta aula
        $qrExistente = QrAula::where('id_aula', $idAula)->first();
        if ($qrExistente) {
            return $qrExistente;
        }

        // Generar token único
        $token = Str::random(32);

        // Generar contenido del QR (JSON)
        $contenidoQr = json_encode([
            'id_aula' => $idAula,
            'nro_aula' => $aula->nro,
            'token' => $token,
            'generado_en' => now()->toIso8601String()
        ]);

        // Generar código QR
        $codigoQr = QrCode::format('svg')->size(300)->generate($contenidoQr);

        // Guardar en BD
        $qr = QrAula::create([
            'id_aula' => $idAula,
            'codigo_qr' => $codigoQr,
            'token' => $token
        ]);

        return $qr;
    }

    /**
     * Regenerar QR para un aula (invalidar anterior)
     * 
     * @param int $idAula
     * @return QrAula
     */
    public function regenerarQrAula($idAula)
    {
        // Eliminar QR anterior
        QrAula::where('id_aula', $idAula)->delete();

        // Generar nuevo
        return $this->generarQrAula($idAula);
    }

    /**
     * Validar QR leído
     * Retorna datos del aula si es válido
     * 
     * @param string $codigoQrLeido - Contenido leído del QR
     * @return array|false - Datos del aula o false si no es válido
     */
    public function validarQrLeido($codigoQrLeido)
    {
        try {
            $datos = json_decode($codigoQrLeido, true);

            if (!$datos || !isset($datos['token']) || !isset($datos['id_aula'])) {
                return false;
            }

            // Buscar QR en BD
            $qr = QrAula::where('token', $datos['token'])
                        ->where('id_aula', $datos['id_aula'])
                        ->first();

            if (!$qr) {
                return false;
            }

            // Retornar información del aula
            return [
                'id_aula' => $qr->id_aula,
                'nro_aula' => $qr->aula->nro,
                'piso' => $qr->aula->piso,
                'ubicacion_gps' => $qr->aula->ubicacion_gps,
                'tipo_aula' => $qr->aula->tipo_aula,
                'valido' => true
            ];

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener QR de un aula
     * 
     * @param int $idAula
     * @return QrAula|null
     */
    public function obtenerQrAula($idAula)
    {
        return QrAula::where('id_aula', $idAula)->first();
    }

    /**
     * Generar QR para todas las aulas (útil para setup inicial)
     * 
     * @return array - Resumen de generación
     */
    public function generarQrTodasAulas()
    {
        $aulas = Aula::where('disponible', true)->get();
        $generados = 0;
        $errores = [];

        foreach ($aulas as $aula) {
            try {
                $this->generarQrAula($aula->id_aula);
                $generados++;
            } catch (\Exception $e) {
                $errores[] = [
                    'id_aula' => $aula->id_aula,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'generados' => $generados,
            'total' => $aulas->count(),
            'errores' => $errores
        ];
    }
}
