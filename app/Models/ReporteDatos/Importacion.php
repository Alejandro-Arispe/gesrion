<?php

namespace App\Models\ReporteDatos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Importacion extends Model
{
    use HasFactory;

    protected $table = 'importacion';
    protected $primaryKey = 'id_importacion';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'archivo_nombre',
        'fecha_importacion',
        'estado'
    ];

    protected $casts = [
        'fecha_importacion' => 'datetime'
    ];
}