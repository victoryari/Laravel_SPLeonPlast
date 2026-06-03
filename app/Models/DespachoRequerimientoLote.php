<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DespachoRequerimientoLote extends Model
{
    protected $table = 'despacho_requerimiento_lotes';
    protected $primaryKey = 'id_despacho_lote';
    const CREATED_AT = 'fecha_despacho';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_detalle',
        'id_requerimiento',
        'lote',
        'cantidad',
    ];

    public function detalle()
    {
        return $this->belongsTo(DetalleRequerimientoMaterial::class, 'id_detalle', 'id_detalle');
    }

    public function requerimiento()
    {
        return $this->belongsTo(RequerimientoMaterial::class, 'id_requerimiento', 'id_requerimiento');
    }
}
