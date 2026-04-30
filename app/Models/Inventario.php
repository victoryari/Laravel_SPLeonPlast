<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventario';
    protected $primaryKey = 'id_inventario';
    public $timestamps = false; // Manejamos fecha_ultimo_movimiento manualmente

    protected $fillable = [
        'codigo_producto', 'id_almacen', 'lote', 'stock_actual', 
        'stock_minimo', 'stock_maximo', 'costo_promedio', 
        'ultimo_costo', 'fecha_ultimo_movimiento', 'usuario_ultimo_movimiento'
    ];

    public function producto() {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }

    public function almacen() {
        return $this->belongsTo(Almacen::class, 'codigo_almacen', 'codigo_almacen');
    }
}