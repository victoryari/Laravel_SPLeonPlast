<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroTrabajo extends Model
{
    use HasFactory;

    protected $table = 'centro_trabajo_produccion';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    // Mapeamos el campo fecha_creacion al comportamiento estándar de Laravel
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'codigo',
        'descripcion',
        'estado'
    ];

    // Traer solo los centros activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }
}