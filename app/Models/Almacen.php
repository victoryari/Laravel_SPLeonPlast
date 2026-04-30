<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table = 'almacen';
    protected $primaryKey = 'codigo_almacen';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'codigo_almacen',
        'descripcion',
        'tipo_almacen',
        'direccion',
        'responsable',
        'activo',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'activo' => 'integer',
    ];

    /**
     * Tipos de almacén disponibles (enum de la BD).
     */
    const TIPOS_ALMACEN = [
        'MATERIA_PRIMA'       => 'Materia Prima',
        'PRODUCTO_TERMINADO'  => 'Producto Terminado',
        'PRODUCTO_PROCESO'    => 'Producto en Proceso',
        'INSUMOS'             => 'Insumos',
        'SUMINISTROS'         => 'Suministros',
    ];

    /**
     * Scope para obtener solo almacenes activos.
     */
    public function scopeActivos(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where('activo', 1);
    }
}