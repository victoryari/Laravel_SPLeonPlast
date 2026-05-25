<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kardex extends Model
{
    protected $table = 'kardex';
    protected $primaryKey = 'id_kardex';
    public $timestamps = false;

    protected $fillable = [
        'codigo_almacen', 'codigo_producto', 'codigo_unidad_medida',
        'fecha_movimiento', 'tipo_movimiento', 'documento',
        'numero_documento', 'cantidad_entrada', 'costo_entrada',
        'total_entrada', 'cantidad_salida', 'costo_salida',
        'total_salida', 'cantidad_saldo', 'observaciones',
        'costo_promedio', 'total_saldo', 'lote', 'usuario_registro'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'codigo_almacen', 'codigo_almacen');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_registro');
    }
}
