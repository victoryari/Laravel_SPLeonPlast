<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuiaRemisionTercerosSalidaDetalle extends Model
{
    use HasFactory;

    protected $table = 'guia_remision_terceros_salida_detalle';
    protected $primaryKey = 'id_detalle_salida';

    protected $fillable = [
        'id_guia_salida',
        'codigo_producto',
        'cantidad_enviada',
        'cantidad_devuelta',
        'cantidad_merma',
        'estado_detalle',
    ];

    public function guia()
    {
        return $this->belongsTo(GuiaRemisionTercerosSalida::class, 'id_guia_salida', 'id_guia_salida');
    }
}
