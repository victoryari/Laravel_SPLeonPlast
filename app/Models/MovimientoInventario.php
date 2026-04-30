<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_almacen', 'codigo_producto', 'tipo_movimiento', 'cantidad', 
        'costo_unitario', 'total', 'saldo_actual', 'documento_referencia', 
        'observaciones', 'fecha_movimiento', 'usuario_movimiento', 'estado'
    ];

    public function almacen() { return $this->belongsTo(Almacen::class, 'id_almacen'); }
    public function producto() { return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo'); }
    public function usuario() { return $this->belongsTo(Usuario::class, 'usuario_movimiento'); }
}