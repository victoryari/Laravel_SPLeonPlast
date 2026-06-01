<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleGuiaCompra extends Model
{
    protected $table = 'detalle_guia_compras';
    protected $primaryKey = 'id_detalle_guia';
    
    public $timestamps = false;

    protected $fillable = [
        'id_guia',
        'codigo_producto',
        'descripcion_producto',
        'cantidad',
        'codigo_unidad_medida',
        'codigo_almacen',
        'lote',
        'fecha_vencimiento'
    ];

    public function guia()
    {
        return $this->belongsTo(GuiaRemisionCompra::class, 'id_guia', 'id_guia');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }
}
