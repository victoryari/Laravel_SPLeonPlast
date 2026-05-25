<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduccionIngresoProceso extends Model
{
    use HasFactory;

    protected $table = 'produccion_ingresos_proceso';
    protected $primaryKey = 'id_ingreso';
    public $timestamps = false; // Manejado manualmente si es necesario

    protected $fillable = [
        'idop',
        'id_proceso',
        'codigo_producto_proceso',
        'descripcion_producto_proceso',
        'cantidad',
        'codigo_unidad_medida',
        'codigo_almacen',
        'lote_produccion',
        'fecha_ingreso',
        'usuario_registro',
        'estado'
    ];
}
