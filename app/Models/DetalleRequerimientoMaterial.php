<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleRequerimientoMaterial extends Model
{
    protected $table = 'detalle_requerimientos_materiales';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_requerimiento',
        'codigo_producto',
        'codigo_almacen_origen',
        'codigo_almacen_destino',
        'cantidad_solicitada',
        'cantidad_atendida',
        'lote_preferente',
        'observaciones',
    ];

    public function requerimiento()
    {
        return $this->belongsTo(RequerimientoMaterial::class, 'id_requerimiento', 'id_requerimiento');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }

    public function almacenOrigen()
    {
        return $this->belongsTo(Almacen::class, 'codigo_almacen_origen', 'codigo_almacen');
    }

    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'codigo_almacen_destino', 'codigo_almacen');
    }
}
