<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    protected $table = 'detalle_compra';
    protected $primaryKey = 'id_detalle_compra';
    public $timestamps = false;

    protected $fillable = [
    'id_compra', 
    'codigo_producto', 
    'descripcion_producto', // Recomendado
    'cantidad', 
    'precio_unitario', 
    'codigo_unidad_medida',
    'subtotal', 
    'igv', 
    'total', 
    'codigo_almacen'
    ];

    public function producto() {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }

    // AGREGAR ESTA RELACIÓN:
    public function almacen() {
        return $this->belongsTo(Almacen::class, 'codigo_almacen', 'codigo_almacen');
    }
}