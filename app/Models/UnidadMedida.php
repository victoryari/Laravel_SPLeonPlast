<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidad_medida';
    protected $primaryKey = 'codigo'; // Definido según tu lista de campos
    public $incrementing = false; // Al ser código, asumimos que es un string identificador
    protected $keyType = 'string';

    // Desactivamos timestamps estándar ya que usas 'fecha_creacion'
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
        'estado',
        'fecha_creacion'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'estado' => 'integer',
    ];
}