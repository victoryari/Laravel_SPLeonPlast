<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuiaRemisionTercerosSalida extends Model
{
    use HasFactory;

    protected $table = 'guia_remision_terceros_salida';
    protected $primaryKey = 'id_guia_salida';

    protected $fillable = [
        'numero_guia',
        'fecha_emision',
        'codigo_almacen_origen',
        'proveedor_destino',
        'ruc_proveedor',
        'motivo_traslado',
        'observaciones',
        'estado_guia',
        'usuario_registro',
    ];

    public function detalles()
    {
        return $this->hasMany(GuiaRemisionTercerosSalidaDetalle::class, 'id_guia_salida', 'id_guia_salida');
    }
}
