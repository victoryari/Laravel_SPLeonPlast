<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';

    // Configuramos para que use fecha_registro como created_at
    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = null;

    protected $fillable = [
        'ruc',
        'razon_social',
        'direccion',
        'telefono',
        'email',
        'contacto',
        'activo'
    ];

    // Scope para filtrar solo los proveedores activos
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }
}