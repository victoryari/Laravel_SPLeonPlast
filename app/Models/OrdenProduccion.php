<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenProduccion extends Model
{
    use HasFactory;

    protected $table = 'orden_produccion_global';
    protected $primaryKey = 'idop';
    public $timestamps = false;

    protected $fillable = [
        'codigo_op',
        'codigo_producto_proceso',
        'descripcion_producto_proceso',
        'fecha',
        'hora_inicio',
        'texto_obs',
        'estado',
        'activo'
    ];

    public function procesos()
    {
        return $this->hasMany(OrdenProceso::class, 'idop', 'idop');
    }

    public function productoProceso()
    {
        return $this->belongsTo(ProductoProceso::class, 'codigo_producto_proceso', 'codigo');
    }

    // Accessors útiles para la vista principal
    public function getProcesosTotalesAttribute()
    {
        return $this->procesos()->where('estado', '!=', 0)->count();
    }

    public function getProcesosCompletadosAttribute()
    {
        return $this->procesos()->where('estado', '!=', 0)->where('estado_avance', 'COMPLETADO')->count();
    }
}
