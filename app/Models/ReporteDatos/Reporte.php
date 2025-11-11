<?php

namespace App\Models\ReporteDatos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Administration\Usuario;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reporte';
    protected $primaryKey = 'id_reporte';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'generado_por',
        'fecha_generacion',
        'ruta_archivo'
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime'
    ];

    // Relación
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'generado_por', 'id_usuario');
    }
}