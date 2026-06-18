<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TercerosMapeoProductos extends Model
{
    use HasFactory;

    protected $table = 'terceros_mapeo_productos';
    protected $primaryKey = 'id_mapeo';

    protected $fillable = [
        'codigo_producto_origen',
        'codigo_producto_destino',
        'descripcion_proceso',
        'estado',
    ];
}
