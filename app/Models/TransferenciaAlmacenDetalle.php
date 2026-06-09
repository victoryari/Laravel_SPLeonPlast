<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferenciaAlmacenDetalle extends Model
{
    use HasFactory;

    protected $table = 'transferencias_almacen_detalle';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_transferencia',
        'codigo_producto',
        'lote',
        'cantidad',
    ];

    public function transferencia()
    {
        return $this->belongsTo(TransferenciaAlmacen::class, 'id_transferencia', 'id_transferencia');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }
}
