<?php

namespace App\Models\Planificacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ConfiguracionAcademica\Aula;

class QrAula extends Model
{
    use HasFactory;

    protected $table = 'qr_aula';
    public $timestamps = false;

    protected $fillable = ['id_aula', 'codigo_qr', 'token'];

    protected $dates = ['generado_en', 'actualizado_en'];

    // Relación N:1
    public function aula()
    {
        return $this->belongsTo(Aula::class, 'id_aula');
    }

    /**
     * Scope para buscar por token
     */
    public function scopePorToken($query, $token)
    {
        return $query->where('token', $token);
    }

    /**
     * Scope para buscar por aula
     */
    public function scopePorAula($query, $idAula)
    {
        return $query->where('id_aula', $idAula);
    }
}
