<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $table = 'color';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'codigo',
        'descripcion',
        'activo'
    ];

    // Traer solo los colores activos
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }
}