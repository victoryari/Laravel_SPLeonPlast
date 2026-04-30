<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperacionProduccion extends Model
{
    use HasFactory;

    protected $table = 'operacion_produccion';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    // Usamos el campo fecha_creacion como created_at nativo de Laravel
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null; // Si no tienes campo de actualización

    protected $fillable = [
        'codigo',
        'descripcion',
        'estado'
    ];

    // Scope para traer solo los registros activos (Soft Delete)
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }
}